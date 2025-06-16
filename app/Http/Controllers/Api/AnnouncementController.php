<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification; // Import for bulk sending
use App\Notifications\NewCourseAnnouncement; // Import our new notification
use Illuminate\Support\Facades\Log; // Import for debugging

class AnnouncementController extends Controller
{
    /**
     * Display a listing of published and relevant announcements for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = now();

        $announcements = Announcement::published() // Uses the scopePublished() from Announcement model
            ->where(function ($query) use ($user) {
                $query->where('target_audience_type', 'all')
                      ->orWhereNull('target_audience_type');

                if ($user->role_id) {
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('target_audience_type', 'role')
                          ->where('target_audience_id', $user->role_id);
                    });
                }

                if ($user->hasRole('student')) {
                    $enrolledCourseIds = Enrollment::where('user_id', $user->id)
                                                ->pluck('course_id')->unique();
                    if ($enrolledCourseIds->isNotEmpty()) {
                        $query->orWhere(function ($q) use ($enrolledCourseIds) {
                            $q->where('target_audience_type', 'course')
                              ->whereIn('target_audience_id', $enrolledCourseIds);
                        });
                    }
                }
            })
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return response()->json($announcements);
    }

    /**
     * Display a specific published announcement if the user is in the target audience.
     */
    public function showPublic(Announcement $announcement, Request $request)
    {
        $user = Auth::user();
        $now = now();

        if ($announcement->status !== 'published' || 
            ($announcement->published_at && $announcement->published_at->gt($now)) ||
            ($announcement->expires_at && $announcement->expires_at->lt($now))
        ) {
            return response()->json(['message' => 'Announcement not found or not currently active.'], 404);
        }

        $isTargeted = false;
        if ($announcement->target_audience_type === 'all' || is_null($announcement->target_audience_type)) {
            $isTargeted = true;
        } elseif ($announcement->target_audience_type === 'role' && $user->role_id === $announcement->target_audience_id) {
            $isTargeted = true;
        } elseif ($announcement->target_audience_type === 'course' && $user->hasRole('student')) {
            $isEnrolled = Enrollment::where('user_id', $user->id)
                                    ->where('course_id', $announcement->target_audience_id)
                                    ->exists();
            if ($isEnrolled) {
                $isTargeted = true;
            }
        }

        if (!$isTargeted) {
            return response()->json(['message' => 'You are not authorized to view this announcement.'], 403);
        }
        
        $announcement->load('creator:id,name');
        return response()->json($announcement);
    }

    // --- Admin Methods ---

    public function adminIndex(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|string|in:draft,published,archived',
            'target_audience_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $announcements = Announcement::with('creator:id,name')
            ->when($request->query('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->query('target_audience_type'), function ($query, $type) {
                return $query->where('target_audience_type', $type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'sometimes|string|in:draft,published,archived',
            'published_at' => 'nullable|date_format:Y-m-d H:i:s',
            'expires_at' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:published_at',
            'target_audience_type' => 'nullable|string|in:all,role,course',
            'target_audience_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        if (in_array($data['target_audience_type'] ?? null, ['role', 'course']) && empty($data['target_audience_id'])) {
            return response()->json(['errors' => ['target_audience_id' => ['The target audience ID is required when type is role or course.']]], 422);
        }
        
        $data['user_id'] = Auth::id();
        $isPublished = ($data['status'] ?? 'draft') === 'published';
        if ($isPublished && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $announcement = Announcement::create($data);

        // --- NEW NOTIFICATION LOGIC ---
        if ($isPublished && $announcement->target_audience_type === 'course') {
            $this->notifyEnrolledStudents($announcement);
        }
        // --- END NOTIFICATION LOGIC ---

        return response()->json([
            'message' => 'Announcement created successfully.',
            'announcement' => $announcement->load('creator:id,name')
        ], 201);
    }

    public function show(Announcement $announcement)
    {
        return response()->json($announcement->load('creator:id,name'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'status' => 'sometimes|string|in:draft,published,archived',
            'published_at' => 'nullable|date_format:Y-m-d H:i:s',
            'expires_at' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:published_at',
            'target_audience_type' => 'sometimes|nullable|string|in:all,role,course',
            'target_audience_id' => 'sometimes|nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $wasAlreadyPublished = $announcement->status === 'published';
        $data = $validator->validated();
        
        if (in_array($data['target_audience_type'] ?? $announcement->target_audience_type, ['role', 'course']) && empty($data['target_audience_id'] ?? $announcement->target_audience_id)) {
            return response()->json(['errors' => ['target_audience_id' => ['The target audience ID is required when type is role or course.']]], 422);
        }

        if (isset($data['status']) && $data['status'] === 'published' && !$wasAlreadyPublished) {
            $data['published_at'] = empty($data['published_at']) ? now() : $data['published_at'];
        }

        $announcement->update($data);

        // --- NEW NOTIFICATION LOGIC ---
        $isNowPublished = $announcement->fresh()->status === 'published';
        if ($isNowPublished && !$wasAlreadyPublished && $announcement->target_audience_type === 'course') {
            $this->notifyEnrolledStudents($announcement);
        }
        // --- END NOTIFICATION LOGIC ---

        return response()->json([
            'message' => 'Announcement updated successfully.',
            'announcement' => $announcement->fresh()->load('creator:id,name')
        ]);
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return response()->json(['message' => 'Announcement deleted successfully.'], 200);
    }

    /**
     * Helper method to find and notify students enrolled in a course.
     */
    private function notifyEnrolledStudents(Announcement $announcement)
    {
        if ($announcement->target_audience_type !== 'course' || !$announcement->target_audience_id) {
            return;
        }

        $enrolledStudents = User::whereHas('enrollments', function ($query) use ($announcement) {
            $query->where('course_id', $announcement->target_audience_id)
                  ->where('status', 'enrolled'); // Only notify actively enrolled students
        })->whereHas('role', function ($query) {
            $query->where('slug', 'student');
        })->get();

        if ($enrolledStudents->isNotEmpty()) {
            Notification::send($enrolledStudents, new NewCourseAnnouncement($announcement));
            Log::info("Dispatched NewCourseAnnouncement for announcement #{$announcement->id} to {$enrolledStudents->count()} students.");
        }
    }
}
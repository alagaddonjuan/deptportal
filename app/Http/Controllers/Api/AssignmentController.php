<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Subject;
use App\Models\User;
use App\Models\Enrollment; // Added for fetching enrolled students
use App\Notifications\NewAssignmentPosted; // Added
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification; // Added for bulk sending
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // For debugging if needed
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments.
     * Can be filtered by subject_id.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'nullable|integer|exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // $user = Auth::user(); // User context might be needed for more complex filtering later
        $assignmentsQuery = Assignment::with(['subject:id,name,code', 'teacher:id,name']);

        if ($request->has('subject_id')) {
            $assignmentsQuery->where('subject_id', $request->subject_id);
        }

        $assignments = $assignmentsQuery->latest('posted_at')->paginate(15);
        return response()->json($assignments);
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user(); // This is the teacher/admin creating the assignment

        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|integer|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:now',
            'max_marks' => 'nullable|numeric|min:0',
            'assignment_file' => 'nullable|file|mimes:pdf,doc,docx,txt,zip|max:10240', // Max 10MB
            'status' => 'sometimes|string|in:draft,published',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subject = Subject::find($request->subject_id);
        if (!$subject) { // Should be caught by 'exists:subjects,id' but good to be defensive
            return response()->json(['errors' => ['subject_id' => 'Selected subject not found.']], 422);
        }

        // Authorization: Only admin or the teacher assigned to the subject can create an assignment
        if (!$user->isAdmin() && $subject->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized. Only admins or the assigned subject teacher can create assignments for this subject.'], 403);
        }

        $data = $validator->validated();
        $data['teacher_id'] = $user->id; // Creator of the assignment

        // Define $isPublished based on the input status
        $isPublished = ($request->input('status', 'draft') === 'published');
        
        // Set posted_at based on $isPublished
        $data['posted_at'] = $isPublished ? now() : null;

        if ($request->hasFile('assignment_file')) {
            // Store the file in 'public/assignments/subject_X'
            $filePath = $request->file('assignment_file')->store('assignments/subject_' . $request->subject_id, 'public');
            $data['file_path'] = $filePath;
        }

        $assignment = Assignment::create($data);

        // Send Notification if the assignment is published
        if ($isPublished) {
            // Ensure subject relationship is loaded for the notification content
            $assignment->load('subject:id,name,course_id'); // Make sure course_id is included if subject model has it directly

            if ($assignment->subject && $assignment->subject->course_id) {
                $courseId = $assignment->subject->course_id;
            
                $enrolledStudents = User::whereHas('enrollments', function ($query) use ($courseId) {
                    $query->where('course_id', $courseId)
                          ->where('status', 'enrolled'); // Ensure only actively enrolled students
                })->whereHas('role', function ($query) { // Ensure they are students
                    $query->where('slug', 'student');
                })->get();

                if ($enrolledStudents->isNotEmpty()) {
                    Notification::send($enrolledStudents, new NewAssignmentPosted($assignment));
                    Log::info('Notification dispatched for assignment ID: ' . $assignment->id . ' to ' . $enrolledStudents->count() . ' students.');
                } else {
                    Log::info('No enrolled students found to notify for assignment ID: ' . $assignment->id . ' in course ID: ' . $courseId);
                }
            } else {
                Log::warning('Could not determine course_id for assignment ID: ' . $assignment->id . ' to send notifications.');
            }
        }

        return response()->json([
            'message' => 'Assignment created successfully.',
            'assignment' => $assignment->load(['subject:id,name', 'teacher:id,name'])
        ], 201);
    }

    /**
     * Display the specified assignment.
     */
    public function show(Assignment $assignment)
    {
        $assignment->load(['subject:id,name,code', 'teacher:id,name']);

        if ($assignment->file_path) {
            $assignment->download_url = Storage::disk('public')->url($assignment->file_path);
        }

        return response()->json($assignment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Assignment $assignment)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && $assignment->teacher_id !== $user->id && $assignment->subject->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to update this assignment.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'subject_id' => 'sometimes|required|integer|exists:subjects,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:now',
            'max_marks' => 'nullable|numeric|min:0',
            'assignment_file' => 'nullable|file|mimes:pdf,doc,docx,txt,zip|max:10240',
            'remove_file' => 'sometimes|boolean',
            'status' => 'sometimes|string|in:draft,published,archived',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $oldStatusIsPublished = ($assignment->status === 'published');

        // Handle status and posted_at for updates
        if (isset($data['status'])) {
            if ($data['status'] === 'published' && !$oldStatusIsPublished) {
                // If changing status to published and it wasn't published before
                $data['posted_at'] = now();
            } elseif ($data['status'] === 'draft' || $data['status'] === 'archived') {
                $data['posted_at'] = null; // Or keep existing if was published then archived? Policy decision.
            }
        }

        if ($request->boolean('remove_file') && $assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
            $data['file_path'] = null;
        } elseif ($request->hasFile('assignment_file')) {
            if ($assignment->file_path) {
                Storage::disk('public')->delete($assignment->file_path);
            }
            $currentSubjectId = $request->input('subject_id', $assignment->subject_id);
            $filePath = $request->file('assignment_file')->store('assignments/subject_' . $currentSubjectId, 'public');
            $data['file_path'] = $filePath;
        }
        
        unset($data['teacher_id']); // teacher_id (creator) should not be changed on update

        $assignment->update($data);

        // Send notification if status changed to published from a non-published state
        $newStatusIsPublished = ($assignment->fresh()->status === 'published');
        if ($newStatusIsPublished && !$oldStatusIsPublished) {
            $assignment->load('subject:id,name,course_id');
            if ($assignment->subject && $assignment->subject->course_id) {
                $courseId = $assignment->subject->course_id;
                $enrolledStudents = User::whereHas('enrollments', function ($query) use ($courseId) {
                    $query->where('course_id', $courseId)->where('status', 'enrolled');
                })->whereHas('role', function ($query) {
                    $query->where('slug', 'student');
                })->get();

                if ($enrolledStudents->isNotEmpty()) {
                    Notification::send($enrolledStudents, new NewAssignmentPosted($assignment));
                    Log::info('Notification dispatched on update for assignment ID: ' . $assignment->id . ' to ' . $enrolledStudents->count() . ' students.');
                }
            }
        }


        return response()->json([
            'message' => 'Assignment updated successfully.',
            'assignment' => $assignment->fresh()->load(['subject:id,name', 'teacher:id,name'])
        ]);
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy(Assignment $assignment)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $assignment->teacher_id !== $user->id && $assignment->subject->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to delete this assignment.'], 403);
        }

        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }
        $assignment->delete();

        return response()->json(['message' => 'Assignment deleted successfully.'], 200);
    }

    /**
     * Allow downloading the assignment file.
     */
    public function downloadFile(Assignment $assignment)
    {
        if (!$assignment->file_path || !Storage::disk('public')->exists($assignment->file_path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }
        return Storage::disk('public')->download($assignment->file_path);
    }
}
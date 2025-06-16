<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User; // To validate teacher if marked_by_user_id is restricted
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{   
    use AuthorizesRequests;
    /**
     * Display a listing of the resource for an Admin.
     * Filterable by enrollment_id, subject_id, session_date, status.
     */
    public function index(Request $request)
    {   
        $this->authorize('viewAny', Attendance::class);
        // Admin only - protected by isAdmin middleware on the route group
        $validator = Validator::make($request->all(), [
            'enrollment_id' => 'nullable|integer|exists:enrollments,id',
            'subject_id' => 'nullable|integer|exists:subjects,id',
            'session_date' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|string|in:present,absent,late,excused',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendances = Attendance::with([
                'enrollment.student:id,name,email',
                'subject:id,name,code',
                'markedBy:id,name'
            ])
            ->when($request->query('enrollment_id'), function ($query, $enrollmentId) {
                return $query->where('enrollment_id', $enrollmentId);
            })
            ->when($request->query('subject_id'), function ($query, $subjectId) {
                return $query->where('subject_id', $subjectId);
            })
            ->when($request->query('session_date'), function ($query, $sessionDate) {
                return $query->where('session_date', $sessionDate);
            })
            ->when($request->query('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest('session_date')
            ->latest('session_time') // If session_time is used
            ->paginate(20);

        return response()->json($attendances);
    }

    /**
     * Store a newly created attendance record in storage.
     * (Admin only for now, can be adapted for Teachers)
     */
    public function store(Request $request)
    {   
        $this->authorize('create', [Attendance::class, $request->all()]);
        // Admin only - protected by isAdmin middleware on the route group
        $validator = Validator::make($request->all(), [
            'enrollment_id' => 'required|integer|exists:enrollments,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'session_date' => 'required|date_format:Y-m-d',
            'session_time' => 'nullable|date_format:H:i', // Or H:i:s if seconds are needed
            'status' => 'required|string|in:present,absent,late,excused',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Ensure the subject is part of the course the student is enrolled in
        $enrollment = Enrollment::with('course.subjects')->find($request->enrollment_id);
        if (!$enrollment || !$enrollment->course->subjects->contains($request->subject_id)) {
            return response()->json(['errors' => ['subject_id' => ['This subject does not belong to the student\'s enrolled course.']]], 422);
        }
        
        // Check for duplicate attendance record based on unique constraint in migration
        $uniqueCheckAttributes = [
            'enrollment_id' => $request->enrollment_id,
            'subject_id' => $request->subject_id,
            'session_date' => $request->session_date,
        ];
        if ($request->filled('session_time')) {
            $uniqueCheckAttributes['session_time'] = $request->session_time;
        } else {
             // If session_time can be null in DB and part of unique key, handle query accordingly
             // For simplicity, if session_time is not provided, we assume one record per day.
             // Adjust if your unique key `student_subject_session_attendance_unique` always requires session_time or handles null.
             // If your unique key includes session_time and it can be null, then this check is tricky.
             // The migration defined it as: unique(['enrollment_id', 'subject_id', 'session_date', 'session_time']
             // So session_time IS part of the unique key. It might be better to require session_time or default it if not provided.
             // For now, let's assume if not provided, it's a general daily record. Or require it.
             // Let's assume if it's not provided, it's not part of the check for this example.
             // Better: Ensure your DB unique constraint handles nullable session_time as you expect.
             // For this code, let's require session_time for the unique check to be robust if it's part of the key.
             // If session_time is truly optional and can be null, the unique key might need reconsideration or a more complex check.
             // For this example, let's proceed assuming it's part of the uniqueness if provided.
        }

        $existingAttendance = Attendance::where($uniqueCheckAttributes)->first();
        if ($existingAttendance) {
            return response()->json(['message' => 'Attendance for this student, subject, and session already recorded.'], 409); // Conflict
        }


        $attendanceData = $validator->validated();
        $attendanceData['marked_by_user_id'] = Auth::id(); // Assumes admin/teacher is logged in

        $attendance = Attendance::create($attendanceData);

        return response()->json([
            'message' => 'Attendance recorded successfully.',
            'attendance' => $attendance->load(['enrollment.student:id,name', 'subject:id,name', 'markedBy:id,name'])
        ], 201);
    }

    /**
     * Display the specified attendance record (Admin action).
     */
    public function show(Attendance $attendance)
    {   
        $this->authorize('view', $attendance);
        // Admin only - protected by isAdmin middleware on the route group
        return response()->json($attendance->load(['enrollment.student:id,name,email', 'subject:id,name,code', 'markedBy:id,name']));
    }

    /**
     * Update the specified attendance record in storage (Admin action).
     */
    public function update(Request $request, Attendance $attendance)
    {   
        $this->authorize('update', $attendance);
        // Admin only - protected by isAdmin middleware on the route group
        $validator = Validator::make($request->all(), [
            // enrollment_id, subject_id, session_date, session_time usually define the record and aren't changed.
            // One would typically delete and re-create if those were wrong.
            'status' => 'sometimes|required|string|in:present,absent,late,excused',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = $validator->validated();
        $updateData['marked_by_user_id'] = Auth::id(); // Update who last marked it

        $attendance->update($updateData);

        return response()->json([
            'message' => 'Attendance record updated successfully.',
            'attendance' => $attendance->fresh()->load(['enrollment.student:id,name', 'subject:id,name', 'markedBy:id,name'])
        ]);
    }

    /**
     * Remove the specified attendance record from storage (Admin action).
     */
    public function destroy(Attendance $attendance)
    {   
        $this->authorize('delete', $attendance);
        // Admin only - protected by isAdmin middleware on the route group
        try {
            $attendance->delete();
            return response()->json(['message' => 'Attendance record deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting attendance record.'], 500);
        }
    }

    /**
     * Display a listing of the authenticated student's attendance records.
     * Filterable by subject_id, date range.
     */
    public function myAttendance(Request $request)
    {
        $student = Auth::user();

        $validator = Validator::make($request->all(), [
            'subject_id' => 'nullable|integer|exists:subjects,id',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendances = Attendance::whereHas('enrollment', function ($query) use ($student) {
                $query->where('user_id', $student->id);
            })
            ->with(['subject:id,name,code', 'enrollment.course:id,name'])
            ->when($request->query('subject_id'), function ($query, $subjectId) {
                return $query->where('subject_id', $subjectId);
            })
            ->when($request->query('start_date'), function ($query, $startDate) {
                return $query->where('session_date', '>=', $startDate);
            })
            ->when($request->query('end_date'), function ($query, $endDate) {
                return $query->where('session_date', '<=', $endDate);
            })
            ->orderBy('session_date', 'desc')
            ->orderBy('session_time', 'desc') // If session_time is used
            ->paginate(20);

        return response()->json($attendances);
    }
}
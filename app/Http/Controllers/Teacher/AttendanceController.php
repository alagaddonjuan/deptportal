<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Redirect to the attendance creation flow, which is the main index action.
     */
    public function index()
    {
        return redirect()->route('teacher.attendance.create');
    }

    /**
     * Show the form for selecting a subject and date.
     */
    public function create()
    {
        $subjects = Subject::where('teacher_id', Auth::id())->get();
        return view('teacher.attendance.create', compact('subjects'));
    }

    /**
     * Show the roster of students for attendance marking.
     */
    public function showRoster(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'session_date' => 'required|date',
        ]);

        $subject = Subject::with('course')->find($request->subject_id);

        // Authorization: Ensure the teacher is assigned to this subject
        if ($subject->teacher_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $students = User::whereHas('enrollments', function ($query) use ($subject) {
            $query->where('course_id', $subject->course_id);
        })->get();

        return view('teacher.attendance.roster', [
            'students' => $students,
            'subject' => $subject,
            'session_date' => $request->session_date,
        ]);
    }

    /**
     * Store the attendance records.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'session_date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,excused',
        ]);

        foreach ($request->attendance as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'student_user_id' => $studentId,
                    'subject_id' => $request->subject_id,
                    'session_date' => $request->session_date,
                ],
                [
                    'status' => $status,
                    'remarks' => $request->remarks[$studentId] ?? null,
                ]
            );
        }

        return redirect()->route('teacher.index')->with('success', 'Attendance recorded successfully.');
    }
}

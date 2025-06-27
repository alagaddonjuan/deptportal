<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassSchedule;
use App\Models\User;

class TimetableController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // Get the course the student is enrolled in
        $enrollment = $student->enrollments()->with('course.subjects')->first();

        if (!$enrollment) {
            $schedules = collect(); // Return an empty collection if not enrolled
        } else {
            // Get the IDs of the subjects for that course
            $subjectIds = $enrollment->course->subjects->pluck('id');

            // Fetch the class schedules for those subjects
            $schedules = ClassSchedule::whereIn('subject_id', $subjectIds)
                ->with(['subject', 'teacher'])
                ->get();
        }
            
        return view('student.timetable.index', compact('schedules'));
    }
}
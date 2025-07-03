<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard for the authenticated student.
     */
    public function index()
    {
        $student = Auth::user();
        $dayOfWeek = Carbon::now()->dayOfWeekIso;

        // Get the student's course enrollment
        $enrollment = $student->enrollments()->with('course.subjects')->first();

        $todaysSchedule = collect();
        if ($enrollment) {
            // Get the IDs of the subjects for the student's course
            $subjectIds = $enrollment->course->subjects->pluck('id');

            // Fetch today's schedule for those subjects
            $todaysSchedule = ClassSchedule::whereIn('subject_id', $subjectIds)
                ->where('day_of_week', $dayOfWeek)
                ->with(['subject', 'teacher'])
                ->orderBy('start_time')
                ->get();
        }

        // Fetch the 5 most recent grades
        $recentGrades = $student->grades()
            ->with('assessment.subject')
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('student.dashboard', compact('todaysSchedule', 'recentGrades'));
    }
}

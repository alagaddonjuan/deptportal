<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard for the authenticated teacher.
     */
    public function index()
    {
        $teacherId = Auth::id();
        
        // Get the current day of the week (1 for Monday, 7 for Sunday)
        $dayOfWeek = Carbon::now()->dayOfWeekIso;

        // Fetch today's schedule for the teacher
        $todaysSchedule = ClassSchedule::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->with('subject.course') // Eager load relationships for efficiency
            ->orderBy('start_time')
            ->get();

        return view('teacher.dashboard', compact('todaysSchedule'));
    }
}
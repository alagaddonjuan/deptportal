<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassSchedule;

class TimetableController extends Controller
{
    /**
     * Display the authenticated teacher's timetable.
     */
    public function index()
    {
        $teacherId = Auth::id();

        $schedules = ClassSchedule::where('teacher_id', $teacherId)
            ->with('subject.course')
            ->get();

        $timetable = [];
        $timeSlots = [];
        foreach ($schedules as $schedule) {
            $time = date('H:i', strtotime($schedule->start_time)) . ' - ' . date('H:i', strtotime($schedule->end_time));
            if (!in_array($time, $timeSlots)) {
                $timeSlots[] = $time;
            }
            $timetable[$time][$schedule->day_of_week] = $schedule;
        }
        sort($timeSlots);
        
        $daysOfWeek = [ 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday' ];

        return view('teacher.timetable.index', compact('timetable', 'timeSlots', 'daysOfWeek'));
    }
}
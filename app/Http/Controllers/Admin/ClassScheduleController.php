<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class ClassScheduleController extends Controller
{
    public function index()
    {
        $schedules = ClassSchedule::with(['subject.course', 'teacher'])->get();
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

        return view('admin.timetables.index', compact('timetable', 'timeSlots', 'daysOfWeek'));
    }

    public function create()
    {
        $subjects = Subject::all();
        $teachers = User::whereHas('role', fn($q) => $q->where('slug', 'teacher'))->get();
        $daysOfWeek = [ 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday' ];

        return view('admin.timetables.create', compact('subjects', 'teachers', 'daysOfWeek'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
        ]);

        // Check for conflicts
        if ($this->hasConflict($validated)) {
            return back()->withInput()->with('error', 'A scheduling conflict was detected for this teacher or location at the selected time.');
        }

        ClassSchedule::create($validated);
        return redirect()->route('admin.timetable.index')->with('success', 'Timetable entry created successfully.');
    }

    public function edit(ClassSchedule $timetable) // Changed variable name to match route model binding
    {
        $subjects = Subject::all();
        $teachers = User::whereHas('role', fn($q) => $q->where('slug', 'teacher'))->get();
        $daysOfWeek = [ 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday' ];

        return view('admin.timetables.edit', [
            'schedule' => $timetable,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'daysOfWeek' => $daysOfWeek
        ]);
    }

    public function update(Request $request, ClassSchedule $timetable) // Changed variable name
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
        ]);

        // Check for conflicts, excluding the current entry being edited
        if ($this->hasConflict($validated, $timetable->id)) {
             return back()->withInput()->with('error', 'A scheduling conflict was detected for this teacher or location at the selected time.');
        }

        $timetable->update($validated);
        return redirect()->route('admin.timetable.index')->with('success', 'Timetable entry updated successfully.');
    }

    public function destroy(ClassSchedule $timetable) // Changed variable name
    {
        $timetable->delete();
        return redirect()->route('admin.timetable.index')->with('success', 'Timetable entry deleted successfully.');
    }

    // Helper function to check for conflicts
    private function hasConflict(array $validatedData, $excludeId = null): bool
    {
        $query = ClassSchedule::where('day_of_week', $validatedData['day_of_week'])
            ->where(function ($query) use ($validatedData) {
                $query->where(function ($q) use ($validatedData) {
                    $q->where('teacher_id', $validatedData['teacher_id'])
                      ->where('start_time', '<', $validatedData['end_time'])
                      ->where('end_time', '>', $validatedData['start_time']);
                })->orWhere(function ($q) use ($validatedData) {
                    $q->where('location', $validatedData['location'])
                      ->where('start_time', '<', $validatedData['end_time'])
                      ->where('end_time', '>', $validatedData['start_time']);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
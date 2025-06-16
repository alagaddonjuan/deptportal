<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Subject;
use App\Models\User;
use App\Models\Enrollment; // Needed for myTimetable
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClassScheduleController extends Controller
{
    /**
     * Display a listing of the resource for an Admin.
     * Filterable by subject_id, teacher_id, day_of_week, academic_year_term.
     */
    public function index(Request $request)
    {
        // Admin only - protected by isAdmin middleware on the route group
        $validator = Validator::make($request->all(), [
            'subject_id' => 'nullable|integer|exists:subjects,id',
            'teacher_id' => 'nullable|integer|exists:users,id',
            'day_of_week' => 'nullable|integer|between:1,7',
            'academic_year_term' => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $schedules = ClassSchedule::with(['subject:id,name,code', 'teacher:id,name'])
            ->when($request->query('subject_id'), function ($query, $subjectId) {
                return $query->where('subject_id', $subjectId);
            })
            ->when($request->query('teacher_id'), function ($query, $teacherId) {
                return $query->where('teacher_id', $teacherId);
            })
            ->when($request->query('day_of_week'), function ($query, $dayOfWeek) {
                return $query->where('day_of_week', $dayOfWeek);
            })
            ->when($request->query('academic_year_term'), function ($query, $term) {
                return $query->where('academic_year_term', $term);
            })
            ->orderBy('academic_year_term')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(20);

        return response()->json($schedules);
    }

    /**
     * Store a newly created resource in storage by an Admin.
     */
    public function store(Request $request)
    {
        // Admin only - protected by isAdmin middleware on the route group
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|integer|exists:subjects,id',
            'teacher_id' => 'required|integer|exists:users,id',
            'day_of_week' => 'required|integer|between:1,7', // 1=Monday, 7=Sunday
            'start_time' => 'required|date_format:H:i', // Use H:i for 24-hour format without seconds
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:100',
            'academic_year_term' => 'required|string|max:30',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Ensure the assigned teacher_id actually has the 'teacher' role
        $teacher = User::find($request->teacher_id);
        if (!$teacher || !$teacher->hasRole('teacher')) {
            return response()->json(['errors' => ['teacher_id' => 'The selected user is not a valid teacher.']], 422);
        }
        
        // --- Basic Conflict Checking ---
        $teacherConflict = ClassSchedule::where('teacher_id', $request->teacher_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('academic_year_term', $request->academic_year_term)
            ->where(function ($query) use ($request) {
                $query->where(function($q) use ($request) { // Slot starts within new slot
                    $q->where('start_time', '>=', $request->start_time)
                      ->where('start_time', '<', $request->end_time);
                })->orWhere(function($q) use ($request) { // Slot ends within new slot
                    $q->where('end_time', '>', $request->start_time)
                      ->where('end_time', '<=', $request->end_time);
                })->orWhere(function($q) use ($request) { // Slot encapsulates new slot
                    $q->where('start_time', '<=', $request->start_time)
                      ->where('end_time', '>=', $request->end_time);
                });
            })->exists();

        if ($teacherConflict) {
            return response()->json(['message' => 'Conflict: Teacher is already scheduled at this time.'], 409);
        }

        if ($request->filled('location')) {
            $locationConflict = ClassSchedule::where('location', $request->location)
                ->where('day_of_week', $request->day_of_week)
                ->where('academic_year_term', $request->academic_year_term)
                ->where(function ($query) use ($request) {
                     $query->where(function($q) use ($request) {
                        $q->where('start_time', '>=', $request->start_time)
                          ->where('start_time', '<', $request->end_time);
                    })->orWhere(function($q) use ($request) {
                        $q->where('end_time', '>', $request->start_time)
                          ->where('end_time', '<=', $request->end_time);
                    })->orWhere(function($q) use ($request) {
                        $q->where('start_time', '<=', $request->start_time)
                          ->where('end_time', '>=', $request->end_time);
                    });
                })->exists();

            if ($locationConflict) {
                return response()->json(['message' => 'Conflict: Location is already booked at this time.'], 409);
            }
        }

        $classSchedule = ClassSchedule::create($validator->validated());

        return response()->json([
            'message' => 'Class schedule created successfully.',
            'class_schedule' => $classSchedule->load(['subject:id,name', 'teacher:id,name'])
        ], 201);
    }

    /**
     * Display the specified resource (Admin action).
     */
    public function show(ClassSchedule $classSchedule)
    {
        return response()->json($classSchedule->load(['subject:id,name,code', 'teacher:id,name']));
    }

    /**
     * Update the specified resource in storage (Admin action).
     */
    public function update(Request $request, ClassSchedule $classSchedule)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'sometimes|required|integer|exists:subjects,id',
            'teacher_id' => 'sometimes|required|integer|exists:users,id',
            'day_of_week' => 'sometimes|required|integer|between:1,7',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:100',
            'academic_year_term' => 'sometimes|required|string|max:30',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        if ($request->has('teacher_id') && $request->teacher_id != $classSchedule->teacher_id) {
            $teacher = User::find($request->teacher_id);
            if (!$teacher || !$teacher->hasRole('teacher')) {
                return response()->json(['errors' => ['teacher_id' => 'The selected user is not a valid teacher.']], 422);
            }
        }

        $fieldsToCheckForConflict = [
            'teacher_id' => $request->input('teacher_id', $classSchedule->teacher_id),
            'location' => $request->input('location', $classSchedule->location),
            'day_of_week' => $request->input('day_of_week', $classSchedule->day_of_week),
            'academic_year_term' => $request->input('academic_year_term', $classSchedule->academic_year_term),
            'start_time' => $request->input('start_time', $classSchedule->start_time),
            'end_time' => $request->input('end_time', $classSchedule->end_time),
        ];

        // Teacher Conflict Check (excluding current record)
        $teacherConflict = ClassSchedule::where('teacher_id', $fieldsToCheckForConflict['teacher_id'])
            ->where('day_of_week', $fieldsToCheckForConflict['day_of_week'])
            ->where('academic_year_term', $fieldsToCheckForConflict['academic_year_term'])
            ->where('id', '!=', $classSchedule->id)
            ->where(function ($query) use ($fieldsToCheckForConflict) {
                $query->where(function($q) use ($fieldsToCheckForConflict) {
                    $q->where('start_time', '>=', $fieldsToCheckForConflict['start_time'])->where('start_time', '<', $fieldsToCheckForConflict['end_time']);
                })->orWhere(function($q) use ($fieldsToCheckForConflict) {
                    $q->where('end_time', '>', $fieldsToCheckForConflict['start_time'])->where('end_time', '<=', $fieldsToCheckForConflict['end_time']);
                })->orWhere(function($q) use ($fieldsToCheckForConflict) {
                    $q->where('start_time', '<=', $fieldsToCheckForConflict['start_time'])->where('end_time', '>=', $fieldsToCheckForConflict['end_time']);
                });
            })->exists();

        if ($teacherConflict) {
            return response()->json(['message' => 'Conflict: Teacher is already scheduled at this time.'], 409);
        }

        // Location Conflict Check (excluding current record, only if location is relevant)
        if ($fieldsToCheckForConflict['location']) { 
            $locationConflict = ClassSchedule::where('location', $fieldsToCheckForConflict['location'])
                ->where('day_of_week', $fieldsToCheckForConflict['day_of_week'])
                ->where('academic_year_term', $fieldsToCheckForConflict['academic_year_term'])
                ->where('id', '!=', $classSchedule->id)
                ->where(function ($query) use ($fieldsToCheckForConflict) { // Outer function for grouping OR conditions
                     $query->where(function($q) use ($fieldsToCheckForConflict) {
                        $q->where('start_time', '>=', $fieldsToCheckForConflict['start_time'])->where('start_time', '<', $fieldsToCheckForConflict['end_time']);
                    })->orWhere(function($q) use ($fieldsToCheckForConflict) {
                        $q->where('end_time', '>', $fieldsToCheckForConflict['start_time'])->where('end_time', '<=', $fieldsToCheckForConflict['end_time']);
                    })->orWhere(function($q) use ($fieldsToCheckForConflict) {
                        $q->where('start_time', '<=', $fieldsToCheckForConflict['start_time'])->where('end_time', '>=', $fieldsToCheckForConflict['end_time']);
                    });
                }) // <<< THIS WAS THE MISSING CLOSING PARENTHESIS
                ->exists();
            if ($locationConflict) {
                return response()->json(['message' => 'Conflict: Location is already booked at this time.'], 409);
            }
        }

        $classSchedule->update($validator->validated());

        return response()->json([
            'message' => 'Class schedule updated successfully.',
            'class_schedule' => $classSchedule->fresh()->load(['subject:id,name', 'teacher:id,name'])
        ]);
    }

    /**
     * Remove the specified resource from storage (Admin action).
     */
    public function destroy(ClassSchedule $classSchedule)
    {
        try {
            $classSchedule->delete();
            return response()->json(['message' => 'Class schedule deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting class schedule.'], 500);
        }
    }

    /**
     * Display the timetable for the authenticated student.
     */
    public function myTimetable(Request $request)
    {
        $student = Auth::user();

        $enrolledSubjectIds = Enrollment::where('user_id', $student->id)
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->join('subjects', 'subjects.course_id', '=', 'courses.id')
            ->where('courses.status', 'active') 
            ->where('subjects.status', 'active') 
            ->distinct()
            ->pluck('subjects.id');

        if ($enrolledSubjectIds->isEmpty()) {
            return response()->json([]); 
        }
        
        $validator = Validator::make($request->all(), [
            'academic_year_term' => 'nullable|string|max:30',
            'day_of_week' => 'nullable|integer|between:1,7',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $timetable = ClassSchedule::whereIn('subject_id', $enrolledSubjectIds)
            ->with(['subject:id,name,code', 'teacher:id,name', 'subject.course:id,name'])
            ->when($request->query('academic_year_term'), function($query, $term){
                return $query->where('academic_year_term', $term);
            })
            ->when($request->query('day_of_week'), function($query, $day){
                return $query->where('day_of_week', $day);
            })
            ->orderBy('academic_year_term')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json($timetable);
    }

    /**
     * Display the timetable for the authenticated teacher.
     */
    public function teacherTimetable(Request $request)
    {
        $teacher = Auth::user();
        if (!$teacher->hasRole('teacher')) {
            return response()->json(['message' => 'This timetable is for teachers only.'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'academic_year_term' => 'nullable|string|max:30',
            'day_of_week' => 'nullable|integer|between:1,7',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $timetable = ClassSchedule::where('teacher_id', $teacher->id)
            ->with(['subject:id,name,code', 'subject.course:id,name'])
             ->when($request->query('academic_year_term'), function($query, $term){
                return $query->where('academic_year_term', $term);
            })
            ->when($request->query('day_of_week'), function($query, $day){
                return $query->where('day_of_week', $day);
            })
            ->orderBy('academic_year_term')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json($timetable);
    }
}
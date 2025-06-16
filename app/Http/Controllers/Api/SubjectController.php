<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Course; // To validate course_id
use App\Models\User;   // To validate teacher_id
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     * (Authenticated users)
     * Can be filtered by course_id. e.g., GET /api/subjects?course_id=1
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'nullable|integer|exists:courses,id',
            'teacher_id' => 'nullable|integer|exists:users,id',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subjects = Subject::query()
            ->with(['course:id,name,code', 'teacher:id,name']) // Eager load course and teacher name/id
            ->when($request->query('course_id'), function ($query, $courseId) {
                return $query->where('course_id', $courseId);
            })
            ->when($request->query('teacher_id'), function ($query, $teacherId) {
                return $query->where('teacher_id', $teacherId);
            })
            ->when($request->query('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        return response()->json($subjects);
    }

    /**
     * Store a newly created resource in storage.
     * (Admin only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|integer|exists:courses,id',
            'teacher_id' => 'nullable|integer|exists:users,id', // Ensure teacher exists and has 'teacher' role if possible
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'description' => 'nullable|string',
            'credits' => 'nullable|numeric|min:0', // Changed to numeric to allow decimals
            'type' => 'nullable|string|max:50|in:Core,Elective,Practical,Theory',
            'semester_term' => 'nullable|integer|min:1',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Optional: Further validation to ensure teacher_id belongs to a user with 'teacher' role
        if ($request->filled('teacher_id')) {
            $teacher = User::find($request->teacher_id);
            if (!$teacher || !$teacher->hasRole('teacher')) { // Assumes hasRole method on User model
                return response()->json(['errors' => ['teacher_id' => ['The selected teacher is invalid or not a teacher.']]], 422);
            }
        }

        $subject = Subject::create($validator->validated());

        return response()->json([
            'message' => 'Subject created successfully.',
            'subject' => $subject->load(['course:id,name', 'teacher:id,name'])
        ], 201);
    }

    /**
     * Display the specified resource.
     * (Authenticated users)
     */
    public function show(Subject $subject)
    {
        return response()->json($subject->load(['course:id,name,code', 'teacher:id,name']));
    }

    /**
     * Update the specified resource in storage.
     * (Admin only)
     */
    public function update(Request $request, Subject $subject)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'sometimes|required|integer|exists:courses,id',
            'teacher_id' => 'nullable|integer|exists:users,id',
            'name' => 'sometimes|required|string|max:255',
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('subjects')->ignore($subject->id),
            ],
            'description' => 'nullable|string',
            'credits' => 'nullable|numeric|min:0',
            'type' => 'nullable|string|max:50|in:Core,Elective,Practical,Theory',
            'semester_term' => 'nullable|integer|min:1',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Optional: Further validation to ensure teacher_id belongs to a user with 'teacher' role
        if ($request->filled('teacher_id') && $request->teacher_id != $subject->teacher_id) { // Check if teacher_id is being changed
            $teacher = User::find($request->teacher_id);
            if (!$teacher || !$teacher->hasRole('teacher')) {
                return response()->json(['errors' => ['teacher_id' => ['The selected teacher is invalid or not a teacher.']]], 422);
            }
        }

        $subject->update($validator->validated());

        return response()->json([
            'message' => 'Subject updated successfully.',
            'subject' => $subject->fresh()->load(['course:id,name', 'teacher:id,name'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * (Admin only)
     */
    public function destroy(Subject $subject)
    {
        // Consider if there are other dependencies on subjects (e.g., enrollments, grades)
        // before allowing deletion, or handle via database constraints.
        try {
            $subject->delete();
            return response()->json(['message' => 'Subject deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting subject. It might be associated with other data.'], 500);
        }
    }
}
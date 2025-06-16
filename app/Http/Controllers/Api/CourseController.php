<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     * (Authenticated users)
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Add filtering (e.g., by level, status) and sorting if needed
        $courses = Course::query()
            ->when($request->query('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->query('level'), function ($query, $level) {
                return $query->where('level', 'like', '%' . $level . '%');
            })
            ->latest() // Default sort by latest
            ->paginate(15); // Paginate results

        return response()->json($courses);
    }

    /**
     * Store a newly created resource in storage.
     * (Admin only)
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:courses,code',
            'description' => 'nullable|string',
            'level' => 'nullable|string|max:100',
            'duration_years' => 'nullable|integer|min:0',
            'status' => 'sometimes|string|in:active,inactive,archived',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::create($validator->validated());

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $course
        ], 201);
    }

    /**
     * Display the specified resource.
     * (Authenticated users)
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Course $course)
    {
        // You might want to load related subjects by default:
        // $course->load('subjects');
        return response()->json($course);
    }

    /**
     * Update the specified resource in storage.
     * (Admin only)
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('courses')->ignore($course->id),
            ],
            'description' => 'nullable|string',
            'level' => 'nullable|string|max:100',
            'duration_years' => 'nullable|integer|min:0',
            'status' => 'sometimes|string|in:active,inactive,archived',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course->update($validator->validated());

        return response()->json([
            'message' => 'Course updated successfully.',
            'course' => $course
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * (Admin only)
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Course $course)
    {
        // Consider what happens to subjects if a course is deleted.
        // If onDelete('cascade') is set in the subjects migration for course_id, subjects will be auto-deleted.
        // If not, you might need to handle related subjects here (e.g., prevent deletion if subjects exist, or delete them manually).
        
        try {
            $course->delete();
            return response()->json(['message' => 'Course deleted successfully.'], 200);
            // Or return response()->json(null, 204); // No Content
        } catch (\Exception $e) {
            // Log error: \Log::error('Error deleting course: ' . $e->getMessage());
            // Check for foreign key constraint violations if subjects are not set to cascade delete
            // and still exist for this course.
            return response()->json(['message' => 'Error deleting course. It might be associated with other data (e.g., subjects).'], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::latest()->paginate(10);
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:courses',
            'description' => 'nullable|string',
            'level' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,archived',
        ]);
        
        Course::create($validated);
        return redirect()->route('admin.courses.index')->with('success', 'Course created successfully.');
    }
    
    public function edit(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
         $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:courses,code,'.$course->id,
            'description' => 'nullable|string',
            'level' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,archived',
        ]);

        $course->update($validated);
        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        // Add a check to prevent deleting courses with subjects later if needed
        // if ($course->subjects()->count() > 0) {
        //     return back()->with('error', 'Cannot delete a course that has subjects.');
        // }

        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }
}
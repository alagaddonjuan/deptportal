<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subjects = Subject::with(['course', 'teacher'])->latest()->paginate(10);
        return view('admin.subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::where('status', 'active')->get();
        $teachers = User::whereHas('role', function ($query) {
            $query->where('slug', 'teacher');
        })->where('status', 'active')->get();

        return view('admin.subjects.create', compact('courses', 'teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:subjects',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'nullable|exists:users,id',
            'credits' => 'required|integer|min:0',
            'type' => 'required|in:core,elective',
        ]);

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')->with('success', 'Subject created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject)
    {
        $courses = Course::where('status', 'active')->get();
        $teachers = User::whereHas('role', function ($query) {
            $query->where('slug', 'teacher');
        })->where('status', 'active')->get();

        return view('admin.subjects.edit', compact('subject', 'courses', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:subjects,code,'.$subject->id,
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'nullable|exists:users,id',
            'credits' => 'required|integer|min:0',
            'type' => 'required|in:core,elective',
        ]);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        // Add checks here later if needed, e.g., if the subject has grades/assignments
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Subject deleted successfully.');
    }
}

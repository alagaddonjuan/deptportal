<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = Enrollment::with(['student', 'course'])->latest()->paginate(10);
        return view('admin.enrollments.index', compact('enrollments'));
    }

    public function create()
    {
        // Fetch only active students and courses for the dropdowns
        $students = User::whereHas('role', function ($query) {
            $query->where('slug', 'student');
        })->where('status', 'active')->get();

        $courses = Course::where('status', 'active')->get();

        return view('admin.enrollments.create', compact('students', 'courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            // Add a unique rule to prevent a student from being enrolled in the same course twice
            'status' => 'required|in:enrolled,withdrawn,completed',
        ], [
            'student_user_id.unique' => 'This student is already enrolled in this course.',
        ]);

        // Custom validation to check for duplicate enrollment
        $existingEnrollment = Enrollment::where('student_user_id', $validated['student_user_id'])
                                        ->where('course_id', $validated['course_id'])
                                        ->first();

        if ($existingEnrollment) {
            return back()->withInput()->with('error', 'This student is already enrolled in this course.');
        }

        Enrollment::create($validated);

        return redirect()->route('admin.enrollments.index')->with('success', 'Student enrolled successfully.');
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return redirect()->route('admin.enrollments.index')->with('success', 'Enrollment removed successfully.');
    }
}

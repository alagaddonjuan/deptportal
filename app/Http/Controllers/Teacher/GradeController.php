<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    /**
     * Redirect to the grade creation flow, which is the main index action.
     */
    public function index()
    {
        return redirect()->route('teacher.grades.create');
    }

    /**
     * Show the form for selecting a subject and assessment.
     */
    public function create()
    {
        $subjects = Subject::where('teacher_id', Auth::id())->get();
        return view('teacher.grades.create', compact('subjects'));
    }

    /**
     * Show the roster of students for grade entry.
     */
    public function showRoster(Request $request)
    {
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
        ]);

        $assessment = Assessment::with('subject.course')->findOrFail($request->assessment_id);

        // Authorization: Ensure the teacher is assigned to this subject
        if ($assessment->subject->teacher_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Get students enrolled in the subject's course
        $students = User::whereHas('enrollments', function ($query) use ($assessment) {
            $query->where('course_id', $assessment->subject->course_id)
                  ->where('status', 'enrolled'); // Ensure only currently enrolled students are shown
        })->get();
        
        // Eager load existing grades to pre-fill the form if they exist
        $existingGrades = Grade::where('assessment_id', $assessment->id)
            ->whereIn('student_user_id', $students->pluck('id'))
            ->pluck('marks_obtained', 'student_user_id');

        return view('teacher.grades.roster', compact('students', 'assessment', 'existingGrades'));
    }

    /**
     * Store or update the grade records.
     */
    public function store(Request $request)
    {
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'grades' => 'required|array',
            'grades.*' => ['nullable', 'numeric', 'min:0'], // Validate each grade entry
        ]);

        $assessment = Assessment::find($request->assessment_id);

        foreach ($request->grades as $studentId => $marks) {
            // Skip if no marks were entered for this student
            if (is_null($marks)) {
                continue;
            }

            // Validate marks against the assessment's max_marks
            if ($marks > $assessment->max_marks) {
                 return back()->withInput()->with('error', "Marks for a student cannot exceed the maximum of {$assessment->max_marks}.");
            }
            
            Grade::updateOrCreate(
                [
                    'student_user_id' => $studentId,
                    'assessment_id' => $request->assessment_id,
                ],
                [
                    'marks_obtained' => $marks,
                ]
            );
        }

        return redirect()->route('teacher.index')->with('success', 'Grades have been saved successfully.');
    }
}
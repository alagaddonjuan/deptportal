<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    /**
     * Show the form for creating a new assignment.
     */
    public function create()
    {
        $subjects = Subject::where('teacher_id', Auth::id())->get();
        return view('teacher.assignments.create', compact('subjects'));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'max_marks' => 'nullable|integer|min:0',
            'assignment_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10240', // Max 10MB
        ]);

        // Authorization check: Make sure the teacher is assigned to the selected subject
        $subject = Subject::findOrFail($validated['subject_id']);
        if ($subject->teacher_id !== Auth::id()) {
            abort(403, 'You are not authorized to create an assignment for this subject.');
        }
        
        $validated['teacher_id'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('assignment_file')) {
            $path = $request->file('assignment_file')->store('assignments', 'public');
            $validated['file_path'] = $path;
        }

        Assignment::create($validated);

        return redirect()->route('teacher.index')->with('success', 'Assignment created successfully.');
    }
}


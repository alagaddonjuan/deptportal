<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display a listing of the authenticated parent's linked children.
     */
    public function myChildren()
    {
        $parent = Auth::user();
        $linkedStudents = $parent->linkedStudents()->with('student')->get();
        return view('parent.my-children.index', compact('linkedStudents'));
    }

    /**
     * Display the grades for a specific linked child.
     */
    public function showChildGrades(User $student)
    {
        // Authorization Check: Ensure the logged-in parent is actually linked to this student
        $isLinked = Auth::user()->linkedStudents()->where('student_user_id', $student->id)->exists();

        if (!$isLinked) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        // Fetch the grades for the specified student
        $grades = $student->grades()->with('assessment.subject')->latest('updated_at')->paginate(15);

        return view('parent.my-children.grades', compact('student', 'grades'));
    }
}
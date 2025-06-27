<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    /**
     * Display the grades for the authenticated student.
     */
    public function index()
    {
        $student = Auth::user();
        
        // Eager load the relationships to prevent N+1 issues
        $grades = $student->grades()->with('assessment.subject')->latest('updated_at')->paginate(15);

        return view('student.grades.index', compact('grades'));
    }
}
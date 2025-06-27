<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display the attendance records for the authenticated student.
     */
    public function index()
    {
        $student = Auth::user();
        
        // Eager load the subject relationship to prevent N+1 issues and order by the most recent date
        $attendances = $student->attendances()->with('subject')->latest('session_date')->paginate(15);

        return view('student.attendance.index', compact('attendances'));
    }
}
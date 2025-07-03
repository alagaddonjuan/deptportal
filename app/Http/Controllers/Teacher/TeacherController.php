<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Display the teacher tools hub page.
     */
    public function index()
    {
        return view('teacher.index');
    }
}
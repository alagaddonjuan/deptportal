<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    /**
     * Display the teacher tools hub page.
     */
    public function index()
    {
        return view('teacher.tools.index');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the main admin dashboard with stats and charts.
     */
    public function index()
    {
        // Data for Stat Cards
        $stats = [
            'students' => User::whereHas('role', fn($q) => $q->where('slug', 'student'))->count(),
            'teachers' => User::whereHas('role', fn($q) => $q->where('slug', 'teacher'))->count(),
            'courses' => Course::count(),
            'subjects' => Subject::count(),
        ];

        // Data for User Roles Chart
        $roles = Role::withCount('users')->get();
        $chartData = [
            'labels' => $roles->pluck('name'),
            'data' => $roles->pluck('users_count'),
        ];
        
        return view('admin.index', compact('stats', 'chartData'));
    }
}
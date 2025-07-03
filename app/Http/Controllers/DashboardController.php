<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the appropriate dashboard for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();

        // If the user is an admin, redirect them to their dedicated admin dashboard route.
        if ($user->isAdmin()) {
            return redirect()->route('admin.index');
        }

        // If the user is a teacher, redirect them to their dedicated teacher dashboard route.
        if ($user->isTeacher()) {
            return redirect()->route('teacher.index');
        }
        
        // If the user is a parent, redirect them to their list of children.
        if ($user->isParent()) {
            return redirect()->route('parent.my-children');
        }

        // All other users (like students) will see the default generic dashboard view.
        return view('dashboard');
    }
}

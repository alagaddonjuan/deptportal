<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuardianStudentLink; // <-- This was the missing line
use App\Models\User;
use App\Models\Course; // This is not strictly needed here but good to keep for consistency
use Illuminate\Http\Request;

class GuardianStudentLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $links = GuardianStudentLink::with(['guardian', 'student'])->latest()->paginate(15);
        return view('admin.guardian-links.index', compact('links'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch only active users with the 'parent' role
        $guardians = User::whereHas('role', function ($query) {
            $query->where('slug', 'parent');
        })->where('status', 'active')->get();

        // Fetch only active users with the 'student' role
        $students = User::whereHas('role', function ($query) {
            $query->where('slug', 'student');
        })->where('status', 'active')->get();

        return view('admin.guardian-links.create', compact('guardians', 'students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guardian_user_id' => 'required|exists:users,id',
            'student_user_id' => 'required|exists:users,id',
            'relationship_type' => 'required|string|max:255',
        ]);

        // Custom validation to check for duplicate links
        $existingLink = GuardianStudentLink::where('guardian_user_id', $validated['guardian_user_id'])
                                           ->where('student_user_id', $validated['student_user_id'])
                                           ->first();

        if ($existingLink) {
            return back()->withInput()->with('error', 'This guardian is already linked to this student.');
        }

        GuardianStudentLink::create($validated);

        return redirect()->route('admin.guardian-student-links.index')->with('success', 'Link created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GuardianStudentLink $guardian_student_link)
    {
        $guardian_student_link->delete();
        return redirect()->route('admin.guardian-student-links.index')->with('success', 'Link removed successfully.');
    }
}
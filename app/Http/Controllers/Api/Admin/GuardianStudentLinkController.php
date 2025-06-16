<?php

namespace App\Http\Controllers\Api\Admin; // Ensure namespace is correct

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // For the pivot table interaction if not using a dedicated model
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GuardianStudentLinkController extends Controller
{
    /**
     * Display a listing of guardian-student links.
     * Filterable by guardian_user_id or student_user_id.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guardian_user_id' => 'nullable|integer|exists:users,id',
            'student_user_id' => 'nullable|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // We query the User model and eager load the relationships
        // This is an example, a more direct query on the pivot might be more performant for just links
        
        $query = User::query(); // Starting with User query for context

        if ($request->filled('guardian_user_id')) {
            $query->where('id', $request->guardian_user_id)
                  ->with(['children' => function ($q) {
                      $q->withPivot('relationship_type', 'is_primary_contact', 'can_access_records');
                  }, 'children.profile:user_id,first_name,last_name']); // Load children with pivot data
            $links = $query->first();
            if ($links) {
                return response()->json($links->children); // Return only the children collection
            }
            return response()->json([]);

        } elseif ($request->filled('student_user_id')) {
            $query->where('id', $request->student_user_id)
                  ->with(['guardians' => function ($q) {
                      $q->withPivot('relationship_type', 'is_primary_contact', 'can_access_records');
                  }, 'guardians.profile:user_id,first_name,last_name']); // Load guardians with pivot data
            $links = $query->first();
            if ($links) {
                return response()->json($links->guardians); // Return only the guardians collection
            }
            return response()->json([]);
        } else {
            // Listing all links can be resource-intensive if not paginated properly.
            // A direct query on the pivot table might be better for a full list.
            // For now, let's return an empty set or a message if no filter is provided for admin general listing.
            // Or, we can list all users who are guardians and their children.
            // This part needs refinement based on desired output for "all links".
            // Let's defer implementing a "list all links" without filter for this initial step.
            return response()->json(['message' => 'Please provide guardian_user_id or student_user_id to list links.'], 400);
        }
    }

    /**
     * Store a new guardian-student link.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guardian_user_id' => 'required|integer|exists:users,id',
            'student_user_id' => 'required|integer|exists:users,id|different:guardian_user_id',
            'relationship_type' => 'nullable|string|max:100',
            'is_primary_contact' => 'nullable|boolean',
            'can_access_records' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $guardian = User::find($request->guardian_user_id);
        $student = User::find($request->student_user_id);

        if (!$guardian || !$guardian->hasRole('parent')) { // Assuming 'parent' is the slug for guardians
            return response()->json(['errors' => ['guardian_user_id' => 'The selected guardian is invalid or not a parent/guardian.']], 422);
        }

        if (!$student || !$student->hasRole('student')) {
            return response()->json(['errors' => ['student_user_id' => 'The selected student is invalid or not a student.']], 422);
        }

        // Check if the link already exists
        if ($guardian->children()->where('student_user_id', $student->id)->exists()) {
            return response()->json(['message' => 'This guardian is already linked to this student.'], 409); // Conflict
        }

        // Prepare pivot data
        $pivotData = [
            'relationship_type' => $request->input('relationship_type'),
            'is_primary_contact' => $request->boolean('is_primary_contact'), // boolean casts null to false
            'can_access_records' => $request->boolean('can_access_records', true), // Default to true if not sent
        ];
        // Filter out null values if you don't want to store them explicitly
        $pivotData = array_filter($pivotData, function($value) { return !is_null($value); });


        $guardian->children()->attach($student->id, $pivotData);

        return response()->json([
            'message' => 'Guardian successfully linked to student.',
            'link_details' => $pivotData // Or return the guardian with updated children list
        ], 201);
    }

    /**
     * Remove a guardian-student link.
     * Identified by guardian_user_id and student_user_id in the request query or body.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guardian_user_id' => 'required|integer|exists:users,id',
            'student_user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $guardian = User::find($request->guardian_user_id);
        $student = User::find($request->student_user_id);

        if (!$guardian || !$student) {
            return response()->json(['message' => 'Guardian or Student not found.'], 404);
        }

        // Detach the student from the guardian's children list
        if ($guardian->children()->detach($student->id)) {
            return response()->json(['message' => 'Guardian-student link removed successfully.'], 200);
        }

        return response()->json(['message' => 'Link not found or could not be removed.'], 404);
    }
}
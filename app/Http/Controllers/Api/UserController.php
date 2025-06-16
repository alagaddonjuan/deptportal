<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role; // For user creation/update
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash; // For creating/updating passwords
use Illuminate\Support\Facades\Auth; // For checking current admin ID in destroy
use Illuminate\Validation\Rule; // For unique email updates

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * (Admin only - protected by 'isAdmin' middleware on the route)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // You can add query parameters for filtering, sorting, and searching later
        // For example: $request->query('sort_by'), $request->query('role_slug')

        $users = User::with(['role', 'profile']) // Eager load role and profile
                     ->latest() // Order by latest created
                     ->paginate(15); // Paginate results, 15 per page

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     * (Admin only - protected by 'isAdmin' middleware on the route)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed', // Expects 'password_confirmation' field
            'role_slug' => 'required|string|exists:roles,slug',
            'status' => 'sometimes|string|in:active,inactive,suspended',
            'email_verified' => 'sometimes|boolean',
            // Optional profile fields (assuming profile data is nested under 'profile' key)
            'profile.first_name' => 'nullable|string|max:255',
            'profile.last_name' => 'nullable|string|max:255',
            'profile.phone_number' => 'nullable|string|max:20',
            // Add other profile validation rules as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        $role = Role::where('slug', $request->role_slug)->firstOrFail();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
            'status' => $request->input('status', 'active'), // Default to 'active' if not provided
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
        ]);

        // Create profile if profile data is provided
        if ($request->has('profile') && is_array($request->profile)) {
            $user->profile()->create($request->profile);
        } else {
            // Optionally, create an empty profile if you want every user to have one
            $user->profile()->create([]);
        }

        return response()->json([
            'message' => 'User created successfully by admin.',
            'user' => $user->load(['role', 'profile']) // Return user with loaded relationships
        ], 201); // HTTP 201 Created
    }

    /**
     * Display the specified resource.
     * (Admin only - protected by 'isAdmin' middleware on the route)
     *
     * @param  \App\Models\User  $user  (Route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        return response()->json($user->load(['role', 'profile']));
    }

    /**
     * Update the specified resource in storage.
     * (Admin only - protected by 'isAdmin' middleware on the route)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user  (Route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id), // Ignore current user's email
            ],
            'password' => 'nullable|string|min:8|confirmed', // Password is optional
            'role_slug' => 'sometimes|required|string|exists:roles,slug',
            'status' => 'sometimes|required|string|in:active,inactive,suspended',
            'email_verified' => 'sometimes|boolean',
            // Optional profile fields for update
            'profile.first_name' => 'nullable|string|max:255',
            'profile.last_name' => 'nullable|string|max:255',
            'profile.phone_number' => 'nullable|string|max:20',
            // Add other profile validation rules as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update user fields
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->filled('password')) { // Only update password if provided
            $user->password = Hash::make($request->password);
        }
        if ($request->has('role_slug')) {
            $role = Role::where('slug', $request->role_slug)->firstOrFail();
            $user->role_id = $role->id;
        }
        if ($request->has('status')) {
            $user->status = $request->status;
        }
        if ($request->has('email_verified')) {
            $user->email_verified_at = $request->boolean('email_verified') ? now() : null;
        }
        
        $user->save(); // Save user changes

        // Update profile if profile data is provided
        if ($request->has('profile') && is_array($request->profile)) {
            // Ensure profile exists, create if not (though it should from 'store')
            $user->profile()->updateOrCreate([], $request->profile);
        }

        return response()->json([
            'message' => 'User updated successfully by admin.',
            'user' => $user->fresh()->load(['role', 'profile']) // Return fresh model with loaded relationships
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * (Admin only - protected by 'isAdmin' middleware on the route)
     *
     * @param  \App\Models\User  $user  (Route model binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        // Basic safeguard: prevent admin from deleting themselves
        if (Auth::id() === $user->id) {
            return response()->json(['message' => 'You cannot delete your own admin account.'], 403); // Forbidden
        }

        // You might want to add more complex checks, e.g., not deleting the only admin user.

        try {
            $user->delete(); // This will also delete related profile if onDelete('cascade') is set
            return response()->json(['message' => 'User deleted successfully by admin.'], 200);
            // Or return response()->json(null, 204); // No Content
        } catch (\Exception $e) {
            // Log the error for debugging
            // \Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting user. The user might be associated with other critical data.'], 500);
        }
    }

    // --- User's Own Profile Management ---

    /**
     * Display the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showProfile(Request $request)
    {
        $user = $request->user(); // Get the currently authenticated user

        // Eager load profile and role for a complete view
        $user->load(['profile', 'role']);

        return response()->json($user);
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user(); // Get the currently authenticated user

        $validator = Validator::make($request->all(), [
            // User model fields
            'name' => 'sometimes|required|string|max:255',
            // Email updates are more complex due to uniqueness and verification,
            // so we'll omit direct email update via this profile endpoint for now.
            // 'email' => ['sometimes','required','string','email','max:255',Rule::unique('users')->ignore($user->id)],

            // Profile model fields (assuming they are sent in the root of the request or nested under 'profile')
            // For simplicity, let's assume profile fields are sent directly, not nested under 'profile' key for this user-facing update.
            // If they are nested, adjust access: e.g., $request->input('profile.first_name')
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date_format:Y-m-d',
            'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:20', // Consider more specific phone validation if needed
            'alternate_phone_number' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:2000', // Max length for bio
            // 'profile_picture_path' // File uploads are handled differently, typically via a separate endpoint.
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update User model fields
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        // Add more updatable User fields here if necessary (e.g., if email updates were simple)
        $user->save();

        // Update Profile model fields
        // Use updateOrCreate to ensure a profile record exists.
        // The first array is for matching conditions (empty means it will always try to match or create based on user_id from relation).
        // The second array contains the values to update or create with.
        $profileData = $request->only([
            'first_name', 'last_name', 'middle_name', 'date_of_birth', 'gender',
            'address_line1', 'address_line2', 'city', 'state', 'country', 'postal_code',
            'phone_number', 'alternate_phone_number', 'bio'
        ]);

        // Filter out null values so they don't overwrite existing data with null if not provided in request
        $profileData = array_filter($profileData, function ($value) {
            return !is_null($value);
        });
        
        if (!empty($profileData)) {
            $user->profile()->updateOrCreate([], $profileData);
        } else if (!$user->profile) {
            // Ensure profile record exists even if no data is passed for update,
            // this maintains consistency as profiles are expected.
            $user->profile()->create([]);
        }


        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh()->load(['role', 'profile']) // Return fresh model with updated data
        ]);
    }

    // --- User's Own Password Management ---

    /**
     * Change the authenticated user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $user = $request->user(); // Get the currently authenticated user

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The :attribute is incorrect.');
                }
            }],
            'new_password' => 'required|string|min:8|confirmed', // 'confirmed' looks for 'new_password_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update the user's password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Optionally, you might want to log out the user from other devices by revoking all tokens
        // $user->tokens()->delete(); // This line is optional

        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function myChildren(Request $request)
{
    $guardian = Auth::user();

    // Ensure the user has the 'parent' or 'guardian' role
    if (!$guardian->hasRole('parent')) { // Assuming 'parent' is your guardian role slug
        return response()->json(['message' => 'This action is for guardians/parents only.'], 403);
    }

    // Fetch children with their profiles and the pivot data from the link
    $children = $guardian->children()
                         ->with(['profile:user_id,first_name,last_name', 'role:id,name,slug']) // Load student's profile and role
                         ->get()
                         ->map(function ($student) {
                             // Make pivot data directly accessible on the student object if desired for frontend ease
                             $student->link_details = [
                                 'relationship_type' => $student->pivot->relationship_type,
                                 'is_primary_contact' => (bool) $student->pivot->is_primary_contact,
                                 'can_access_records' => (bool) $student->pivot->can_access_records,
                             ];
                             // Unset pivot if you don't want it nested under 'pivot' key for the response
                             // unset($student->pivot); 
                             return $student;
                         });

    return response()->json($children);
}

}
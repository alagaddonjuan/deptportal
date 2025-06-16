<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // For login

class AuthController extends Controller
{
   /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' looks for a 'password_confirmation' field
            'role_slug' => 'required|string|exists:roles,slug', // e.g., 'student', 'teacher'. Admin should be created via seeder or by another admin.
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        $role = Role::where('slug', $request->role_slug)->first();

        // Prevent self-registration as admin unless specifically allowed by other logic
        if ($role->slug === 'admin') {
             // You might want to restrict admin registration to specific conditions
             // For now, let's assume general registration is not for admins.
             // This can be handled by frontend not offering 'admin' as a choice,
             // but backend validation is crucial.
            if (!Auth::check() || !Auth::user()->isAdmin()) { // Example: only an existing admin can create another admin
                 // return response()->json(['message' => 'Admin registration not allowed through this endpoint.'], 403);
            }
             // If you decide to allow admin registration here under certain conditions, ensure proper authorization.
             // For a public portal, 'admin' role_slug should typically not be accepted from a public registration form.
        }


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
            'status' => 'active', // Default status
        ]);

        // Optionally, create a profile entry if you have a profiles table
        if ($user && config('features.profiles_enabled', true)) { // Assuming a config or simple true
            $user->profile()->create([
                // Add default profile fields if necessary, or leave empty
                // e.g., 'first_name' => explode(' ', $user->name)[0] (simple example)
            ]);
        }

        // Optionally, log the user in immediately and return a token
        // $token = $user->createToken('api-token')->plainTextToken;
        // return response()->json([
        //     'message' => 'User registered successfully!',
        //     'user' => $user->load('role', 'profile'),
        //     'token' => $token
        // ], 201);

        return response()->json(['message' => 'User registered successfully. Please login.'], 201);
    }

    /**
     * Log in an existing user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials.'], 401); // Unauthorized
        }

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->status !== 'active') {
            Auth::logout(); // Log out if attempted login with inactive/suspended account
            return response()->json(['message' => 'Your account is not active. Please contact support.'], 403); // Forbidden
        }

        $user->load(['role', 'profile']); // Eager load relationships

        $token = $user->createToken('api-token-' . $user->id)->plainTextToken; // Add user ID to token name for potential specificity

        return response()->json([
            'message' => 'Login successful!',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Log out the current authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete(); // Revoke the token that was used to authenticate the current request

        // To revoke all tokens for the user (e.g., if they want to log out from all devices):
        // $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    } //
}

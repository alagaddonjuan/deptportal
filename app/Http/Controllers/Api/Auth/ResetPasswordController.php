<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password; // Import the Password facade
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset; // Event fired after a successful reset
use Illuminate\Support\Str; // For Str::random() if needed, though broker handles token generation
use App\Models\User; // If you need to interact with the User model directly

class ResetPasswordController extends Controller
{
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed', // Expects 'password_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                // $user->setRememberToken(Str::random(60)); // Optional: reset remember token
                $user->save();

                event(new PasswordReset($user)); // Fire the PasswordReset event

                // Optionally, log the user in immediately after password reset
                // Or, more commonly for APIs, just tell them to log in with their new password.
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        // For an API, we return JSON.
        if ($response == Password::PASSWORD_RESET) {
            return response()->json(['message' => __(trans($response))], 200); // trans() for localization
        }

        // If the reset attempt failed, e.g., invalid token or email.
        return response()->json(['message' => __(trans($response))], 400); // Or 422 if specific validation-like error
    }
}
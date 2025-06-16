<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password; // Import the Password facade
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then send the message back
        // to the user. Behind the scenes, this method will typically store a token
        // in the `password_reset_tokens` table and send a Mailable.
        $status = Password::sendResetLink($request->only('email'));

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 200);
        }

        // If the broker was unable to send the link,
        // this will occur if it couldn't find a user with the given email address.
        // However, our 'exists:users,email' validation should prevent this specific case.
        // Other reasons could be throttling if too many requests are made.
        return response()->json(['message' => __($status)], 400); // Or a more specific error code/message
    }
}
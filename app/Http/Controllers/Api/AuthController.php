<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Step 1: Standard Login & OTP Generation
     * POST /api/login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Security check: Verify user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The credentials provided are incorrect.',
            ], 401);
        }

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // Store hashed OTP in the two_factor_secret column
        // We set confirmed_at to null until the OTP is verified
        $user->update([
            'two_factor_secret' => Hash::make($otp),
            'two_factor_confirmed_at' => null,
        ]);

        /*
         * MOCK SENDING LOGIC
         * In production, use Mail::to($user)->send(...) or an SMS service.
         */
        Log::info("TuPay OTP for {$user->email}: {$otp}");

        return response()->json([
            'message' => 'A verification code has been sent to your registered contact info.',
            'email' => $user->email,
            'requires_2fa' => true,
        ]);
    }

    /**
     * Step 2: Verify TOTP and Issue Token
     * POST /api/2fa/verify.
     */
    public function verify2FA(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->two_factor_secret) {
            return response()->json(['message' => 'No active session found.'], 404);
        }

        // Check if the provided OTP matches the hashed secret
        if (!Hash::check($request->code, $user->two_factor_secret)) {
            return response()->json(['message' => 'The verification code is invalid or has expired.'], 422);
        }

        // Clear the secret to prevent reuse (One-time use logic)
        $user->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => now(),
        ]);

        // Create the Sanctum access token
        $token = $user->createToken('tupay_access_token')->plainTextToken;

        return response()->json([
            'message' => 'Authentication successful.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number,
            ],
        ]);
    }

    /**
     * Logout
     * POST /api/logout.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No active session found.'], 401);
        }

        // Delete only the token used for this specific request
        $user->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.',
        ]);
    }
}

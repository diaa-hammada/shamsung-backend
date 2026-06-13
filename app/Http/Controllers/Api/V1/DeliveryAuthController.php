<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Otp\SendOtpRequest;
use App\Http\Requests\Api\V1\Otp\VerifyOtpRequest;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryAuthController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    // STEP 1
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $sent = $this->otpService->sendOtp($request->validated('phone'));

        if (!$sent) {
            return response()->json([
                'message' => 'Too many attempts. Please wait before requesting a new OTP.',
            ], 429);
        }

        return response()->json([
            'message' => 'OTP sent successfully',
        ], 200);
    }

    // STEP 2
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->verifyOtp(
            $request->validated('phone'),
            $request->validated('code'),
            'delivery'
        );

        if ($result === 'otp_invalid_or_expired') {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        if ($result === 'user_not_found') {
            return response()->json(['message' => 'This phone number is not registered.'], 404);
        }

        if ($result === 'account_deactivated') {
            return response()->json(['message' => 'Your account has been deactivated.'], 403);
        }

        return response()->json([
            'message' => 'Delivery worker logged in successfully',
            'data'    => [
                'delivery_worker' => $result['user']->load('shop:id,name'),
                'token'           => $result['token'],
            ],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function profile(Request $request): JsonResponse
    {
        $worker = $request->user()->load('shop:id,name,address');

        return response()->json([
            'message' => 'Profile retrieved successfully',
            'data'    => $worker,
        ], 200);
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => ['required', 'string', 'max:255']]);
        $request->user()->update(['fcm_token' => $request->input('fcm_token')]);

        return response()->json(['message' => 'FCM token updated successfully'], 200);
    }
}

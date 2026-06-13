<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Otp\SendOtpRequest;
use App\Http\Requests\Api\V1\Otp\VerifyOtpRequest;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use App\Models\Customer;
use App\Models\Otp;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;

class CustomerAuthController extends Controller
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
        $phone = $request->validated('phone');
        $code  = $request->validated('code');

        $result = $this->otpService->verifyOtp($phone, $code, 'customer');

        if ($result === 'otp_invalid_or_expired') {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        // Phone verified but no account yet → OtpService already set phone_verified=true
        if ($result === 'user_not_found') {
            return response()->json([
                'message' => 'Phone verified. Please complete your registration.',
                'data'    => [
                    'is_new_user' => true,
                    'phone'       => $phone,
                ],
            ], 200);
        }

        return response()->json([
            'message' => 'Login successful',
            'data'    => [
                'customer' => $result['user'],
                'token'    => $result['token'],
            ],
        ], 200);
    }

    // STEP 3 — only when is_new_user = true
    public function register(RegisterCustomerRequest $request): JsonResponse
    {
        $verified = Otp::where('phone', $request->validated('phone'))
            ->where('phone_verified', true)
            ->where('is_used', true)
            ->where('expires_at', '>', now()->subMinutes(10))
            ->latest()
            ->first();

        if (!$verified) {
            return response()->json([
                'message' => 'يجب التحقق من رقم الهاتف أولاً',
            ], 403);
        }

        $customer = Customer::create($request->validated());
        $token    = $customer->createToken('customer_token')->plainTextToken;

        $verified->delete();

        return response()->json([
            'message' => 'Customer registered successfully',
            'data'    => [
                'customer' => $customer,
                'token'    => $token,
            ],
        ], 201);
    }

    public function logout(): JsonResponse
    {
        /** @var Customer $customer */
        $customer = auth()->user();
        $customer->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function profile(): JsonResponse
    {
        return response()->json([
            'data' => auth()->user(),
        ], 200);
    }

    public function deleteAccount(): JsonResponse
    {
        /** @var Customer $customer */
        $customer = auth()->user();
        $customer->tokens()->delete();
        $customer->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ], 200);
    }

    public function updateFcmToken(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => ['required', 'string', 'max:255']]);
        $request->user()->update(['fcm_token' => $request->input('fcm_token')]);

        return response()->json(['message' => 'FCM token updated successfully'], 200);
    }
}

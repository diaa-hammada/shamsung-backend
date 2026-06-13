<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\AdminLoginRequest;
use App\Http\Requests\Api\V1\Admin\ChangeAdminPasswordRequest;
use App\Http\Requests\Api\V1\Admin\UpdateAdminProfileRequest;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $admin = Admin::where('email', $request->validated('email'))->first();

        if (! $admin || ! Hash::check($request->validated('password'), $admin->password)) {
            return response()->json([
                'message' => 'Invalid admin credentials',
            ], 401);
        }

        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'message' => 'Admin logged in successfully',
            'data'    => [
                'admin' => $admin,
                'token' => $token,
            ],
        ], 200);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Profile retrieved successfully',
            'data'    => $request->user(),
        ], 200);
    }

    public function updateProfile(UpdateAdminProfileRequest $request): JsonResponse
    {
        $admin = $request->user();
        $admin->update($request->validated());

        return response()->json([
            'message' => 'Profile updated successfully',
            'data'    => $admin->fresh(),
        ], 200);
    }

    public function changePassword(ChangeAdminPasswordRequest $request): JsonResponse
    {
        $admin = $request->user();

        if (! Hash::check($request->validated('current_password'), $admin->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors'  => ['current_password' => ['The current password is incorrect.']],
            ], 422);
        }

        $admin->update(['password' => $request->validated('new_password')]);

        return response()->json([
            'message' => 'Password changed successfully',
        ], 200);
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => 'required|string']);
        $request->user()->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'FCM token updated successfully']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}

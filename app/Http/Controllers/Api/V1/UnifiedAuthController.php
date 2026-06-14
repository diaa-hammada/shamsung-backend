<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Technician;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UnifiedAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email    = $request->input('email');
        $password = $request->input('password');

        // Check Admin first
        $admin = Admin::where('email', $email)->first();
        if ($admin && Hash::check($password, $admin->password)) {
            $token = $admin->createToken('admin_token')->plainTextToken;

            return response()->json([
                'message' => 'Logged in successfully',
                'data'    => [
                    'role'  => 'admin',
                    'user'  => $admin,
                    'token' => $token,
                ],
            ], 200);
        }

        // Check Technician
        $technician = Technician::where('email', $email)->first();
        if ($technician && $technician->password && Hash::check($password, $technician->password)) {
            if (! $technician->is_active) {
                return response()->json([
                    'message' => 'Your account has been deactivated.',
                ], 403);
            }

            $token = $technician->createToken('technician_token')->plainTextToken;

            return response()->json([
                'message' => 'Logged in successfully',
                'data'    => [
                    'role'  => 'technician',
                    'user'  => $technician->load('shop:id,name'),
                    'token' => $token,
                ],
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }
}

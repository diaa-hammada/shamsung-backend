<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use App\Http\Requests\Auth\LoginCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function register(RegisterCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        $token = $customer->createToken('customer_auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Customer registered successfully',
            'data' => [
                'customer' => $customer,
                'token' => $token,
            ]
        ], 201);
    }

    public function login(LoginCustomerRequest $request): JsonResponse
    {
        $loginField = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $customer = Customer::where($loginField, $request->input('login'))->first();

        if (!$customer || !Hash::check($request->input('password'), $customer->password)) {
            throw ValidationException::withMessages([
                'login' => ['Invalid credentials provided.'],
            ]);
        }

        $token = $customer->createToken('customer_auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'customer' => $customer,
                'token' => $token,
            ]
        ], 200);
    }
    
    public function profile(): JsonResponse
{
    return response()->json([
        'data' => auth()->user()
    ], 200);
}
    
    public function logout(): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth()->user();
        
        $customer->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function deleteAccount(): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth()->user();
        
        $customer->tokens()->delete();
        $customer->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ], 200);
    }
}
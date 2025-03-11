<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    // Register User
    public function register(Request $request)
    {
        try {
            // Validate Input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            // Create User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Generate Token (Valid for 5 Days)
            // $token = $user->createToken('auth_token', ['*'], Carbon::now()->addDays(5))->plainTextToken;

            // Success Response
            return response()->json([
                'data' => ['user' => $user],
                'status' => 'success',
                'message' => 'User registered successfully',
                'errors' => null
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Extract First Validation Error Message
            $errors = $e->errors();
            $firstErrorMessage = collect($errors)->first()[0]; // Get first error message

            // Return Validation Errors with First Error in Message
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => $firstErrorMessage, // Show first error message in "message"
                'errors' => $errors // Full error details
            ], 422);
        } catch (\Exception $e) {
            // Handle Other Errors
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Something went wrong',
                'errors' => $e->getMessage()
            ], 500);
        }
    }



    // Login User
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Invalid credentials',
                'errors' => ['email' => 'Invalid email or password']
            ], 401);
        }

        // Fetching user manually after authentication
        $user = User::where('email', $request->email)->firstOrFail();

        // Generating a token valid for 5 days
        $token = $user->createToken('auth_token', ['*'], Carbon::now()->addDays(5))->plainTextToken;

        return response()->json([
            'data' => ['user' => $user, 'token' => $token],
            'status' => 'success',
            'message' => 'Login successful',
            'errors' => null
        ], 200);
    }


    // Logout User
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'data' => null,
            'status' => 'success',
            'message' => 'Logged out successfully',
            'errors' => null
        ]);
    }

    // Get Authenticated User
    public function user(Request $request)
    {
        return response()->json([
            'data' => $request->user(),
            'status' => 'success',
            'message' => 'User data retrieved',
            'errors' => null
        ]);
    }
}

<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class AuthService
{
    /**
     * Registering a new user.
     */
    public function registerUser(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        // Creating user
        // Hashing the password before storing it
        // in the database
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return $user;
    }

    /**
     * Authenticating user and generating sanctum token.
     */
    public function loginUser(Request $request)
    {
        // Validate incoming request
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Attempting to authenticate user
        if (!Auth::attempt($credentials)) {
            return null;
        }


        $user = User::where('email', $request->email)->firstOrFail();
        // Generating sanctum token
        $token = $user->createToken('auth_token', ['*'], Carbon::now()->addDays(5))->plainTextToken;

        // Returning user and token
        return ['user' => $user, 'token' => $token];
    }
}

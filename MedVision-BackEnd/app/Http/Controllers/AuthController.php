<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:doctor,patient',  // Validate the role input
        ]);
    
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],  // Save the role in the user model
        ]);
    
        $token = JWTAuth::fromUser($user);
    
        return response()->json([
            'message' => 'Successfully registered',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // Login a user and return the token
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'message' => 'Successfully logged in',
            'token' => $token,
            'user' => Auth::user()
        ]);
    }

    // Logout a user
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    // Get the authenticated user
    public function me()
    {
        return response()->json([
            'message' => 'Authenticated user data retrieved successfully',
            'user' => Auth::user()
        ]);
    }
}

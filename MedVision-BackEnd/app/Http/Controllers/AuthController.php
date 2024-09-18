<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function registerDoctor(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'specialization' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate the profile picture
        ]);
    
        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => 'doctor',
            ]);
    
            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('public/profile_pictures');
                $user->profile_picture = $path;
                $user->save(); 
            }
    
            $doctor = Doctor::create([
                'user_id' => $user->id,
                'specialization' => $validatedData['specialization'],
            ]);
    
            $token = JWTAuth::fromUser($user);
    
            return response()->json([
                'message' => 'Doctor registered successfully',
                'token' => $token,
                'user' => $user,
                'doctor' => $doctor,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed, please try again.'], 500);
        }
    }
    public function registerPatient(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'required|string|in:male,female',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', 
        ]);
    
        try {
            $profilePicturePath = null;
            if ($request->hasFile('profile_picture')) {
                \Log::info('Patient profile picture upload: ' . $request->file('profile_picture')->getClientOriginalName());
    
                $profilePicturePath = $request->file('profile_picture')->store('public/profile_pictures');
                \Log::info('Stored patient profile picture at: ' . $profilePicturePath);
            }
    
         
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => 'patient',  
                'gender' => $validatedData['gender'], 
                'profile_picture' => $profilePicturePath,
            ]);
    
            $token = JWTAuth::fromUser($user);
    
            return response()->json([
                'message' => 'Successfully registered as a patient',
                'token' => $token,
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Patient registration error: ' . $e->getMessage());
            return response()->json(['error' => 'Registration failed, please try again.'], 500);
        }
    }
    
    public function registerAdmin(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', 
        ]);
    
        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => 'admin', 
            ]);
            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('public/profile_pictures');
                $user->profile_picture = $path;
                $user->save(); 
            }
    
            $token = JWTAuth::fromUser($user);
    
            return response()->json([
                'message' => 'Admin registered successfully',
                'token' => $token,
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed, please try again.'], 500);
        }
    }
    

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);
    
        if (!$token = JWTAuth::attempt($credentials = $request->only('email', 'password'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json([
            'message' => 'Successfully logged in',
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 120, 
            'user' => Auth::user(),
        ]);
    }
    // Logout a user
    public function logout(Request $request)
{
    try {
        // Extract the token from the Authorization header
        $fullToken = $request->header('Authorization');

        // Log the full token for debugging purposes
        \Log::info('Token: ' . $fullToken);

        // Check if Authorization header exists and contains the Bearer token
        if ($fullToken && preg_match('/Bearer\s(\S+)/', $fullToken, $matches)) {
            $token = $matches[1]; // Extract the actual token part (without 'Bearer')

            // Invalidate the token
            JWTAuth::setToken($token)->invalidate();

            return response()->json(['message' => 'Successfully logged out']);
        }

        return response()->json(['error' => 'Failed to logout, no token found.'], 400);
    } catch (\Exception $e) {
        \Log::error('Logout failed: ' . $e->getMessage());  // Log the error for further debugging
        return response()->json(['error' => 'Failed to logout, please try again.'], 500);
    }
}




    // Get the authenticated user
    public function me()
    {
        return response()->json([
            'message' => 'Authenticated user data retrieved successfully',
            'user' => Auth::user(),
        ]);
    }
}

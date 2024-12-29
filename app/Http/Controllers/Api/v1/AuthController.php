<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request) 
    {
        try {
            // Data validation
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8'
            ]);

            // Save user
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']) // Encrypt password
            ]);

            // Create empty cart for new user
            $user->cart()->create([
                'status' => 'pending' // Provide a default value for the 'status' column
            ]);

            return response()->json([
                'user' => $user,
                'message' => 'User Registered Successfully'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 422);
        } catch (\Throwable $th) {
            \Log::error('Error registering user: ' . $th->getMessage());
            return response()->json(['message' => 'Error registering user', 'error' => $th->getMessage()], 500);
        }        
    }

    public function login(Request $request) 
    {
        try {
            // Data validation
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Get user by email
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'The provided credentials are incorrect'], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error logging in', 'error' => $th->getMessage()], 500);
        }
    }

    public function logout(Request $request) 
    {
        try {
            $request->user()->tokens()->delete();
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error logging out', 'error' => $th->getMessage()], 500);
        }
    }
}
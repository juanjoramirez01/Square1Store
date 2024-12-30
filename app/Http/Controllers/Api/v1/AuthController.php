<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request) 
    {
        Log::info('Register request', $request->all());
        
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string'
            ]);

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'email_verified_at' => now()
            ]);

            $user->cart()->create([
                'status' => 'pending'
            ]);

            Log::info('User registered successfully', ['user_id' => $user->id]);

            return response()->json([
                'user' => $user,
                'message' => 'User Registered Successfully'
            ], 201);
        } catch (ValidationException $e) {
            Log::warning('Validation error during registration', ['errors' => $e->errors()]);
            return response()->json(['message' => $e->errors()], 422);
        } catch (\Throwable $th) {
            Log::error('Error registering user', ['error' => $th->getMessage()]);
            return response()->json(['message' => 'Error registering user', 'error' => $th->getMessage()], 500);
        }        
    }

    public function login(Request $request) 
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $validatedData['email'])->first();

            if (!$user || !Hash::check($validatedData['password'], $user->password)) {
                Log::warning('Invalid login attempt', ['email' => $validatedData['email']]);
                return response()->json(['message' => 'The provided credentials are incorrect'], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User logged in successfully', ['user_id' => $user->id]);

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'message' => 'Login successful',
                'token' => $token
            ]);
        } catch (ValidationException $e) {
            Log::warning('Validation error during login', ['errors' => $e->errors()]);
            return response()->json(['message' => $e->errors()], 422);
        } catch (\Throwable $th) {
            Log::error('Error logging in', ['error' => $th->getMessage()]);
            return response()->json(['message' => 'Error logging in', 'error' => $th->getMessage()], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            Log::info('Fetching user profile');
            $user = auth()->user();
            if (!$user) {
                Log::warning('User not found');
                throw new ModelNotFoundException('User not found');
            }
            Log::info('User found', ['user' => $user]);
            return response()->json([
                'user'=>[
                "id"=>$user->id,
                "name"=>$user->name,
                "email"=>$user->email],
            ], 200);
        } catch (ModelNotFoundException $e) {
            Log::error('ModelNotFoundException', ['message' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Throwable $th) {
            Log::error('Error fetching user profile', ['error' => $th->getMessage()]);
            return response()->json(['message' => 'Error fetching user profile', 'error' => $th->getMessage()], 500);
        }
    }

    public function logout(Request $request) 
    {
        try {
            $user = auth()->user();
            $user->tokens()->delete();
            $user->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Throwable $th) {
            Log::error('Error logging out', ['error' => $th->getMessage()]);
            return response()->json(['message' => 'Error logging out', 'error' => $th->getMessage()], 500);
        }
    }
}
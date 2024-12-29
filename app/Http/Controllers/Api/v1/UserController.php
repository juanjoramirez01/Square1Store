<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        try {
            Log::info('Fetching user profile');
            $user = $request->user();
            if (!$user) {
                Log::warning('User not found');
                throw new ModelNotFoundException('User not found');
            }
            Log::info('User found', ['user' => $user]);
            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            Log::error('ModelNotFoundException', ['message' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Throwable $th) {
            Log::error('Throwable', ['message' => $th->getMessage()]);
            return response()->json(['message' => 'Error fetching user profile', 'error' => $th->getMessage()], 500);
        }
    }
}
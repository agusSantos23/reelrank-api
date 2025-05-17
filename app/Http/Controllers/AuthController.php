<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'sometimes',
                'string',
                'min:8',
                'max:64',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->symbols(),
            ],
            'avatarId' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [];

        foreach ($request->all() as $key => $value) {
            if ($key !== 'avatarId' && is_string($value)) {
                $data[$key] = trim($value);
            } else {
                $data[$key] = $value;
            }
        }

        if (isset($data['name'])) $data['name'] = ucwords($data['name']);


        if (isset($data['lastname'])) $data['lastname'] = ucwords($data['lastname']);


        $user = User::create([
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'avatar_id' => $data['avatarId'],
        ]);


        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registered user.Please check your email.',
            'token' => $token,
        ], 201);
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).+$/',
            ],
        ]);

        if ($validator->fails()) return response()->json(['errors' => 'Invalid send data'], 422);


        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(['token' => $token]);
    }

    public function verifyToken(Request $request)
    {
        $token = $request->input('token');

        if (!$token) return response()->json(['message' => 'Token not proportionate'], 400);


        try {
            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) return response()->json(['message' => 'Invalid token'], 401);


            return response()->json(['message' => 'Valid token'], 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Expired token'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        } catch (\Exception $e) {

            Log::error("token: " . $e->getMessage());
            return response()->json(['message' => 'Error when verifying the token '], 500);
        }
    }
}

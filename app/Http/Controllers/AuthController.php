<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
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

        /*
        $verificationCode = random_int(100000, 999999);

        VerificationCode::create([
            'user_id' => $user->id,
            'code' => $verificationCode,
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($user, $verificationCode));
        */

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
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        try {
            $request->validate([
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'email|required',
                'password' => 'required'
            ]);
            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password)
            ]);

            $token = $this->authenticate($user);

            return response()->json([
                'data' => [
                    'token' => $token, 
                    'user' => $user
                ],
                'status' => 'success',
                'message' => 'user registered successfully'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);
            $credentials = request(['email', 'password']);
            $loginUser = Auth::attempt($credentials);

            if ($loginUser == 1) {
                $token = $this->authenticate($request->user());

                return response()->json([
                    'data' => [
                        'token' => $token, 'user' => $request->user()
                    ],
                    'status' => 'success',
                    'message' => 'user logged in successfully'
                ], 200);
            }

            return response()->json([
                'status' => 'failed',
                'message' => 'login was not successful'
            ], 401);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'failed',
                'message' => 'login was not successful'
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'log out successful'
        ]);
    }

    public function authenticate($user)
    {
        $token = $user->createToken($user->email);

        return $token->plainTextToken;
    }
}

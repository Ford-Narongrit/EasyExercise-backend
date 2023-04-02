<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = JWTAuth::attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|between:2,100',
            'password' => 'required|string|confirmed|min:8',
            'weight' => 'required|numeric',
            'height' => 'required|numeric',
            'year' => 'required|numeric',
            'month' => 'required|numeric',
            'day' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //create user
        $birthday = Carbon::create($request->year, $request->month,$request->day);
        $user = User::create([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'birthday'=> $birthday,
            'weight' => $request->weight,
            'height' => $request->height,
        ]);

        return response()->json(['message' => 'User successfully registered.', 'user' => $user], 201);
    }

    public function me()
    {
        $token = request()->bearerToken();
        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'username' => auth()->user()->username,
            'weight' => auth()->user()->weight,
            'height' => auth()->user()->height,
            'age' => auth()->user()->age,
        ]);
    }
}

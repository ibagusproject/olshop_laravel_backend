<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        // validasi input dari user
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'email', 'unique:users'],
            'phone' => ['nullable', 'string'],
            'roles' => ['nullable', 'string'],
            'password' => ['required', new Password],
        ]);

        // jika validasi gagal
        if ($validator->fails()) {
            return ApiResponse::error($validator->getMessageBag(), 'Authenticated Failed', 500);
        }

        // jika validasi berhasil
        // simpan data user baru
        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'roles' => $request->roles,
            'password' => Hash::make($request->password),
        ]);

        // ambil data user yang baru dibuat
        $user =  User::where('email', $request->email)->first();

        // generate token
        $tokenResult = $user->createToken('authToken')->plainTextToken;

        return ApiResponse::success([
            'access_token' => $tokenResult,
            'token_type' => 'Bearer',
            'user' => $user
        ], 'User Registered');
    }

    public function login(Request $request)
    {
        // validasi input dari user
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['nullable', 'boolean'],
        ]);

        // jika validasi gagal
        if ($validator->fails()) {
            return ApiResponse::error($validator->getMessageBag(), 'Authenticated Failed', 500);
        }

        // cek login
        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return ApiResponse::error([], 'Authenticated Failed', 500);
        }

        // jika login berhasil, ambil data user
        $user = User::where('email', $request->email)->first();

        // cek hash password
        if (!Hash::check($request->password, $user->password)) {
            throw new Exception('Invalid Credentials');
        }

        // generate token
        $tokenResult = $user->createToken('authToken')->plainTextToken;

        if ($request->remember) {
            $user->remember_token = $tokenResult;
            $user->save();
        } else {
            $user->remember_token = null;
            $user->save();
        }

        return ApiResponse::success([
            'access_token' => $tokenResult,
            'token_type' => 'Bearer',
            'user' => $user
        ], 'Authenticated');
    }

    public function fetch(Request $request)
    {
        return ApiResponse::success($request->user(), 'Data Profile User Berhasil Diambil');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ApiResponse::success($user, 'Data Berhasil Diupdate');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->remember_token = null;
        $user->save();
        $token = $user->currentAccessToken()->delete();
        return ApiResponse::success($token, 'Berhasil Logout');
    }
}

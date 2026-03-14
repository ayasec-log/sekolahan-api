<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private function success($data, $statusCode, $message = 'success')
    {
        return response()->json([
            'status'      => true,
            'message'     => $message,
            'data'        => $data,
            'status_code' => $statusCode,
        ], $statusCode);
    }

    private function failedResponse($message, $statusCode)
    {
        return response()->json([
            'status'      => false,
            'message'     => $message,
            'data'        => null,
            'status_code' => $statusCode,
        ], $statusCode);
    }

    // POST /api/login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->failedResponse('Username atau password salah!', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user'  => $user,
            'token' => $token,
        ], 200, 'Login berhasil!');
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 200, 'Logout berhasil!');
    }
}

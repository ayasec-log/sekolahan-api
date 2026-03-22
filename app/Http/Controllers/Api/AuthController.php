<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

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

        $credentials = $request->only('username', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->failedResponse('Username atau password salah!', 401);
            }
        } catch (JWTException $e) {
            return $this->failedResponse('Gagal membuat token!', 500);
        }

        return $this->success(['token' => $token], 200, 'Login berhasil!');
    }

    // POST /api/logout
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return $this->success(null, 200, 'Logout berhasil!');
        } catch (JWTException $e) {
            return $this->failedResponse('Gagal logout!', 500);
        }
    }

    // GET /api/me
    public function me()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->success($user, 200);
    }
}
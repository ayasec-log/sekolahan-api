<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'status'      => false,
                    'message'     => 'User tidak ditemukan!',
                    'data'        => null,
                    'status_code' => 401,
                ], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status'      => false,
                'message'     => 'Token sudah expired!',
                'data'        => null,
                'status_code' => 401,
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status'      => false,
                'message'     => 'Token tidak valid!',
                'data'        => null,
                'status_code' => 401,
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status'      => false,
                'message'     => 'Token tidak ada!',
                'data'        => null,
                'status_code' => 401,
            ], 401);
        }

        return $next($request);
    }
}
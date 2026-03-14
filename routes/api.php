<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route unauthenticated
Route::get('/unauthenticated', function() {
    return response()->json([
        'status'      => false,
        'message'     => 'Unauthenticated. Token tidak valid atau tidak ada!',
        'data'        => null,
        'status_code' => 401,
    ], 401);
});

// Route publik (tanpa token)
Route::post('/login', 'App\Http\Controllers\Api\AuthController@login');

// Route yang butuh token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', 'App\Http\Controllers\Api\AuthController@logout');
    Route::apiResource('/users',  'App\Http\Controllers\Api\UserController');
    Route::apiResource('/guru',   'App\Http\Controllers\Api\GuruController');
    Route::apiResource('/mapel',  'App\Http\Controllers\Api\MapelController');
    Route::apiResource('/kelas',  'App\Http\Controllers\Api\KelasController');
    Route::apiResource('/siswa',  'App\Http\Controllers\Api\SiswaController');
    Route::apiResource('/jadwal', 'App\Http\Controllers\Api\JadwalController');
});
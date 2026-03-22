<?php

use Illuminate\Support\Facades\Route;

// Route publik
Route::post('/login', 'App\Http\Controllers\Api\AuthController@login');

// Route yang butuh JWT
Route::middleware('jwt.verify')->group(function () {
    Route::post('/logout', 'App\Http\Controllers\Api\AuthController@logout');
    Route::get('/me',      'App\Http\Controllers\Api\AuthController@me');

    Route::apiResource('/users',  'App\Http\Controllers\Api\UserController');
    Route::apiResource('/guru',   'App\Http\Controllers\Api\GuruController');
    Route::apiResource('/mapel',  'App\Http\Controllers\Api\MapelController');
    Route::apiResource('/kelas',  'App\Http\Controllers\Api\KelasController');
    Route::apiResource('/siswa',  'App\Http\Controllers\Api\SiswaController');
    Route::apiResource('/jadwal', 'App\Http\Controllers\Api\JadwalController');
});
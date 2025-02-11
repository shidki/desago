<?php

use App\Http\Controllers\authController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');


Route::post('/login',[authController::class,'login'])->name('login');
Route::post('/register',[authController::class,'register'])->name('register');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

Route::post('/loginGoole',[authController::class,'googleLogin']);
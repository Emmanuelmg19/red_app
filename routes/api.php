<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::apiResource('users', UserController::class);

Route::post('users/{user}/images', [UserController::class, 'addImages']);
Route::delete('images/{image}', [UserController::class, 'deleteImage']);
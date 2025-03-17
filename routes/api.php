<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/users', [UserController::class, 'index']);
Route::post('/users/register', [UserController::class, 'register'] );
Route::patch('/users/{id}', [UserController::class, 'update'] );
Route::delete('/users/{id}', [UserController::class, 'destroy'] );

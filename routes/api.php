<?php

use App\Http\Controllers\ChannelController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/channels', [ChannelController::class, 'index']);
    Route::post('/channels', [ChannelController::class, 'store']);
    Route::get('/channels/{channel}', [ChannelController::class, 'show']);
    Route::post('/channels/{channel}/users', [ChannelController::class, 'addUser']);
    Route::post('/channels/private', [ChannelController::class, 'createPrivateChannel']);

    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/channels/{channel}/messages', [MessageController::class, 'index']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();
    $user->api_token = $user->createToken('auth-token')->plainTextToken;
    return $user;
});

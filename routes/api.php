<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/events', [EventController::class, 'index']);
Route::middleware('auth:sanctum')->post('/events', [EventController::class, 'store']);
Route::middleware('auth:sanctum')->put('/events/{event}', [EventController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/events/{event}', [EventController::class, 'destroy']);
Route::post('/login', [UserController::class, 'login']);
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelUploadController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AuthCheckController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/oauth/token', [
    'uses' => '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken',
    'middleware' => 'throttle',
]);

Route::post('/login', [LoginController::class, 'login']);

Route::middleware(['auth:api'])->post('/logout', [LoginController::class, 'logout']);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:api', 'role:admin'])->post('/upload-excel', [ExcelUploadController::class, 'upload_excel']);

Route::middleware(['auth:api'])->get('/personas', [PersonaController::class, 'index']);
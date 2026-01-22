<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout']);
Route::post('/verify-otp',[AuthController::class,'verifyOtp']);
Route::group(['middleware' => ['auth.api','isUserLogged', 'setLang']], function () {
    
});
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

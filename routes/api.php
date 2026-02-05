<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\UserController;
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
Route::get('/share/quiz-result/{token}',[QuizController::class,'sharedResult']);

Route::middleware(['auth:api','setLang'])->group(function () {
    Route::patch('/user/select-mode', [UserController::class,'selectMode']);
    Route::patch('/user/change-mode', [UserController::class,'changeMode']);
    Route::get('user/main-data',[UserController::class,'userMainData']);
    Route::get('user/all-data',[UserController::class,'userAllData']);
    Route::post('user/update-data',[UserController::class,'editUser']);
    Route::patch('/user/change-lang', [UserController::class,'changeLang']);
    Route::delete('/user/delete-account',[UserController::class,'deleteUser']);
    Route::get('user/get-name',[UserController::class,'getUserName']);

});
Route::group(['middleware' => ['auth:api','isUserLogged', 'setLang','userMode:l']], function () {
    Route::get('/categories',[CategoryController::class,'showCategories']);
    // Route::get('/category-progress',[CategoryController::class,'progressPerCategory']);
    Route::get('/categories/{id}/lesson',[CategoryController::class,'lessonsByCategory']);
    Route::get('/lessons-progress',[CategoryController::class,'lessonsProgressPerCategory']);
    Route::get('/categories/{id}/viewed-lessons',[CategoryController::class,'viewedLessons']);
    Route::get('/lessons/{id}/toggle',[ProgressController::class,'toggleLessonCompletion']);
    Route::get('/quizes',[QuizController::class,'showQuizes']);
    Route::get('quizes/{id}',[QuizController::class,'quizQuestions']);
    Route::post('/quizes/{quiz}/start', [QuizController::class, 'startQuiz']);
    Route::post('/quizes/{quiz}/submit', [QuizController::class, 'submitAnswer']);
    Route::get('/quizes/{quiz}/review/{userQuizId}', [QuizController::class, 'ReviewAnswers']);
    Route::get('/share/generate-link/{userQuizId}',[QuizController::class,'generateShareLink']);
});

Route::group(['middleware' => ['auth:api','isUserLogged', 'setLang','userMode:a']], function () {
    
});
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

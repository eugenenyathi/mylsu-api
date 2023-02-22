<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\RequestsController;
use App\Http\Controllers\GenerateRequests;
use App\Http\Controllers\UtilsController;
use App\Http\Controllers\VEngine;

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

Route::get('/v1/test/{studentID}', [TestController::class, 'index']);

Route::get('/v1/request/status/{studentID}', [RequestsController::class, 'requestStatus']);


Route::prefix('/v1/utils')->controller(UtilsController::class)->group(function () {
    Route::get('/spread', 'studentSpread');
    Route::get('/random/student', 'randomStudent');
    Route::get('/profile/{studentID}', 'studentProfile');
});


//mylsu-auto app
Route::prefix('/v1/auto-app')->controller(GenerateRequests::class)->group(function () {
    Route::get('/generate', 'init');
    Route::get('/stats', 'stats');
    Route::get('/destroy', 'destroyAll');
    Route::get('/clear', 'clearAll');
    Route::get('/revert', 'reverseProcessedRequests');
    Route::get('/clear/rooms', 'clearRooms');
    Route::get('/distribution/{level}', 'countGenderFirstSameLevelStudents');
    Route::get('/count', 'count');
    Route::get('/pull', 'pullStudents');
    Route::get('/res', 'fakeRes');
});

//v-engine
Route::prefix('/v1/v-engine')->controller(VEngine::class)->group(function () {
    Route::get('/run', 'init');
    Route::get('/audit', 'audit');
});


Route::prefix('/v1')->controller(AuthController::class)->group(function () {
    Route::post("/validate", 'validateCredentials');
    Route::post("/register", 'register');
    Route::post("/login", 'login');
    Route::post("/reset", 'reset');
    Route::post("/destroy/{studentID}", 'destroy');
});


//protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::prefix('/v1')->controller(HomeController::class)->group(function () {
        Route::get("/dashboard/1/{studentID}", 'dashboardReminder');
        Route::get("/dashboard/2/{studentID}", 'dashboardAside');
        Route::get("/profile/{studentID}", 'profile');
        Route::get("/residence/{studentID}", 'residence');

        //changing the password
        Route::post("/password/verify", 'verifyCurrentPassword');
        Route::patch("/password/update", 'updatePassword');
    });

    Route::prefix('/v1')->controller(SearchController::class)->group(function () {
        Route::post("/search/{student_id}", 'index')->where('student_id', '^L0\d*');
    });

    Route::prefix('/v1/request')->controller(RequestsController::class)->group(function () {
        // Route::get('/status/{studentID}', 'requestStatus');
        Route::post("/create", 'createRequest');
        Route::patch("/response", 'roommateResponse');

        Route::patch("/response/revert/{studentID}", 'revertResponse');

        Route::get("/destroy/{studentID}", 'destroyRequest');
    });

    // logout
    Route::post("/v1/logout", [AuthController::class, 'logout']);
});

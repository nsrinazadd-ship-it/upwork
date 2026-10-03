<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;


Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);

Route::middleware('throttle:auth')->group(function(){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

});


Route::middleware('auth:sanctum')->group(function () {

    // تقديم عرض جديد أو جلب العروض
    Route::apiResource('proposals', ProposalController::class)->only(['index', 'store']);

    Route::post('/logout', [AuthController::class, 'logout']);

    // روابط الملف الشخصي
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);


    //روابط المشاريع
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    Route::patch('/proposals/{proposal}/accept', [ProposalController::class, 'accept']);
    Route::patch('/proposals/{proposal}/reject', [ProposalController::class, 'reject']);

    // روابط التقييمات والمراجعات
    Route::post('/reviews', [ReviewController::class, 'store']);

});

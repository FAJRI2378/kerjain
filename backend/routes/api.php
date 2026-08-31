<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\JobController as AdminJobController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Freelancer\DashboardController as FreelancerDashboardController;
use App\Http\Controllers\Freelancer\JobController as FreelancerJobController;
use App\Http\Controllers\Freelancer\VerificationController;
use App\Http\Controllers\Hire\ApplicationController;
use App\Http\Controllers\Hire\DashboardController as HireDashboardController;
use App\Http\Controllers\Hire\JobController as HireJobController;
use App\Http\Controllers\Hire\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['application' => 'KERJAIN API', 'version' => '1.0'];
});

// Public
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/categories', [CategoryController::class, 'index']);

// Authenticated
Route::middleware(['auth:sanctum', 'maintenance'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Freelancer
    Route::middleware('role:freelancer')->prefix('freelancer')->group(function () {
        Route::get('/dashboard', [FreelancerDashboardController::class, 'index']);
        Route::post('/verification', [VerificationController::class, 'store']);
    });

    // Hirer
    Route::middleware('role:hirer')->prefix('hire')->group(function () {
        Route::get('/dashboard', [HireDashboardController::class, 'index']);
        Route::get('/jobs', [HireJobController::class, 'myJobs']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/tasks', [AdminJobController::class, 'index']);
        Route::post('/tasks/{task}/approve', [AdminJobController::class, 'approve']);
        Route::post('/tasks/{task}/reject', [AdminJobController::class, 'reject']);
        Route::delete('/tasks/{task}', [AdminJobController::class, 'destroy']);
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users/{user}/verify', [AdminUserController::class, 'verify']);
        Route::post('/users/{user}/suspend', [AdminUserController::class, 'suspend']);
        Route::get('/analytics', [AnalyticsController::class, 'index']);
        Route::get('/settings', [SettingController::class, 'index']);
        Route::put('/settings', [SettingController::class, 'update']);
    });

    // Task operations
    Route::get('/tasks', [FreelancerJobController::class, 'index']);
    Route::get('/tasks/mine', [FreelancerJobController::class, 'mine']);
    Route::get('/tasks/{task}', [FreelancerJobController::class, 'show']);
    Route::post('/tasks', [HireJobController::class, 'store']);
    Route::post('/tasks/{task}/apply', [FreelancerJobController::class, 'apply']);
    Route::post('/tasks/{task}/submit', [FreelancerJobController::class, 'submit']);
    Route::post('/tasks/{task}/complete', [HireJobController::class, 'complete']);
    Route::get('/tasks/{task}/applicants', [ApplicationController::class, 'index']);
    Route::post('/tasks/{task}/applications/{application}/accept', [ApplicationController::class, 'accept']);
    Route::post('/tasks/{task}/applications/{application}/reject', [ApplicationController::class, 'reject']);
});

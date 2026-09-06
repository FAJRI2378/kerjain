<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\JobController as AdminJobController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VerificationController as AdminVerificationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController as AuthProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Freelancer\ApplicationController as FreelancerApplicationController;
use App\Http\Controllers\Freelancer\DashboardController as FreelancerDashboardController;
use App\Http\Controllers\Freelancer\JobController as FreelancerJobController;
use App\Http\Controllers\Freelancer\VerificationController;
use App\Http\Controllers\Freelancer\WalletController as FreelancerWalletController;
use App\Http\Controllers\Hire\ApplicationController;
use App\Http\Controllers\Hire\DashboardController as HireDashboardController;
use App\Http\Controllers\Hire\InvoiceController;
use App\Http\Controllers\Hire\JobController as HireJobController;
use App\Http\Controllers\Hire\ProfileController;
use App\Http\Controllers\Hire\ReviewController;
use App\Http\Controllers\Hire\VerificationController as HireVerificationController;
use App\Http\Controllers\Hire\WalletController as HireWalletController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\InvoiceController as SharedInvoiceController;
use App\Http\Controllers\Public\JobController as PublicJobController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['application' => 'KERJAIN API', 'version' => '1.0'];
});

// Public
Route::get('/status', [StatusController::class, 'index']);
Route::middleware('maintenance')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/jobs', [PublicJobController::class, 'index']);
});

// Auth (login harus tetap tersedia saat maintenance agar admin/N4 bisa masuk)
Route::post('/auth/login', [AuthController::class, 'login']);

// Authenticated
Route::middleware(['auth:sanctum', 'maintenance'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/profile', [AuthProfileController::class, 'update']);
    Route::get('/profile', [AccountController::class, 'profile']);
    Route::get('/verification/status', [AccountController::class, 'verificationStatus']);
    Route::post('/account/password', [AccountController::class, 'changePassword']);

    // Invoice (PDF) — dapat diunduh oleh UMKM pemilik, freelancer yang ditugaskan, atau admin
    Route::get('/invoices/{invoice}/pdf', [SharedInvoiceController::class, 'download']);

    // Freelancer
    Route::middleware('role:freelancer')->prefix('freelancer')->group(function () {
        Route::get('/dashboard', [FreelancerDashboardController::class, 'index']);
        Route::get('/applications', [FreelancerApplicationController::class, 'index']);
        Route::post('/applications/{application}/cancel', [FreelancerApplicationController::class, 'cancel']);
        Route::post('/verification', [VerificationController::class, 'store']);
        Route::get('/wallet', [FreelancerWalletController::class, 'index']);
        Route::post('/wallet/withdraw', [FreelancerWalletController::class, 'withdraw']);
    });

    // Hirer
    Route::middleware('role:hirer')->prefix('hire')->group(function () {
        Route::get('/dashboard', [HireDashboardController::class, 'index']);
        Route::get('/jobs', [HireJobController::class, 'myJobs']);
        Route::get('/jobs/{task}/applicants', [ApplicationController::class, 'index']);
        Route::put('/jobs/{task}', [HireJobController::class, 'update']);
        Route::delete('/jobs/{task}', [HireJobController::class, 'destroy']);
        Route::post('/applicants/{application}/accept', [ApplicationController::class, 'accept']);
        Route::post('/applicants/{application}/reject', [ApplicationController::class, 'reject']);
        Route::post('/verification', [HireVerificationController::class, 'store']);
        Route::get('/tasks/completed', [ReviewController::class, 'completed']);
        Route::post('/tasks/{task}/review', [ReviewController::class, 'review']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::get('/wallet', [HireWalletController::class, 'index']);
        Route::post('/wallet/topup', [HireWalletController::class, 'topup']);
        Route::get('/invoices', [InvoiceController::class, 'index']);
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
        Route::get('/verifications', [AdminVerificationController::class, 'index']);
        Route::get('/verifications/pending', [AdminVerificationController::class, 'pending']);
        Route::post('/verifications/{verification}/approve', [AdminVerificationController::class, 'approve']);
        Route::post('/verifications/{verification}/reject', [AdminVerificationController::class, 'reject']);
        Route::get('/analytics', [AnalyticsController::class, 'index']);
        Route::get('/report', [ReportController::class, 'download']);
        Route::get('/settings', [SettingController::class, 'index']);
        Route::put('/settings', [SettingController::class, 'update']);
    });

    // Task operations
    Route::get('/tasks', [FreelancerJobController::class, 'index']);
    Route::get('/tasks/mine', [FreelancerJobController::class, 'mine']);
    Route::get('/tasks/{task}', [FreelancerJobController::class, 'show']);
    Route::post('/tasks', [HireJobController::class, 'store']);
    Route::post('/tasks/{task}/apply', [FreelancerJobController::class, 'apply']);
    Route::get('/tasks/{task}/proof', [FreelancerJobController::class, 'proofImage']);
    Route::post('/tasks/{task}/submit', [FreelancerJobController::class, 'submit']);
    Route::post('/tasks/{task}/revision', [HireJobController::class, 'revision']);
    Route::post('/tasks/{task}/complete', [HireJobController::class, 'complete']);
});

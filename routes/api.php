<?php

use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\KavlingController as AdminKavlingController;
use App\Http\Controllers\Api\Admin\LeadsController as AdminLeadsController;
use App\Http\Controllers\Api\Admin\MarketingController as AdminMarketingController;
use App\Http\Controllers\Api\Admin\PerumahanController as AdminPerumahanController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Leads\CalendarController as LeadsCalendarController;
use App\Http\Controllers\Api\Leads\MarketingController as LeadsMarketingController;
use App\Http\Controllers\Api\Leads\ProfileController as LeadsProfileController;
use App\Http\Controllers\Api\Marketing\CalendarController as MarketingCalendarController;
use App\Http\Controllers\Api\Marketing\LeadsController as MarketingLeadsController;
use App\Http\Controllers\Api\Marketing\ReportController as MarketingReportController;
use App\Http\Controllers\Api\Public\LeadsController as PublicLeadsController;
use App\Http\Controllers\Api\Public\PerumahanController as PublicPerumahanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Endpoints
|--------------------------------------------------------------------------
*/
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Tamu / Public Endpoints
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    Route::get('/filters', [PublicPerumahanController::class, 'filters']);
    Route::get('/perumahan', [PublicPerumahanController::class, 'index']);
    Route::get('/perumahan/{id}', [PublicPerumahanController::class, 'show']);
    Route::get('/tipe-rumah/{id}', [PublicPerumahanController::class, 'showTipe']);
    Route::post('/leads', [PublicLeadsController::class, 'store'])->middleware('throttle:10,1');
});

/*
|--------------------------------------------------------------------------
| Admin Endpoints
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/perumahan', [AdminPerumahanController::class, 'index']);
    Route::post('/perumahan', [AdminPerumahanController::class, 'store']);
    Route::get('/perumahan/{id}', [AdminPerumahanController::class, 'show']);
    Route::put('/perumahan/{id}', [AdminPerumahanController::class, 'update']);
    Route::delete('/perumahan/{id}', [AdminPerumahanController::class, 'destroy']);
    Route::get('/kavling', [AdminKavlingController::class, 'index']);
    Route::post('/tipe-rumah/{idTipe}/kavling', [AdminKavlingController::class, 'store']);
    Route::put('/kavling/{id}', [AdminKavlingController::class, 'update']);
    Route::get('/leads', [AdminLeadsController::class, 'index']);
    Route::get('/leads/{id}', [AdminLeadsController::class, 'show']);
    Route::post('/leads/{id}/assign', [AdminLeadsController::class, 'assign']);
    Route::post('/leads/{id}/validate', [AdminLeadsController::class, 'validateLead']);
    Route::get('/leads/{id}/ktp', [AdminLeadsController::class, 'downloadKtp']);
    Route::get('/reports', [AdminReportController::class, 'index']);
    Route::get('/marketing', [AdminMarketingController::class, 'index']);
    Route::post('/marketing', [AdminMarketingController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Marketing Endpoints
|--------------------------------------------------------------------------
*/
Route::prefix('marketing')->middleware(['auth:sanctum', 'role:marketing'])->group(function () {
    Route::get('/leads', [MarketingLeadsController::class, 'index']);
    Route::get('/leads/{id}', [MarketingLeadsController::class, 'show']);
    Route::patch('/leads/{id}', [MarketingLeadsController::class, 'update']);
    Route::post('/leads/{id}/ktp', [MarketingLeadsController::class, 'uploadKtp']);
    Route::get('/calendar', [MarketingCalendarController::class, 'index']);
    Route::post('/calendar', [MarketingCalendarController::class, 'store']);
    Route::get('/reports', [MarketingReportController::class, 'index']);
    Route::post('/reports', [MarketingReportController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Leads Endpoints
|--------------------------------------------------------------------------
*/
Route::prefix('leads')->middleware(['auth:sanctum', 'role:leads'])->group(function () {
    Route::get('/profile', [LeadsProfileController::class, 'show']);
    Route::get('/calendar', [LeadsCalendarController::class, 'index']);
    Route::get('/marketing-contact', [LeadsMarketingController::class, 'contact']);
});

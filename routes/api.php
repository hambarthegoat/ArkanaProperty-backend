<?php

use App\Http\Controllers\Api\Admin\KavlingController as AdminKavlingController;
use App\Http\Controllers\Api\Admin\LeadsController as AdminLeadsController;
use App\Http\Controllers\Api\Admin\MarketingController as AdminMarketingController;
use App\Http\Controllers\Api\Admin\PerumahanController as AdminPerumahanController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Leads\CalendarController as LeadsCalendarController;
use App\Http\Controllers\Api\Leads\MarketingController as LeadsMarketingController;
use App\Http\Controllers\Api\Marketing\CalendarController as MarketingCalendarController;
use App\Http\Controllers\Api\Marketing\LeadsController as MarketingLeadsController;
use App\Http\Controllers\Api\Marketing\ReportController as MarketingReportController;
use App\Http\Controllers\Api\Public\PerumahanController as PublicPerumahanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Endpoints
|--------------------------------------------------------------------------
*/
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Tamu / Public Endpoints
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    Route::get('/perumahan', [PublicPerumahanController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Admin Endpoints
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/perumahan', [AdminPerumahanController::class, 'index']);
    Route::post('/perumahan', [AdminPerumahanController::class, 'store']);

    Route::post('/tipe-rumah/{idTipe}/kavling', [AdminKavlingController::class, 'store']);
    Route::put('/kavling/{id}', [AdminKavlingController::class, 'update']);

    Route::get('/leads', [AdminLeadsController::class, 'index']);
    Route::post('/leads/{id}/validate', [AdminLeadsController::class, 'validateLead']);

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

    Route::post('/calendar', [MarketingCalendarController::class, 'store']);

    Route::post('/reports', [MarketingReportController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Leads Endpoints
|--------------------------------------------------------------------------
*/
Route::prefix('leads')->middleware(['auth:sanctum', 'role:leads'])->group(function () {
    Route::get('/calendar', [LeadsCalendarController::class, 'index']);
    Route::get('/marketing-contact', [LeadsMarketingController::class, 'contact']);
});

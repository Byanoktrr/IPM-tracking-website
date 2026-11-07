<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EkspedisiController;
use App\Http\Controllers\PoController;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    return auth()->check() ? redirect()->route('admin') : redirect()->route('login');
});

// Authentication Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout Route (Auth)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public Tracking Routes (No Auth Required)
Route::get('/tracking', [PoController::class, 'trackingView'])->name('tracking');
Route::post('/tracking/search', [PoController::class, 'searchTracking'])->name('tracking.search');
Route::get('/po/image/{filename}', [PoController::class, 'serveImage'])->name('po.image');

// Protected Internal Dashboard Routes (Require Auth)
Route::middleware('auth')->group(function () {
    Route::get('/admin', [PoController::class, 'adminView'])->name('admin');
    Route::get('/supervisor', [PoController::class, 'supervisorView'])->name('supervisor');
    Route::get('/delivery', [PoController::class, 'deliveryView'])->name('delivery');

    // Excel Exports
    Route::get('/po/excel/all', [PoController::class, 'exportAllPo'])->name('po.excel.all');
    Route::get('/po/{id}/excel', [PoController::class, 'exportDeliveryOrder'])->whereNumber('id')->name('po.excel');

    // PO Endpoints
    Route::get('/po', [PoController::class, 'index'])->name('po.index');
    Route::post('/po', [PoController::class, 'store'])->name('po.store');
    Route::get('/po/{id}', [PoController::class, 'show'])->whereNumber('id')->name('po.show');
    Route::put('/po/{id}', [PoController::class, 'update'])->whereNumber('id')->name('po.update');
    Route::delete('/po/{id}', [PoController::class, 'destroy'])->whereNumber('id')->name('po.destroy');
    Route::patch('/po/{id}/status', [PoController::class, 'updateStatus'])->whereNumber('id')->name('po.update-status');
    Route::patch('/po/{id}/delivery', [PoController::class, 'updateDelivery'])->whereNumber('id')->name('po.update-delivery');

    // Ekspedisi Endpoints
    Route::get('/ekspedisi', [EkspedisiController::class, 'index'])->name('ekspedisi.index');
    Route::post('/ekspedisi', [EkspedisiController::class, 'store'])->name('ekspedisi.store');
    Route::delete('/ekspedisi/{id}', [EkspedisiController::class, 'destroy'])->whereNumber('id')->name('ekspedisi.destroy');
});

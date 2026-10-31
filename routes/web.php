<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RfmController;
use App\Http\Controllers\DailyActivityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Master User (/settings/users)
    Route::prefix('settings/users')->name('settings.users.')->group(function () {
        Route::get('', [UserController::class, 'index'])->name('index');
        Route::post('', [UserController::class, 'store'])->name('store');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Hak Akses (/settings/roles)
    Route::prefix('settings/roles')->name('settings.roles.')->group(function () {
        Route::get('', [RoleController::class, 'index'])->name('index');
        Route::post('', [RoleController::class, 'store'])->name('store');
        Route::put('{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    // RFM (Request For Maintenance)
    Route::get('rfm', [RfmController::class, 'index'])->name('rfm.index');
    Route::post('rfm', [RfmController::class, 'store'])->name('rfm.store');
    Route::put('rfm/{rfm}', [RfmController::class, 'update'])->name('rfm.update');
    Route::delete('rfm/{rfm}', [RfmController::class, 'destroy'])->name('rfm.destroy');
    Route::post('rfm/{rfm}/approve', [RfmController::class, 'approve'])->name('rfm.approve');
    Route::post('rfm/{rfm}/reject', [RfmController::class, 'reject'])->name('rfm.reject');
    Route::post('rfm/{rfm}/start', [RfmController::class, 'start'])->name('rfm.start');
    Route::post('rfm/{rfm}/resolve', [RfmController::class, 'resolve'])->name('rfm.resolve');
    Route::post('rfm/{rfm}/review', [RfmController::class, 'review'])->name('rfm.review');

    Route::resource('daily-activity', DailyActivityController::class);
});

Route::get('/', function () {
    return redirect()->route('login');
});

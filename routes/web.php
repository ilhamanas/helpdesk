<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RfmController;
use App\Http\Controllers\DailyActivityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ReportController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Master User (/settings/users) — admin only
    Route::middleware('role:admin')->group(function () {
        Route::prefix('settings/users')->name('settings.users.')->group(function () {
            Route::get('', [UserController::class, 'index'])->name('index');
            Route::post('', [UserController::class, 'store'])->name('store');
            Route::put('{user}', [UserController::class, 'update'])->name('update');
            Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
        });

        // Hak Akses (/settings/roles) — admin only
        Route::prefix('settings/roles')->name('settings.roles.')->group(function () {
            Route::get('', [RoleController::class, 'index'])->name('index');
            Route::post('', [RoleController::class, 'store'])->name('store');
            Route::put('{role}', [RoleController::class, 'update'])->name('update');
            Route::delete('{role}', [RoleController::class, 'destroy'])->name('destroy');
        });
    });

    // RFM (Request For Maintenance)
    Route::get('rfm', [RfmController::class, 'index'])->name('rfm.index');
    Route::post('rfm', [RfmController::class, 'store'])->name('rfm.store');
    Route::put('rfm/{rfm}', [RfmController::class, 'update'])->name('rfm.update');
    Route::delete('rfm/{rfm}', [RfmController::class, 'destroy'])->name('rfm.destroy');
    Route::post('rfm/{rfm}/review', [RfmController::class, 'review'])->name('rfm.review');

    Route::post('rfm/{rfm}/approve', [RfmController::class, 'approve'])->name('rfm.approve')->middleware('role:hrd,admin');
    Route::post('rfm/{rfm}/reject', [RfmController::class, 'reject'])->name('rfm.reject')->middleware('role:hrd,admin');
    Route::post('rfm/{rfm}/start', [RfmController::class, 'start'])->name('rfm.start')->middleware('role:technician,admin');
    Route::post('rfm/{rfm}/resolve', [RfmController::class, 'resolve'])->name('rfm.resolve')->middleware('role:technician,admin');

    // Daily Activity — teknisi input; HRD/admin monitoring
    Route::get('daily-activity', [DailyActivityController::class, 'index'])
        ->name('daily-activity.index')->middleware('role:technician,hrd,admin');
    Route::post('daily-activity', [DailyActivityController::class, 'store'])
        ->name('daily-activity.store')->middleware('role:technician');
    Route::put('daily-activity/{dailyActivity}', [DailyActivityController::class, 'update'])
        ->name('daily-activity.update')->middleware('role:technician');
    Route::delete('daily-activity/{dailyActivity}', [DailyActivityController::class, 'destroy'])
        ->name('daily-activity.destroy')->middleware('role:technician');

    Route::get('report', [ReportController::class, 'index'])
        ->name('report.index')->middleware('role:hrd,admin');
});

Route::get('/', function () {
    return redirect()->route('login');
});

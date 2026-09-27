<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\EmployeeController;

// Public Guest Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Root route
Route::get('/', function () {
    return redirect('/login');
});

// Protected Admin Routes
Route::middleware(['auth', 'role:Administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('employees', EmployeeController::class);
});
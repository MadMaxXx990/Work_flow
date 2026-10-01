<?php

use Illuminate\Support\Facades\Route;

// ── Controllers ────────────────────────────────────────────────────────────────
use App\Http\Controllers\AuthController;

// Admin
use App\Http\Controllers\Admin\DashboardController    as AdminDash;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\TaskController         as AdminTaskController;
use App\Http\Controllers\Admin\AttendanceController   as AdminAttendanceController;
use App\Http\Controllers\Admin\FileController         as AdminFileController;
use App\Http\Controllers\Admin\ReportController       as AdminReportController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\NotificationController as AdminNotifController;

// Manager
use App\Http\Controllers\Manager\DashboardController  as ManagerDash;
use App\Http\Controllers\Manager\TaskController       as ManagerTaskController;
use App\Http\Controllers\Manager\AttendanceController as ManagerAttendanceController;
use App\Http\Controllers\Manager\FileController       as ManagerFileController;
use App\Http\Controllers\Manager\ReportController     as ManagerReportController;
use App\Http\Controllers\Manager\NotificationController as ManagerNotifController;

// Employee
use App\Http\Controllers\Employee\DashboardController as EmployeeDash;
use App\Http\Controllers\Employee\TaskController      as EmployeeTaskController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\FileController      as EmployeeFileController;
use App\Http\Controllers\Employee\NotificationController as EmployeeNotifController;

// ── Public / Guest ─────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Administrator ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:Administrator'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDash::class, 'index'])->name('dashboard');

        // Organization
        Route::resource('employees',   EmployeeController::class);
        Route::resource('departments', DepartmentController::class, ['except' => ['show']]);
        Route::resource('positions',   PositionController::class,   ['except' => ['show']]);

        // Workflow
        Route::resource('tasks', AdminTaskController::class);
        Route::post('/tasks/{task}/approve',       [AdminTaskController::class, 'approve'])->name('tasks.approve');
        Route::post('/tasks/{task}/change-stage',  [AdminTaskController::class, 'changeStage'])->name('tasks.change-stage');
        Route::post('/tasks/{task}/comment',       [AdminTaskController::class, 'addComment'])->name('tasks.comment');
        Route::delete('/tasks/{task}/comments/{comment}', [AdminTaskController::class, 'deleteComment'])->name('tasks.comments.destroy');

        // Attendance
        Route::resource('attendance', AdminAttendanceController::class, [
            'parameters' => ['attendance' => 'attendance'],
            'except'     => ['show'],
        ]);

        // Files
        Route::get('/files',              [AdminFileController::class, 'index'])->name('files.index');
        Route::get('/files/{attachment}', [AdminFileController::class, 'download'])->name('files.download');
        Route::delete('/files/{attachment}', [AdminFileController::class, 'destroy'])->name('files.destroy');

        // Reports
        Route::get('/reports',        [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');

        // Activity Log
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        // Notifications
        Route::get('/notifications',           [AdminNotifController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read',[AdminNotifController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [AdminNotifController::class, 'markAllRead'])->name('notifications.read-all');
    });

// ── Manager ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:Manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {

        Route::get('/dashboard', [ManagerDash::class, 'index'])->name('dashboard');

        // Tasks
        Route::resource('tasks', ManagerTaskController::class);
        Route::post('/tasks/{task}/approve',      [ManagerTaskController::class, 'approve'])->name('tasks.approve');
        Route::post('/tasks/{task}/change-stage', [ManagerTaskController::class, 'changeStage'])->name('tasks.change-stage');
        Route::post('/tasks/{task}/comment',      [ManagerTaskController::class, 'addComment'])->name('tasks.comment');
        Route::delete('/tasks/{task}/comments/{comment}', [ManagerTaskController::class, 'deleteComment'])->name('tasks.comments.destroy');
        Route::post('/tasks/{task}/attachments',  [ManagerTaskController::class, 'uploadAttachment'])->name('tasks.attachments.store');

        // Attendance
        Route::resource('attendance', ManagerAttendanceController::class, [
            'parameters' => ['attendance' => 'attendance'],
            'except'     => ['show'],
        ]);

        // Files
        Route::get('/files',              [ManagerFileController::class, 'index'])->name('files.index');
        Route::get('/files/{attachment}', [ManagerFileController::class, 'download'])->name('files.download');

        // Reports
        Route::get('/reports',        [ManagerReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ManagerReportController::class, 'export'])->name('reports.export');

        // Notifications
        Route::get('/notifications',           [ManagerNotifController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read',[ManagerNotifController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [ManagerNotifController::class, 'markAllRead'])->name('notifications.read-all');
    });

// ── Employee ───────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:Employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {

        Route::get('/dashboard', [EmployeeDash::class, 'index'])->name('dashboard');

        // Tasks (read + update own tasks)
        Route::get('/tasks',                       [EmployeeTaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}',                [EmployeeTaskController::class, 'show'])->name('tasks.show');
        Route::post('/tasks/{task}/update',        [EmployeeTaskController::class, 'postUpdate'])->name('tasks.update');
        Route::post('/tasks/{task}/submit-review', [EmployeeTaskController::class, 'submitForReview'])->name('tasks.submit-review');
        Route::post('/tasks/{task}/comment',       [EmployeeTaskController::class, 'addComment'])->name('tasks.comment');
        Route::post('/tasks/{task}/attachments',   [EmployeeTaskController::class, 'uploadAttachment'])->name('tasks.attachments.store');

        // Attendance
        Route::get('/attendance',        [EmployeeAttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/time-in', [EmployeeAttendanceController::class, 'timeIn'])->name('attendance.time-in');
        Route::post('/attendance/time-out',[EmployeeAttendanceController::class, 'timeOut'])->name('attendance.time-out');

        // Files
        Route::get('/files',              [EmployeeFileController::class, 'index'])->name('files.index');
        Route::get('/files/{attachment}', [EmployeeFileController::class, 'download'])->name('files.download');

        // Notifications
        Route::get('/notifications',           [EmployeeNotifController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read',[EmployeeNotifController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [EmployeeNotifController::class, 'markAllRead'])->name('notifications.read-all');
    });

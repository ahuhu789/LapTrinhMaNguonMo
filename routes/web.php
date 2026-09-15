<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TalentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\StreamerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiChatController;

/*
|--------------------------------------------------------------------------
| Web Routes - MCN Talent Platform
|--------------------------------------------------------------------------
*/

// --- 1. Public Routes (Khách vãng lai & Tất cả người dùng) ---
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/talents', [TalentController::class, 'index'])->name('talents.index');
Route::get('/talents/{id}', [TalentController::class, 'show'])->name('talents.show');

// AI Assistant Endpoint (Tư vấn công khai cho khách hàng)
Route::post('/ai/recommend', [AiChatController::class, 'recommend'])->name('ai.recommend');

// --- 2. Authentication Routes ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- 3. Authenticated Routes chung (Tất cả vai trò đã đăng nhập) ---
Route::middleware('auth')->group(function () {
    // Quản lý & tra cứu Booking theo phân quyền RBAC
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
});

// --- 4. Manager & Admin Routes (Quản lý vận hành) ---
Route::middleware(['auth', 'role:manager,admin'])->group(function () {
    Route::get('/manager/dashboard', [AdminController::class, 'managerDashboard'])->name('manager.dashboard');
    
    // Duyệt và Từ chối booking kèm kiểm tra xung đột thời gian
    Route::post('/bookings/{id}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{id}/reject', [BookingController::class, 'reject'])->name('bookings.reject');

    // Quản lý KPI
    Route::get('/manager/kpis', [KpiController::class, 'index'])->name('manager.kpis');
    Route::post('/manager/kpis', [KpiController::class, 'store'])->name('manager.kpis.store');

    // Upload file CSV số liệu và tải mẫu
    Route::get('/manager/metrics/upload', [MetricsController::class, 'showUploadForm'])->name('manager.metrics.upload');
    Route::post('/manager/metrics/upload', [MetricsController::class, 'uploadCsv'])->name('manager.metrics.store');
    Route::get('/manager/metrics/sample', [MetricsController::class, 'downloadSample'])->name('manager.metrics.sample');
});

// --- 5. Streamer Workspace Routes (Dành riêng cho Streamer) ---
Route::middleware(['auth', 'role:streamer'])->group(function () {
    Route::get('/streamer/dashboard', [StreamerController::class, 'dashboard'])->name('streamer.dashboard');
    Route::get('/streamer/profile', [StreamerController::class, 'editProfile'])->name('streamer.profile');
    Route::post('/streamer/profile', [StreamerController::class, 'updateProfile'])->name('streamer.profile.update');
    Route::get('/streamer/schedule', [StreamerController::class, 'schedule'])->name('streamer.schedule');
    Route::post('/streamer/schedule', [StreamerController::class, 'addEvent'])->name('streamer.schedule.store');
});

// --- 6. Admin Control Center Routes (Toàn quyền quản trị) ---
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'usersIndex'])->name('admin.users');
    Route::post('/admin/users/{id}/role', [AdminController::class, 'updateUserRole'])->name('admin.users.role');
    Route::get('/admin/assign-streamers', [AdminController::class, 'assignStreamers'])->name('admin.assign');
    Route::post('/admin/assign-streamers/{id}', [AdminController::class, 'updateStreamerManager'])->name('admin.assign.update');
});

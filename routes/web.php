<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\RisalahRapatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/ganti-password-wajib', [LoginController::class, 'forceChangeForm'])->name('password.force-change');
    Route::post('/ganti-password-wajib', [LoginController::class, 'forceChange'])->name('password.force-change.submit');

    // Profil pengguna
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profil/ubah-password', [ProfileController::class, 'changePasswordForm'])->name('profile.change-password');
    Route::post('/profil/ubah-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Dashboard (view berbeda per role, lihat DashboardController)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Analitik (admin & pimpinan)
    Route::get('/analitik', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('analitik');
    Route::get('/analitik/biaya/{kategori}', [App\Http\Controllers\AnalyticsController::class, 'detailKategori'])
        ->name('analitik.detail-kategori');
    Route::get('/analitik/export-csv', [App\Http\Controllers\AnalyticsController::class, 'exportCsv'])->name('analitik.export-csv');

    // ── Sistem Tiket ─────────────────────────────────────────────────────────
    Route::prefix('tiket')->name('tiket.')->group(function () {
        Route::get('/',             [TicketController::class, 'index'])->name('index');
        Route::get('/buat',         [TicketController::class, 'create'])->name('create');
        Route::post('/',            [TicketController::class, 'store'])->name('store');
        Route::get('/{tiket}',      [TicketController::class, 'show'])->name('show');

        // Aksi per role
        Route::post('/{tiket}/klasifikasi',       [TicketController::class, 'updateKlasifikasi'])->name('klasifikasi');
        Route::post('/{tiket}/alokasi',           [TicketController::class, 'alokasi'])->name('alokasi');
        Route::post('/{tiket}/setujui',           [TicketController::class, 'setujui'])->name('setujui');
        Route::post('/{tiket}/tolak',             [TicketController::class, 'tolak'])->name('tolak');
        Route::post('/{tiket}/selesai',           [TicketController::class, 'selesai'])->name('selesai');
        Route::post('/{tiket}/tutup',             [TicketController::class, 'tutup'])->name('tutup');
        Route::get('/{tiket}/attachment/view',    [TicketController::class, 'viewAttachment'])->name('attachment.view');
        Route::get('/{tiket}/attachment/download',[TicketController::class, 'downloadAttachment'])->name('attachment.download');
    });

    // ── Modul CRUD Generik (20 modul, lihat config/modules.php) ─────────────
    Route::prefix('modul/{key}')->name('modul.')->group(function () {
        Route::get('/',          [ModuleController::class, 'index'])->name('index');
        Route::get('/tambah',    [ModuleController::class, 'create'])->name('create');
        Route::post('/',         [ModuleController::class, 'store'])->name('store');
        Route::get('/{id}/ubah',[ModuleController::class, 'edit'])->name('edit');
        Route::put('/{id}',     [ModuleController::class, 'update'])->name('update');
        Route::delete('/{id}',  [ModuleController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/setujui', [ModuleController::class, 'approve'])->name('approve');
        Route::post('/{id}/tolak',   [ModuleController::class, 'reject'])->name('reject');
    });

    // Risalah Rapat
    Route::resource('risalah', RisalahRapatController::class)->except(['show']);

    // ── Administrasi (Admin Only) ─────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions');

        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
    });

    // Rehash audit log (admin, tanpa prefix middleware group agar backward-compatible)
    Route::post('/admin/audit-log/rehash', [AuditLogController::class, 'rehash'])
        ->name('admin.audit-log.rehash')
        ->middleware('admin');
});
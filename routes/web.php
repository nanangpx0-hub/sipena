<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompilationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorialController;
use App\Http\Controllers\IngestionController;
use App\Http\Controllers\SkdController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Autentikasi (tamatan & undangan sesi)
// ---------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:12,1')->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------------------------------------------------------------------------
// Seluruh modul SI-PENA wajib login (RBAC: operator, editor, approver, viewer)
// ---------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    // Dashboard Utama (semua peran)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Tab Cover & Pembatas (lihat: semua peran, unggah: Editor & Approver)
    Route::get('/covers', [DashboardController::class, 'covers'])->name('covers.index');
    Route::post('/covers/upload', [DashboardController::class, 'uploadCover'])
        ->middleware('role:editor,approver')->name('covers.upload');

    // FASE 4: Ingestion (Operator)
    Route::prefix('ingestion')->name('ingestion.')->middleware('role:operator,approver')->group(function () {
        Route::get('/', [IngestionController::class, 'index'])->name('index');
        Route::post('/upload', [IngestionController::class, 'upload'])->name('upload');
    });

    // FASE 4: Editorial (Editor)
    Route::prefix('editorial')->name('editorial.')->middleware('role:editor,approver')->group(function () {
        Route::get('/', [EditorialController::class, 'index'])->name('index');
        Route::get('/{narrative}/edit', [EditorialController::class, 'edit'])->name('edit');
        Route::put('/{narrative}', [EditorialController::class, 'update'])->name('update');
        Route::post('/{narrative}/submit', [EditorialController::class, 'submitForApproval'])->name('submit');
    });

    // FASE 4: Approval (Approver)
    Route::prefix('approval')->name('approval.')->middleware('role:approver')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::get('/{id}', [ApprovalController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [ApprovalController::class, 'reject'])->name('reject');
    });

    // FASE 5: Compilation & Batch Queue (eksekusi: Approver)
    Route::prefix('compilation')->name('compilation.')->group(function () {
        Route::get('/', [CompilationController::class, 'index'])->name('index');
        Route::get('/{id}/download', [CompilationController::class, 'downloadPdf'])->name('download');
        Route::post('/{id}/compile', [CompilationController::class, 'compileSingle'])
            ->middleware('role:approver')->name('compile');
        Route::post('/batch-all-kda', [CompilationController::class, 'batchCompileAllKDA'])
            ->middleware('role:approver')->name('batch-all');
    });

    // Tab Analisis SKD (lihat: semua peran, unggah: Operator & Approver)
    Route::prefix('skd')->name('skd.')->group(function () {
        Route::get('/', [SkdController::class, 'index'])->name('index');
        Route::post('/upload', [SkdController::class, 'upload'])
            ->middleware('role:operator,approver')->name('upload');
    });
});

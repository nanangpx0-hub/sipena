<?php

use App\Http\Controllers\ActiveYearController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompilationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorialController;
use App\Http\Controllers\IngestionController;
use App\Http\Controllers\PublicationWorkspaceController;
use App\Http\Controllers\SkdController;
use App\Http\Controllers\TableExportController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Autentikasi (tamatan & undangan sesi)
// ---------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:12,1')->name('login.attempt');

    // Portal Gerbang SI-PENA (halaman publik/intranet tanpa autentikasi).
    // Rute "/" TETAP menjadi dashboard terproteksi (lihat ExampleTest & E2E S1).
    Route::get('/portal', fn () => view('welcome'))->name('portal');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------------------------------------------------------------------------
// Seluruh modul SI-PENA wajib login (RBAC: operator, editor, approver, viewer)
// ---------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    // Dashboard Utama (semua peran)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Sesi Tahun Aktif (multi-tahun): dipilih dari dropdown header.
    Route::post('/set-active-year', [ActiveYearController::class, 'store'])
        ->name('active-year.set');

    // Tab Cover & Pembatas (lihat: semua peran, unggah: Editor & Approver)
    Route::get('/covers', [DashboardController::class, 'covers'])->name('covers.index');
    Route::post('/covers/upload', [DashboardController::class, 'uploadCover'])
        ->middleware('role:admin')->name('covers.upload');

    // FASE 4: Ingestion (Operator)
    Route::prefix('ingestion')->name('ingestion.')->middleware('role:operator,admin')->group(function () {
        Route::get('/', [IngestionController::class, 'index'])->name('index');
        Route::post('/upload', [IngestionController::class, 'upload'])->name('upload');
        Route::get('/template/{type}', [IngestionController::class, 'downloadTemplate'])->name('template.download');
        Route::get('/raw-files/{rawFile}/download', [IngestionController::class, 'downloadRawFile'])->name('raw-file.download');
        Route::get('/raw-files/{rawFile}/preview', [IngestionController::class, 'previewExtractedData'])->name('raw-file.preview');
    });

    // FASE 4: Editorial (Admin)
    Route::prefix('editorial')->name('editorial.')->middleware('role:admin')->group(function () {
        Route::get('/', [EditorialController::class, 'index'])->name('index');
        Route::get('/{narrative}/edit', [EditorialController::class, 'edit'])->name('edit');
        Route::put('/{narrative}', [EditorialController::class, 'update'])->name('update');
        Route::post('/{narrative}/submit', [EditorialController::class, 'submitForApproval'])->name('submit');
    });

    // FASE 4: Approval (Admin)
    Route::prefix('approval')->name('approval.')->middleware('role:admin')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::get('/{id}', [ApprovalController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [ApprovalController::class, 'reject'])->name('reject');
    });

    // Laporan cetak catatan audit & mutu data (Approver + Viewer)
    Route::get('/approval/{id}/audit-note', [ApprovalController::class, 'auditNote'])
        ->whereNumber('id')
        ->middleware('role:admin,viewer')
        ->name('approval.audit-note');

    // Ekspor tabel hasil ingesti ke CSV (streaming, tanpa library eksternal)
    Route::get('/tables/{tableId}/export-csv', [TableExportController::class, 'exportCsv'])
        ->whereNumber('tableId')
        ->name('tables.export-csv');

    // FASE 5: Compilation & Batch Queue (eksekusi: Admin)
    Route::prefix('compilation')->name('compilation.')->group(function () {
        // Status antrean untuk polling Alpine (dideklarasikan lebih awal agar tidak tertimpa /{id})
        Route::get('/queue-status', [CompilationController::class, 'queueStatus'])
            ->name('queue-status');

        Route::get('/', [CompilationController::class, 'index'])->name('index');
        Route::get('/{id}/export-csv/{tableId}', [TableExportController::class, 'exportCsv'])
            ->whereNumber('id')->whereNumber('tableId')->name('export-csv');
        Route::get('/{id}/download', [CompilationController::class, 'downloadPdf'])->name('download');
        Route::post('/{id}/compile', [CompilationController::class, 'compileSingle'])
            ->middleware('role:admin')->name('compile');
        Route::post('/batch-all-kda', [CompilationController::class, 'batchCompileAllKDA'])
            ->middleware('role:admin')->name('batch-all');
    });

    // Tab Analisis SKD (lihat: semua peran, unggah: Operator & Admin)
    Route::prefix('skd')->name('skd.')->group(function () {
        Route::get('/', [SkdController::class, 'index'])->name('index');
        Route::post('/upload', [SkdController::class, 'upload'])
            ->middleware('role:operator,admin')->name('upload');
    });

    // Publication Workspace — pusat kendali terpusat per buku publikasi
    Route::prefix('publications/{publication}')->name('pub.')->middleware('role:admin,editor')->group(function () {
        Route::get('/workspace', [PublicationWorkspaceController::class, 'show'])->name('workspace');

        Route::put('/pages/cover', [PublicationWorkspaceController::class, 'updateCover'])->name('page.cover.update');
        Route::put('/pages/imprimatur', [PublicationWorkspaceController::class, 'updateImprimatur'])->name('page.imprimatur.update');
        Route::put('/pages/team', [PublicationWorkspaceController::class, 'updateTeam'])->name('page.team.update');
        Route::put('/pages/preface', [PublicationWorkspaceController::class, 'updatePreface'])->name('page.preface.update');
        Route::put('/pages/abbreviations', [PublicationWorkspaceController::class, 'updateAbbreviations'])->name('page.abbr.update');
        Route::put('/pages/chapters/{narrative}', [PublicationWorkspaceController::class, 'updateChapter'])->name('page.chapter.update');
        Route::post('/generate', [PublicationWorkspaceController::class, 'generate'])->name('generate');
    });
});

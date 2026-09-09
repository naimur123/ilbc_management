<?php

use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

/**
 * No-SSH installer routes (Section 46). Registered directly in
 * bootstrap/app.php (not routes/web.php) so they work even before the
 * database exists — every route here is guarded by InstallGuard, which
 * 404s the whole group forever once storage/installed.lock is written.
 */
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'welcome'])->name('welcome');
    Route::get('database', [InstallController::class, 'databaseForm'])->name('database');
    Route::post('database', [InstallController::class, 'databaseSave'])->name('database.save');
    Route::get('migrate', [InstallController::class, 'migrateForm'])->name('migrate');
    Route::post('migrate', [InstallController::class, 'migrateRun'])->name('migrate.run');
    Route::get('admin', [InstallController::class, 'adminForm'])->name('admin');
    Route::post('admin', [InstallController::class, 'adminSave'])->name('admin.save');
    Route::get('done', [InstallController::class, 'done'])->name('done');
});

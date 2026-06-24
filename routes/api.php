<?php

declare(strict_types=1);

use App\Http\Controllers\AssetController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

Route::get('/', static fn () => response()->json([
    'service' => 'workwize-mdm-api',
    'endpoints' => [
        'GET    /api/stats',
        'GET    /api/assets',
        'GET    /api/assets/{asset}',
        'DELETE /api/assets/{asset}',
        'GET    /api/employees',
        'DELETE /api/employees/{employee}',
        'POST   /api/imports',
        'GET    /api/imports/{import}',
    ],
]));

Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
Route::get('/assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');

Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

Route::post('/imports', [ImportController::class, 'store'])->name('imports.store');
Route::get('/imports/{import}', [ImportController::class, 'show'])->name('imports.show');

<?php

declare(strict_types = 1);

use Centrex\Security\Http\Controllers\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'can:security.audit.view'])
    ->prefix('security')
    ->as('security.')
    ->group(function (): void {
        Route::get('/', [SecurityController::class, 'dashboard'])->name('dashboard');
        Route::get('/risk-flags', [SecurityController::class, 'index'])->name('risk-flags.index');
        Route::get('/risk-flags/{riskFlag}', [SecurityController::class, 'show'])->name('risk-flags.review');
        Route::patch('/risk-flags/{riskFlag}/resolve', [SecurityController::class, 'resolve'])
            ->middleware('can:security.incidents.manage')
            ->name('risk-flags.resolve');
        Route::get('/activities', [SecurityController::class, 'activities'])->name('activities.index');
        Route::get('/approvals', [SecurityController::class, 'approvals'])->name('approvals.index');
        Route::patch('/approvals/{approval}/approve', [SecurityController::class, 'approve'])
            ->middleware('can:security.incidents.manage')
            ->name('approvals.approve');
        Route::get('/ip-lists', [SecurityController::class, 'ipLists'])->name('ip-lists.index');
        Route::get('/users/{userId}/timeline', [SecurityController::class, 'timeline'])->name('users.timeline');

        Route::middleware('can:security.incidents.manage')->group(function (): void {
            Route::get('/roles', [SecurityController::class, 'roles'])->name('roles.index');
            Route::post('/roles', [SecurityController::class, 'storeRole'])->name('roles.store');
            Route::patch('/roles/{roleId}', [SecurityController::class, 'updateRole'])->name('roles.update');
            Route::get('/permissions', [SecurityController::class, 'permissions'])->name('permissions.index');
            Route::post('/permissions', [SecurityController::class, 'storePermission'])->name('permissions.store');
            Route::patch('/permissions/{permissionId}', [SecurityController::class, 'updatePermission'])->name('permissions.update');
        });
    });

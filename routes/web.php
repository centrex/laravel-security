<?php

declare(strict_types = 1);

use Centrex\Security\Http\Controllers\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('security')
    ->as('security.')
    ->group(function (): void {
        Route::get('/', [SecurityController::class, 'dashboard'])->name('dashboard');
        Route::get('/risk-flags', [SecurityController::class, 'index'])->name('risk-flags.index');
        Route::get('/risk-flags/{riskFlag}', [SecurityController::class, 'show'])->name('risk-flags.review');
        Route::patch('/risk-flags/{riskFlag}/resolve', [SecurityController::class, 'resolve'])->name('risk-flags.resolve');
        Route::get('/activities', [SecurityController::class, 'activities'])->name('activities.index');
        Route::get('/approvals', [SecurityController::class, 'approvals'])->name('approvals.index');
        Route::patch('/approvals/{approval}/approve', [SecurityController::class, 'approve'])->name('approvals.approve');
        Route::get('/ip-lists', [SecurityController::class, 'ipLists'])->name('ip-lists.index');
        Route::get('/users/{userId}/timeline', [SecurityController::class, 'timeline'])->name('users.timeline');
    });

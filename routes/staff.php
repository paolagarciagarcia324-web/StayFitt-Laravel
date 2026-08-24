<?php

use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\NovedadWelcomeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:ASISTENTE_TECNICO,ADMIN'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/novedades', [NovedadWelcomeController::class, 'index'])->name('novedades.index');
        Route::get('/novedades/crear', [NovedadWelcomeController::class, 'create'])->name('novedades.create');
        Route::post('/novedades', [NovedadWelcomeController::class, 'store'])->name('novedades.store');
        Route::get('/novedades/{novedad}/editar', [NovedadWelcomeController::class, 'edit'])->name('novedades.edit');
        Route::put('/novedades/{novedad}', [NovedadWelcomeController::class, 'update'])->name('novedades.update');
        Route::delete('/novedades/{novedad}', [NovedadWelcomeController::class, 'destroy'])->name('novedades.destroy');
    });

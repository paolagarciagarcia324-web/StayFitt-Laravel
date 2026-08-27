<?php

use App\Http\Controllers\Institucional\ComunicacionController;
use App\Http\Controllers\Institucional\DashboardController;
use App\Http\Controllers\Institucional\InstitucionContextController;
use App\Http\Controllers\Institucional\InvitacionController;
use App\Http\Controllers\Institucional\PerfilController;
use App\Http\Controllers\Institucional\PlanController;
use App\Http\Controllers\Institucional\ReporteController;
use App\Http\Controllers\Institucional\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:RESPONSABLE_INSTITUCIONAL,ADMIN'])
    ->prefix('institucional')
    ->name('institucional.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/perfil', [PerfilController::class, 'show'])->name('perfil.show');
        Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');

        Route::post('/contexto', [InstitucionContextController::class, 'update'])->name('contexto.update');

        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/{id}', [UsuarioController::class, 'show'])->name('usuarios.show');

        Route::get('/invitaciones', [InvitacionController::class, 'index'])->name('invitaciones.index');
        Route::post('/invitaciones', [InvitacionController::class, 'storeInvitacion'])->name('invitaciones.store');
        Route::post('/invitaciones/enlace', [InvitacionController::class, 'storeEnlace'])->name('invitaciones.enlace');
        Route::delete('/invitaciones/{id}', [InvitacionController::class, 'destroyInvitacion'])->name('invitaciones.destroy');

        Route::get('/plan', [PlanController::class, 'index'])->name('plan.index');

        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/export', [ReporteController::class, 'export'])->name('reportes.export');

        Route::get('/comunicacion', [ComunicacionController::class, 'index'])->name('comunicacion.index');
        Route::post('/comunicacion', [ComunicacionController::class, 'store'])->name('comunicacion.store');
    });

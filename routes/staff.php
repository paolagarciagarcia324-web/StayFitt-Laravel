<?php

use App\Http\Controllers\Staff\ClienteController;
use App\Http\Controllers\Staff\CoachContextController;
use App\Http\Controllers\Staff\ComunicacionController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\NovedadWelcomeController;
use App\Http\Controllers\Staff\PerfilController;
use App\Http\Controllers\Staff\PropuestaController;
use App\Http\Controllers\Staff\RutinaController;
use App\Http\Controllers\Staff\VideoBibliotecaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:ASISTENTE_TECNICO,ADMIN'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/perfil', [PerfilController::class, 'show'])->name('perfil.show');
        Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');

        Route::post('/coach-context', [CoachContextController::class, 'update'])->name('coach-context.update');

        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('clientes.show');
        Route::post('/clientes/observacion', [ClienteController::class, 'observacion'])->name('clientes.observacion');

        Route::get('/rutinas', [RutinaController::class, 'index'])->name('rutinas.index');
        Route::post('/rutinas/borrador', [RutinaController::class, 'storeBorrador'])->name('rutinas.borrador.store');

        Route::get('/propuestas', [PropuestaController::class, 'index'])->name('propuestas.index');

        Route::get('/videos', [VideoBibliotecaController::class, 'index'])->name('videos.index');
        Route::post('/videos', [VideoBibliotecaController::class, 'store'])->name('videos.store');

        Route::get('/comunicacion', [ComunicacionController::class, 'index'])->name('comunicacion.index');
        Route::post('/comunicacion/nota', [ComunicacionController::class, 'nota'])->name('comunicacion.nota');

        Route::get('/novedades', [NovedadWelcomeController::class, 'index'])->name('novedades.index');
        Route::get('/novedades/crear', [NovedadWelcomeController::class, 'create'])->name('novedades.create');
        Route::post('/novedades', [NovedadWelcomeController::class, 'store'])->name('novedades.store');
        Route::get('/novedades/{novedad}/editar', [NovedadWelcomeController::class, 'edit'])->name('novedades.edit');
        Route::put('/novedades/{novedad}', [NovedadWelcomeController::class, 'update'])->name('novedades.update');
        Route::delete('/novedades/{novedad}', [NovedadWelcomeController::class, 'destroy'])->name('novedades.destroy');
    });

<?php

use App\Http\Controllers\Coach\AgendaController;
use App\Http\Controllers\Coach\ClienteController;
use App\Http\Controllers\Coach\ComunicacionController;
use App\Http\Controllers\Coach\DashboardController;
use App\Http\Controllers\Coach\EntrenamientoController;
use App\Http\Controllers\Coach\NutricionPropuestaController;
use App\Http\Controllers\Coach\PerfilController;
use App\Http\Controllers\Coach\PropuestaPlanController;
use App\Http\Controllers\Coach\ReporteProgresoController;
use App\Http\Controllers\Coach\VideoBibliotecaController;
use App\Http\Controllers\Coach\VideoClaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:COACH,ASISTENTE_TECNICO,ADMIN'])
    ->prefix('coach')
    ->name('coach.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/perfil', [PerfilController::class, 'show'])->name('perfil.show');
        Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');

        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('clientes.show');

        Route::get('/entrenamiento', [EntrenamientoController::class, 'index'])->name('entrenamiento.index');
        Route::post('/entrenamiento/planes', [EntrenamientoController::class, 'storePlan'])->name('entrenamiento.planes.store');
        Route::get('/entrenamiento/{id}', [EntrenamientoController::class, 'show'])->name('entrenamiento.show');
        Route::post('/entrenamiento/{id}/rutinas', [EntrenamientoController::class, 'storeRutina'])->name('entrenamiento.rutinas.store');
        Route::put('/entrenamiento/{planId}/rutinas/{rutinaId}', [EntrenamientoController::class, 'updateRutina'])->name('entrenamiento.rutinas.update');

        Route::get('/videos', [VideoBibliotecaController::class, 'index'])->name('videos.index');
        Route::post('/videos', [VideoBibliotecaController::class, 'store'])->name('videos.store');
        Route::put('/videos/{id}', [VideoBibliotecaController::class, 'update'])->name('videos.update');

        Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
        Route::post('/agenda', [AgendaController::class, 'store'])->name('agenda.store');

        Route::get('/propuestas', [PropuestaPlanController::class, 'index'])->name('propuestas.index');
        Route::get('/propuestas/crear', [PropuestaPlanController::class, 'create'])->name('propuestas.create');
        Route::post('/propuestas', [PropuestaPlanController::class, 'store'])->name('propuestas.store');
        Route::get('/propuestas/{propuesta}/entrenamiento', [PropuestaPlanController::class, 'entrenamiento'])->name('propuestas.entrenamiento');
        Route::get('/propuestas/{propuesta}/nutricion', [PropuestaPlanController::class, 'nutricion'])->name('propuestas.nutricion');
        Route::get('/propuestas/{propuesta}/resumen', [PropuestaPlanController::class, 'resumen'])->name('propuestas.resumen');
        Route::post('/propuestas/{propuesta}/enviar', [PropuestaPlanController::class, 'enviar'])->name('propuestas.enviar');
        Route::put('/propuestas/{propuesta}/meta', [PropuestaPlanController::class, 'updateMeta'])->name('propuestas.meta');

        Route::post('/propuestas/{propuesta}/videos', [VideoClaseController::class, 'store'])->name('propuestas.videos.store');
        Route::put('/propuestas/{propuesta}/videos/{id}', [VideoClaseController::class, 'update'])->name('propuestas.videos.update');
        Route::delete('/propuestas/{propuesta}/videos/{id}', [VideoClaseController::class, 'destroy'])->name('propuestas.videos.destroy');

        Route::put('/propuestas/{propuesta}/nutricion', [NutricionPropuestaController::class, 'update'])->name('propuestas.nutricion.update');
        Route::post('/propuestas/{propuesta}/comidas', [NutricionPropuestaController::class, 'storeComida'])->name('propuestas.comidas.store');
        Route::put('/propuestas/{propuesta}/comidas/{comida}', [NutricionPropuestaController::class, 'updateComida'])->name('propuestas.comidas.update');
        Route::delete('/propuestas/{propuesta}/comidas/{comida}', [NutricionPropuestaController::class, 'destroyComida'])->name('propuestas.comidas.destroy');

        Route::get('/comunicacion', [ComunicacionController::class, 'index'])->name('comunicacion.index');
        Route::get('/comunicacion/{id}', [ComunicacionController::class, 'show'])->name('comunicacion.show');
        Route::post('/comunicacion/{id}/mensajes', [ComunicacionController::class, 'store'])->name('comunicacion.mensajes.store');

        Route::get('/reportes/progreso', [ReporteProgresoController::class, 'index'])->name('reportes.progreso');
        Route::post('/reportes/progreso/export', [ReporteProgresoController::class, 'export'])->name('reportes.progreso.export');
        Route::post('/reportes/progreso/observacion', [ReporteProgresoController::class, 'observacion'])->name('reportes.progreso.observacion');
    });

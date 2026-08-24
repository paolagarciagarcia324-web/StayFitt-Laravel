<?php

use App\Http\Controllers\Coach\DashboardController;
use App\Http\Controllers\Coach\NutricionPropuestaController;
use App\Http\Controllers\Coach\PropuestaPlanController;
use App\Http\Controllers\Coach\VideoClaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:COACH,ASISTENTE_TECNICO,ADMIN'])
    ->prefix('coach')
    ->name('coach.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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
    });

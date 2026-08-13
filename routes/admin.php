<?php

use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InstitucionController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitudes.show');
    Route::post('/solicitudes/{id}/aprobar', [SolicitudController::class, 'aprobar'])->name('solicitudes.aprobar');
    Route::post('/solicitudes/{id}/rechazar', [SolicitudController::class, 'rechazar'])->name('solicitudes.rechazar');

    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');

    Route::get('/planes', [PlanController::class, 'index'])->name('planes.index');
    Route::get('/planes/crear', [PlanController::class, 'create'])->name('planes.create');
    Route::post('/planes', [PlanController::class, 'store'])->name('planes.store');
    Route::get('/planes/{plan}/editar', [PlanController::class, 'edit'])->name('planes.edit');
    Route::put('/planes/{plan}', [PlanController::class, 'update'])->name('planes.update');

    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/coaches', [CoachController::class, 'index'])->name('coaches.index');

    Route::get('/instituciones', [InstitucionController::class, 'index'])->name('instituciones.index');
    Route::post('/instituciones', [InstitucionController::class, 'store'])->name('instituciones.store');
});

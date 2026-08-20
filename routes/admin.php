<?php

use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HorarioPresencialController;
use App\Http\Controllers\Admin\InstitucionController;
use App\Http\Controllers\Admin\ModuloProximoController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\PasarelaController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\TiendaController;
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
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/virtuales', [ClienteController::class, 'virtuales'])->name('clientes.virtuales');
    Route::get('/clientes/presenciales', [ClienteController::class, 'presenciales'])->name('clientes.presenciales');
    Route::get('/coaches', [CoachController::class, 'index'])->name('coaches.index');
    Route::post('/coaches', [CoachController::class, 'store'])->name('coaches.store');

    Route::get('/instituciones', [InstitucionController::class, 'index'])->name('instituciones.index');
    Route::post('/instituciones', [InstitucionController::class, 'store'])->name('instituciones.store');

    Route::get('/horarios', [HorarioPresencialController::class, 'index'])->name('horarios.index');
    Route::put('/horarios/{id}', [HorarioPresencialController::class, 'update'])->name('horarios.update');

    Route::get('/pasarelas', [PasarelaController::class, 'index'])->name('pasarelas.index');
    Route::put('/pasarelas/{id}', [PasarelaController::class, 'update'])->name('pasarelas.update');

    Route::get('/tienda', [TiendaController::class, 'index'])->name('tienda.index');
    Route::post('/tienda', [TiendaController::class, 'store'])->name('tienda.store');
    Route::put('/tienda/ordenes/{id}', [TiendaController::class, 'updateOrden'])->name('tienda.orden');
    Route::put('/tienda/{id}', [TiendaController::class, 'update'])->name('tienda.update');

    Route::get('/eventos', [ModuloProximoController::class, 'eventos'])->name('eventos.index');
    Route::get('/calendario', [ModuloProximoController::class, 'calendario'])->name('calendario.index');
    Route::get('/comunidad', [ModuloProximoController::class, 'comunidad'])->name('comunidad.index');
    Route::get('/estadisticas', [ModuloProximoController::class, 'estadisticas'])->name('estadisticas.index');
    Route::get('/reportes', [ModuloProximoController::class, 'reportes'])->name('reportes.index');
});

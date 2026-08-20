<?php

use App\Http\Controllers\Admin\CalendarioController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\ComunidadController;
use App\Http\Controllers\Admin\CuponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EstadisticaController;
use App\Http\Controllers\Admin\EventoController;
use App\Http\Controllers\Admin\HorarioPresencialController;
use App\Http\Controllers\Admin\InstitucionController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\PasarelaController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\TiendaController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\WhatsappController;
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

    Route::get('/cupones', [CuponController::class, 'index'])->name('cupones.index');
    Route::post('/cupones', [CuponController::class, 'store'])->name('cupones.store');
    Route::put('/cupones/{id}', [CuponController::class, 'update'])->name('cupones.update');

    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');

    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/virtuales', [ClienteController::class, 'virtuales'])->name('clientes.virtuales');
    Route::get('/clientes/presenciales', [ClienteController::class, 'presenciales'])->name('clientes.presenciales');
    Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::put('/clientes/{id}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::post('/clientes/{id}/coach', [ClienteController::class, 'asignarCoach'])->name('clientes.coach');
    Route::put('/clientes/{id}/accesos/{accesoId}', [ClienteController::class, 'updateAcceso'])->name('clientes.acceso');

    Route::get('/coaches', [CoachController::class, 'index'])->name('coaches.index');
    Route::post('/coaches', [CoachController::class, 'store'])->name('coaches.store');
    Route::put('/coaches/{id}', [CoachController::class, 'update'])->name('coaches.update');

    Route::get('/instituciones', [InstitucionController::class, 'index'])->name('instituciones.index');
    Route::post('/instituciones', [InstitucionController::class, 'store'])->name('instituciones.store');
    Route::put('/instituciones/{id}', [InstitucionController::class, 'update'])->name('instituciones.update');
    Route::post('/instituciones/{id}/vincular', [InstitucionController::class, 'vincularCliente'])->name('instituciones.vincular');
    Route::post('/instituciones/{id}/enlaces', [InstitucionController::class, 'storeEnlace'])->name('instituciones.enlaces');

    Route::get('/horarios', [HorarioPresencialController::class, 'index'])->name('horarios.index');
    Route::put('/horarios/{id}', [HorarioPresencialController::class, 'update'])->name('horarios.update');

    Route::get('/pasarelas', [PasarelaController::class, 'index'])->name('pasarelas.index');
    Route::put('/pasarelas/{id}', [PasarelaController::class, 'update'])->name('pasarelas.update');

    Route::get('/tienda', [TiendaController::class, 'index'])->name('tienda.index');
    Route::post('/tienda', [TiendaController::class, 'store'])->name('tienda.store');
    Route::put('/tienda/ordenes/{id}', [TiendaController::class, 'updateOrden'])->name('tienda.orden');
    Route::put('/tienda/{id}', [TiendaController::class, 'update'])->name('tienda.update');

    Route::get('/whatsapp', [WhatsappController::class, 'index'])->name('whatsapp.index');
    Route::put('/whatsapp', [WhatsappController::class, 'update'])->name('whatsapp.update');
    Route::post('/whatsapp/grupos', [WhatsappController::class, 'storeGrupo'])->name('whatsapp.grupos');

    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
    Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
    Route::put('/eventos/{id}', [EventoController::class, 'update'])->name('eventos.update');

    Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/comunidad', [ComunidadController::class, 'index'])->name('comunidad.index');
    Route::get('/comunidad/{id}', [ComunidadController::class, 'show'])->name('comunidad.show');
    Route::get('/estadisticas', [EstadisticaController::class, 'index'])->name('estadisticas.index');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::post('/reportes/export', [ReporteController::class, 'export'])->name('reportes.export');
});

<?php

use App\Http\Controllers\Admin\AnalisisController;
use App\Http\Controllers\Admin\AsistenteController;
use App\Http\Controllers\Admin\CalendarioController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\ComunidadController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\CuponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EstadisticaController;
use App\Http\Controllers\Admin\EventoController;
use App\Http\Controllers\Admin\HorarioPresencialController;
use App\Http\Controllers\Admin\NovedadController;
use App\Http\Controllers\Admin\InstitucionController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\PasarelaController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PropuestaPlanController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\RolController;
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
    Route::post('/solicitudes/{id}/suspender', [SolicitudController::class, 'suspender'])->name('solicitudes.suspender');

    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::get('/pagos/{id}', [PagoController::class, 'show'])->name('pagos.show');
    Route::put('/pagos/{id}', [PagoController::class, 'update'])->name('pagos.update');

    Route::get('/planes', [PlanController::class, 'index'])->name('planes.index');
    Route::get('/planes/crear', [PlanController::class, 'create'])->name('planes.create');
    Route::post('/planes', [PlanController::class, 'store'])->name('planes.store');
    Route::get('/planes/{plan}/editar', [PlanController::class, 'edit'])->name('planes.edit');
    Route::put('/planes/{plan}', [PlanController::class, 'update'])->name('planes.update');
    Route::delete('/planes/{plan}', [PlanController::class, 'destroy'])->name('planes.destroy');

    Route::get('/propuestas', [PropuestaPlanController::class, 'index'])->name('propuestas.index');
    Route::get('/propuestas/{propuesta}', [PropuestaPlanController::class, 'show'])->name('propuestas.show');
    Route::get('/propuestas/{propuesta}/publicar', [PropuestaPlanController::class, 'publicarForm'])->name('propuestas.publicar.form');
    Route::post('/propuestas/{propuesta}/publicar', [PropuestaPlanController::class, 'publicar'])->name('propuestas.publicar');
    Route::post('/propuestas/{propuesta}/rechazar', [PropuestaPlanController::class, 'rechazar'])->name('propuestas.rechazar');

    Route::get('/novedades', [NovedadController::class, 'index'])->name('novedades.index');
    Route::get('/novedades/crear', [NovedadController::class, 'create'])->name('novedades.create');
    Route::post('/novedades', [NovedadController::class, 'store'])->name('novedades.store');
    Route::get('/novedades/{novedad}/editar', [NovedadController::class, 'edit'])->name('novedades.edit');
    Route::put('/novedades/{novedad}', [NovedadController::class, 'update'])->name('novedades.update');
    Route::delete('/novedades/{novedad}', [NovedadController::class, 'destroy'])->name('novedades.destroy');

    Route::get('/cupones', [CuponController::class, 'index'])->name('cupones.index');
    Route::post('/cupones', [CuponController::class, 'store'])->name('cupones.store');
    Route::put('/cupones/{id}', [CuponController::class, 'update'])->name('cupones.update');
    Route::delete('/cupones/{id}', [CuponController::class, 'destroy'])->name('cupones.destroy');
    Route::get('/cupones/{id}/usos', [CuponController::class, 'usos'])->name('cupones.usos');

    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::post('/usuarios/{id}/reset-password', [UsuarioController::class, 'resetPassword'])->name('usuarios.reset-password');

    Route::get('/roles', [RolController::class, 'index'])->name('roles.index');

    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');

    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/virtuales', [ClienteController::class, 'virtuales'])->name('clientes.virtuales');
    Route::get('/clientes/presenciales', [ClienteController::class, 'presenciales'])->name('clientes.presenciales');
    Route::get('/clientes/{id}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::put('/clientes/{id}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::put('/clientes/{id}/completo', [ClienteController::class, 'updateCompleto'])->name('clientes.updateCompleto');
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    Route::post('/clientes/{id}/coach', [ClienteController::class, 'asignarCoach'])->name('clientes.coach');
    Route::put('/clientes/{id}/accesos/{accesoId}', [ClienteController::class, 'updateAcceso'])->name('clientes.acceso');

    Route::get('/coaches', [CoachController::class, 'index'])->name('coaches.index');
    Route::post('/coaches', [CoachController::class, 'store'])->name('coaches.store');
    Route::put('/coaches/{id}', [CoachController::class, 'update'])->name('coaches.update');
    Route::delete('/coaches/{id}', [CoachController::class, 'destroy'])->name('coaches.destroy');

    Route::get('/asistentes', [AsistenteController::class, 'index'])->name('asistentes.index');
    Route::post('/asistentes', [AsistenteController::class, 'store'])->name('asistentes.store');
    Route::put('/asistentes/{id}', [AsistenteController::class, 'update'])->name('asistentes.update');
    Route::delete('/asistentes/{id}', [AsistenteController::class, 'destroy'])->name('asistentes.destroy');

    Route::get('/instituciones', [InstitucionController::class, 'index'])->name('instituciones.index');
    Route::post('/instituciones', [InstitucionController::class, 'store'])->name('instituciones.store');
    Route::put('/instituciones/{id}', [InstitucionController::class, 'update'])->name('instituciones.update');
    Route::delete('/instituciones/{id}', [InstitucionController::class, 'destroy'])->name('instituciones.destroy');
    Route::post('/instituciones/{id}/vincular', [InstitucionController::class, 'vincularCliente'])->name('instituciones.vincular');
    Route::post('/instituciones/{id}/desvincular', [InstitucionController::class, 'desvincularCliente'])->name('instituciones.desvincular');
    Route::post('/instituciones/{id}/enlaces', [InstitucionController::class, 'storeEnlace'])->name('instituciones.enlaces');
    Route::delete('/instituciones/{id}/enlaces/{enlaceId}', [InstitucionController::class, 'destroyEnlace'])->name('instituciones.enlaces.destroy');

    Route::get('/horarios', [HorarioPresencialController::class, 'index'])->name('horarios.index');
    Route::post('/horarios', [HorarioPresencialController::class, 'store'])->name('horarios.store');
    Route::put('/horarios/{id}', [HorarioPresencialController::class, 'update'])->name('horarios.update');
    Route::delete('/horarios/{id}', [HorarioPresencialController::class, 'destroy'])->name('horarios.destroy');

    Route::get('/pasarelas', [PasarelaController::class, 'index'])->name('pasarelas.index');
    Route::put('/pasarelas/{id}', [PasarelaController::class, 'update'])->name('pasarelas.update');

    Route::get('/tienda', [TiendaController::class, 'index'])->name('tienda.index');
    Route::post('/tienda', [TiendaController::class, 'store'])->name('tienda.store');
    Route::put('/tienda/ordenes/{id}', [TiendaController::class, 'updateOrden'])->name('tienda.orden');
    Route::put('/tienda/{id}', [TiendaController::class, 'update'])->name('tienda.update');
    Route::delete('/tienda/{id}', [TiendaController::class, 'destroy'])->name('tienda.destroy');

    Route::get('/whatsapp', [WhatsappController::class, 'index'])->name('whatsapp.index');
    Route::put('/whatsapp', [WhatsappController::class, 'update'])->name('whatsapp.update');
    Route::post('/whatsapp/grupos', [WhatsappController::class, 'storeGrupo'])->name('whatsapp.grupos');
    Route::put('/whatsapp/grupos/{id}', [WhatsappController::class, 'updateGrupo'])->name('whatsapp.grupos.update');
    Route::delete('/whatsapp/grupos/{id}', [WhatsappController::class, 'destroyGrupo'])->name('whatsapp.grupos.destroy');

    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
    Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
    Route::put('/eventos/{id}', [EventoController::class, 'update'])->name('eventos.update');
    Route::delete('/eventos/{id}', [EventoController::class, 'destroy'])->name('eventos.destroy');

    Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::post('/calendario/sesiones', [CalendarioController::class, 'store'])->name('calendario.sesiones.store');
    Route::put('/calendario/sesiones/{id}', [CalendarioController::class, 'update'])->name('calendario.sesiones.update');
    Route::delete('/calendario/sesiones/{id}', [CalendarioController::class, 'destroy'])->name('calendario.sesiones.destroy');
    Route::get('/comunidad', [ComunidadController::class, 'index'])->name('comunidad.index');
    Route::get('/comunidad/{id}', [ComunidadController::class, 'show'])->name('comunidad.show');
    Route::put('/comunidad/{id}', [ComunidadController::class, 'updateChat'])->name('comunidad.update');
    Route::delete('/comunidad/{chatId}/mensajes/{mensajeId}', [ComunidadController::class, 'destroyMensaje'])->name('comunidad.mensajes.destroy');
    Route::get('/analisis', [AnalisisController::class, 'index'])->name('analisis.index');
    Route::get('/estadisticas', [EstadisticaController::class, 'index'])->name('estadisticas.index');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::post('/reportes/preview', [ReporteController::class, 'preview'])->name('reportes.preview');
    Route::post('/reportes/export', [ReporteController::class, 'export'])->name('reportes.export');
    Route::get('/reportes/{id}/download', [ReporteController::class, 'download'])->name('reportes.download');
});

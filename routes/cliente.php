<?php

use App\Http\Controllers\Cliente\AgendaController;
use App\Http\Controllers\Cliente\AnalisisController;
use App\Http\Controllers\Cliente\ComercialController;
use App\Http\Controllers\Cliente\ComunicacionController;
use App\Http\Controllers\Cliente\ContenidoController;
use App\Http\Controllers\Cliente\DashboardController;
use App\Http\Controllers\Cliente\EntrenamientoController;
use App\Http\Controllers\Cliente\NotificacionController;
use App\Http\Controllers\Cliente\NovedadController;
use App\Http\Controllers\Cliente\NutricionController;
use App\Http\Controllers\Cliente\PagoController;
use App\Http\Controllers\Cliente\PerfilController;
use App\Http\Controllers\Cliente\PerfilPresencialController;
use App\Http\Controllers\Cliente\PlanController;
use App\Http\Controllers\Cliente\ProgresoController;
use App\Http\Controllers\Cliente\TiendaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:CLIENTE,CLIENTE_INSTITUCIONAL'])
    ->prefix('cliente')
    ->name('cliente.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
        Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
        Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])->name('perfil.password');

        // Ficha / expediente presencial (perfilpresencial01)
        Route::get('/presencial', [PerfilPresencialController::class, 'show'])->name('presencial.perfil');
        Route::post('/presencial/asistencia', [PerfilPresencialController::class, 'checkin'])->name('presencial.checkin');
        Route::put('/presencial', [PerfilPresencialController::class, 'update'])->name('presencial.update');
        Route::put('/presencial/password', [PerfilPresencialController::class, 'updatePassword'])->name('presencial.password');

        Route::get('/mi-plan', [PlanController::class, 'index'])->name('plan.index');
        Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
        Route::get('/analisis', [AnalisisController::class, 'index'])->name('analisis.index');
        Route::get('/novedades', [NovedadController::class, 'index'])->name('novedades.index');

        Route::get('/comercial/planes', [ComercialController::class, 'planes'])->name('comercial.planes');
        Route::get('/comercial/planes/{id}', [ComercialController::class, 'planShow'])->name('comercial.planes.show');
        Route::get('/comercial/solicitudes', [ComercialController::class, 'solicitudes'])->name('comercial.solicitudes');
        Route::get('/comercial/cupones', [ComercialController::class, 'cupones'])->name('comercial.cupones');
        Route::post('/comercial/cupones/validar', [ComercialController::class, 'validarCupon'])->name('comercial.cupones.validar');

        // Invitaciones WA fuera del gate de módulo
        Route::post('/comunicacion/whatsapp/{id}/aceptar', [ComunicacionController::class, 'aceptarWhatsapp'])
            ->name('comunicacion.whatsapp.aceptar');
        Route::post('/comunicacion/whatsapp/{id}/cerrar', [ComunicacionController::class, 'cerrarWhatsapp'])
            ->name('comunicacion.whatsapp.cerrar');

        Route::middleware('modulo:COMUNICACION')->group(function () {
            Route::get('/comunicacion', [ComunicacionController::class, 'index'])->name('comunicacion.index');
            Route::get('/comunicacion/{id}', [ComunicacionController::class, 'show'])->name('comunicacion.show');
            Route::post('/comunicacion/{id}/mensajes', [ComunicacionController::class, 'store'])->name('comunicacion.mensajes.store');
        });

        Route::middleware('modulo:ENTRENAMIENTO')->group(function () {
            Route::get('/entrenamiento', [EntrenamientoController::class, 'index'])->name('entrenamiento.index');
            Route::get('/entrenamiento/rutinas/{id}', [EntrenamientoController::class, 'showRutina'])->name('entrenamiento.rutina');
            Route::post('/entrenamiento/rutinas/{id}/completar', [EntrenamientoController::class, 'completarRutina'])->name('entrenamiento.completar');
        });

        Route::middleware('modulo:NUTRICION')->group(function () {
            Route::get('/nutricion', [NutricionController::class, 'index'])->name('nutricion.index');
            Route::get('/nutricion/{id}', [NutricionController::class, 'show'])->name('nutricion.show');
        });

        Route::middleware('modulo:CONTENIDO_VIRTUAL')->group(function () {
            Route::get('/contenido', [ContenidoController::class, 'index'])->name('contenido.index');
            Route::get('/contenido/{id}', [ContenidoController::class, 'show'])->name('contenido.show');
            Route::post('/contenido/{id}/completar', [ContenidoController::class, 'completar'])->name('contenido.completar');
        });

        Route::middleware('modulo:PROGRESO')->group(function () {
            Route::get('/progreso', [ProgresoController::class, 'index'])->name('progreso.index');
            Route::get('/progreso/nuevo', [ProgresoController::class, 'create'])->name('progreso.create');
            Route::post('/progreso', [ProgresoController::class, 'store'])->name('progreso.store');
        });

        Route::middleware('modulo:AGENDA')->group(function () {
            Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
            Route::post('/agenda/{id}/inscribir', [AgendaController::class, 'inscribir'])->name('agenda.inscribir');
            Route::post('/agenda/{id}/cancelar', [AgendaController::class, 'cancelar'])->name('agenda.cancelar');
        });

        Route::middleware('modulo:TIENDA')->group(function () {
            Route::get('/tienda', [TiendaController::class, 'index'])->name('tienda.index');
            Route::get('/tienda/carrito', [TiendaController::class, 'carrito'])->name('tienda.carrito');
            Route::post('/tienda/{id}/agregar', [TiendaController::class, 'agregar'])->name('tienda.agregar');
            Route::put('/tienda/items/{id}', [TiendaController::class, 'actualizarItem'])->name('tienda.items.update');
            Route::post('/tienda/checkout', [TiendaController::class, 'checkout'])->name('tienda.checkout');
            Route::get('/tienda/ordenes', [TiendaController::class, 'ordenes'])->name('tienda.ordenes');
        });

        Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
        Route::post('/notificaciones/{id}/leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leida');
        Route::post('/notificaciones/leidas', [NotificacionController::class, 'marcarTodas'])->name('notificaciones.todas');
    });

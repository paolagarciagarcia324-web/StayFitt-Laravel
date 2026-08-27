<?php

use App\Http\Controllers\PreRegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistroInstitucionalController;
use App\Http\Controllers\RegistroPresencialController;
use App\Http\Controllers\Webhooks\WhatsappWebhookController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/pre-registro/{plan:slug}', [PreRegisterController::class, 'create'])->name('pre-registro.create');
Route::post('/pre-registro/{plan:slug}', [PreRegisterController::class, 'store'])->name('pre-registro.store');
Route::get('/pre-registro/exito/{codigo}', [PreRegisterController::class, 'success'])->name('pre-registro.success');

Route::get('/registro-presencial', [RegistroPresencialController::class, 'create'])->name('registro-presencial.create');
Route::post('/registro-presencial', [RegistroPresencialController::class, 'store'])->name('registro-presencial.store');
Route::get('/registro-presencial/pago/{codigo}', [RegistroPresencialController::class, 'checkout'])->name('registro-presencial.checkout');
Route::post('/registro-presencial/pago/{codigo}', [RegistroPresencialController::class, 'pagar'])->name('registro-presencial.pagar');
Route::get('/registro-presencial/exito/{codigo}', [RegistroPresencialController::class, 'success'])->name('registro-presencial.success');

Route::get('/webhooks/whatsapp', [WhatsappWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsappWebhookController::class, 'handle'])->name('webhooks.whatsapp.handle');

Route::get('/registro-institucional/enlace/{token}', [RegistroInstitucionalController::class, 'showEnlace'])->name('registro-institucional.enlace');
Route::post('/registro-institucional/enlace/{token}', [RegistroInstitucionalController::class, 'storeEnlace'])->name('registro-institucional.enlace.store');
Route::get('/registro-institucional/invitacion/{token}', [RegistroInstitucionalController::class, 'showInvitacion'])->name('registro-institucional.invitacion');
Route::post('/registro-institucional/invitacion/{token}', [RegistroInstitucionalController::class, 'storeInvitacion'])->name('registro-institucional.invitacion.store');

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user?->tieneRol('ADMIN')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user?->tieneRol('ASISTENTE_TECNICO')) {
        return redirect()->route('staff.dashboard');
    }

    if ($user?->tieneRol('COACH')) {
        return redirect()->route('coach.dashboard');
    }

    if ($user?->tieneRol('RESPONSABLE_INSTITUCIONAL')) {
        return redirect()->route('institucional.dashboard');
    }

    if ($user?->tieneRol('CLIENTE') || $user?->tieneRol('CLIENTE_INSTITUCIONAL')) {
        return redirect()->route('cliente.dashboard');
    }

    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/admin.php';
require __DIR__.'/coach.php';
require __DIR__.'/staff.php';
require __DIR__.'/institucional.php';
require __DIR__.'/cliente.php';
require __DIR__.'/auth.php';

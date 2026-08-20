<?php

use App\Http\Controllers\PreRegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistroPresencialController;
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

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user?->tieneRol('ADMIN')) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/admin.php';
require __DIR__.'/auth.php';

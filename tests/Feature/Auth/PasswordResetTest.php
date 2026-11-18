<?php

use App\Models\TokenRecuperacion;
use App\Models\User;
use App\Notifications\RecuperarContrasenaNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200)
        ->assertSee('Recupera tu cuenta')
        ->assertDontSee('Enlace temporal generado');
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email]);

    $response
        ->assertRedirect(route('password.request'))
        ->assertSessionHas('recuperacion_enviada', true)
        ->assertSessionMissing('reset_url');

    Notification::assertSentTo($user, RecuperarContrasenaNotification::class);
    expect(TokenRecuperacion::query()->where('id_user', $user->id_user)->exists())->toBeTrue();
});

test('solicitud de recuperacion confirma en la misma pantalla y no vuelve al login', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email]);

    $response->assertRedirect(route('password.request'));
    $this->followRedirects($response)
        ->assertOk()
        ->assertSee('Revisa tu correo')
        ->assertSee('enlace temporal')
        ->assertDontSee('Enviar enlace de recuperación')
        ->assertDontSee('Si el correo está registrado, te enviamos un enlace');

    $this->get('/login')
        ->assertOk()
        ->assertDontSee('Revisa tu correo')
        ->assertDontSee('Si el correo está registrado, te enviamos un enlace');
});

test('correo inexistente no revela si la cuenta existe y no envia notificacion', function () {
    Notification::fake();

    $response = $this->from('/forgot-password')->post('/forgot-password', [
        'email' => 'no.existe.'.uniqid().'@figuefit.test',
    ]);

    $response
        ->assertRedirect(route('password.request'))
        ->assertSessionHas('recuperacion_enviada', true);

    Notification::assertNothingSent();
    expect(TokenRecuperacion::query()->count())->toBe(0);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, RecuperarContrasenaNotification::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200)
            ->assertSee('class="lg-toggle-password"', false)
            ->assertSee('data-target="password"', false)
            ->assertSee('data-target="password_confirmation"', false);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, RecuperarContrasenaNotification::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $user->refresh();
        expect(Hash::check('password', $user->password_hash))->toBeTrue();
        expect(TokenRecuperacion::query()->where('id_user', $user->id_user)->whereNotNull('usado_en')->exists())->toBeTrue();

        return true;
    });
});

test('flujo completo de recuperacion permite iniciar sesion con la nueva clave', function () {
    Notification::fake();

    $user = User::factory()->create([
        'correo' => 'recupera.flow@test.local',
        'email' => 'recupera.flow@test.local',
    ]);
    $user->assignPassword('antiguaClave123');
    $user->save();

    $rol = \App\Models\Rol::query()->firstOrCreate(
        ['codigo' => 'CLIENTE'],
        ['codigo_rol' => 'CLIENTE', 'nombre' => 'Cliente', 'estado' => 'ACTIVO']
    );
    $user->roles()->syncWithoutDetaching([$rol->id_rol]);

    $this->post('/forgot-password', ['email' => 'recupera.flow@test.local'])
        ->assertRedirect(route('password.request'));

    $tokenPlain = null;
    Notification::assertSentTo($user, RecuperarContrasenaNotification::class, function ($notification) use (&$tokenPlain) {
        $tokenPlain = $notification->token;

        return true;
    });

    expect($tokenPlain)->not->toBeNull();

    $this->post('/reset-password', [
        'token' => $tokenPlain,
        'email' => 'recupera.flow@test.local',
        'password' => 'nuevaClaveSegura2026',
        'password_confirmation' => 'nuevaClaveSegura2026',
    ])->assertRedirect(route('login'));

    $loginResponse = $this->post('/login', [
        'email' => 'recupera.flow@test.local',
        'password' => 'nuevaClaveSegura2026',
    ]);

    $loginResponse->assertRedirect(route('cliente.dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
});

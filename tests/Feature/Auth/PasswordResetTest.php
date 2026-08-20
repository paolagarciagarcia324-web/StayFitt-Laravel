<?php

use App\Models\TokenRecuperacion;
use App\Models\User;
use App\Notifications\RecuperarContrasenaNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertRedirect(route('login'));

    Notification::assertSentTo($user, RecuperarContrasenaNotification::class);
    expect(TokenRecuperacion::query()->where('id_user', $user->id_user)->exists())->toBeTrue();
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, RecuperarContrasenaNotification::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

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
            ->assertRedirect(route('login'));

        $user->refresh();
        expect(Hash::check('password', $user->password_hash))->toBeTrue();
        expect(TokenRecuperacion::query()->where('id_user', $user->id_user)->whereNotNull('usado_en')->exists())->toBeTrue();

        return true;
    });
});

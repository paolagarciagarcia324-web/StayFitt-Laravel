<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $email = fake()->unique()->safeEmail();

        return [
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'correo' => $email,
            'email' => $email,
            'password_hash' => static::$password ??= Hash::make('password'),
            'estado' => 'ACTIVO',
            'estado_cuenta' => 'ACTIVO',
            'acepto_terminos' => true,
            'fecha_aceptacion_terminos' => now(),
            'correo_verificado_en' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (User $user) {
            if ($user->getAuthPassword() === '') {
                $user->assignPassword('password');
            }
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'correo_verificado_en' => null,
            'email_verificado_en' => null,
        ]);
    }
}

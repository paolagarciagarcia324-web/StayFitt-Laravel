<x-auth-layout title="Iniciar sesión | FigueFit">
    <h2>Iniciar sesión</h2>
    <p class="lg-subtitle">Ingresa tus credenciales para continuar en FigueFit.</p>

    @if (session('status'))
        <p class="lg-status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="lg-form">
        @csrf

        <label>
            <span>Correo electrónico</span>
            <div class="lg-input">
                <span class="lg-input-icon" aria-hidden="true">✉</span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nombre@correo.com" required autofocus autocomplete="username">
            </div>
            @error('email')
                <small class="lg-error">{{ $message }}</small>
            @enderror
        </label>

        <label>
            <div class="lg-label-row">
                <span>Contraseña</span>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                @endif
            </div>
            <div class="lg-input">
                <span class="lg-input-icon" aria-hidden="true">🔒</span>
                <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            </div>
            @error('password')
                <small class="lg-error">{{ $message }}</small>
            @enderror
        </label>

        <p class="lg-hint">Si te inscribiste antes de crear contraseña, usa tu número de identificación como contraseña.</p>

        <button type="submit" class="lg-submit">Entrar al sistema →</button>
    </form>

    <a class="lg-back" href="{{ route('welcome') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.4 5.3a1 1 0 0 1 .05 1.4L11.16 12l4.29 5.3a1 1 0 1 1-1.55 1.26l-4.8-5.93a1 1 0 0 1 0-1.26l4.8-5.93a1 1 0 0 1 1.5-.14Z"/></svg>
        Volver al inicio
    </a>
</x-auth-layout>

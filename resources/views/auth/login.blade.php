<x-auth-layout title="Iniciar sesión | StayFit">
    <h2>Iniciar sesión</h2>
    <p class="lg-subtitle">Ingresa tus credenciales para continuar en StayFit.</p>

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
            <div class="lg-input has-toggle">
                <span class="lg-input-icon" aria-hidden="true">🔒</span>
                <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                <button type="button" class="lg-toggle-password" data-target="password" aria-controls="password" aria-label="Mostrar contraseña" title="Mostrar contraseña" aria-pressed="false">
                    <svg class="lg-icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg class="lg-icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                        <line x1="1" y1="2" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <small class="lg-error">{{ $message }}</small>
            @enderror
        </label>

        <p class="lg-hint">Cuenta nueva por pago o alta de coach: la primera vez entra con tu documento.</p>

        <button type="submit" class="lg-submit">Entrar al sistema →</button>
    </form>

    <a class="lg-back" href="{{ route('welcome') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.4 5.3a1 1 0 0 1 .05 1.4L11.16 12l4.29 5.3a1 1 0 1 1-1.55 1.26l-4.8-5.93a1 1 0 0 1 0-1.26l4.8-5.93a1 1 0 0 1 1.5-.14Z"/></svg>
        Volver al inicio
    </a>
</x-auth-layout>

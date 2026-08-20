<x-auth-layout title="Recuperar contraseña | FigueFit">
    <p class="lg-kicker">Acceso FigueFit</p>
    <h2>Recupera tu cuenta</h2>
    <p class="lg-subtitle">Ingresa el correo de tu usuario. Te enviaremos un enlace seguro para definir una nueva contraseña y volver a entrenar.</p>

    @if (session('status'))
        <p class="lg-status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="lg-form">
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

        <p class="lg-hint">El enlace vence en 60 minutos. Si no pediste este cambio, puedes volver al inicio de sesión.</p>

        <button type="submit" class="lg-submit">Enviar enlace de recuperación →</button>
    </form>

    <a class="lg-back" href="{{ route('login') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.4 5.3a1 1 0 0 1 .05 1.4L11.16 12l4.29 5.3a1 1 0 1 1-1.55 1.26l-4.8-5.93a1 1 0 0 1 0-1.26l4.8-5.93a1 1 0 0 1 1.5-.14Z"/></svg>
        Volver al inicio de sesión
    </a>
</x-auth-layout>

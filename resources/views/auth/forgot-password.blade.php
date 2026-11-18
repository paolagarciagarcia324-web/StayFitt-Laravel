<x-auth-layout title="Recuperar contraseña | StayFit">
    @if (session('recuperacion_enviada'))
        <p class="lg-kicker">Acceso StayFit</p>
        <h2>Revisa tu correo</h2>
        <p class="lg-subtitle">Si ese correo está registrado en StayFit, te enviamos un enlace temporal para crear tu nueva contraseña.</p>

        <div class="lg-confirm">
            <p class="lg-status">El enlace llega a tu bandeja de entrada o spam y vence en 60 minutos. Cuando definas la clave, vuelve a iniciar sesión.</p>
            <ol class="lg-confirm-steps">
                <li>Solicitud de recuperación enviada</li>
                <li>Abre el enlace seguro del correo</li>
                <li>Crea tu nueva contraseña</li>
                <li>Ingresa de nuevo con esa clave</li>
            </ol>
        </div>
    @else
        <p class="lg-kicker">Acceso StayFit</p>
        <h2>Recupera tu cuenta</h2>
        <p class="lg-subtitle">Escribe el correo de tu usuario. Te enviaremos un enlace temporal para crear una contraseña nueva.</p>

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

            <p class="lg-hint">El enlace vence en 60 minutos. Si no pediste este cambio, ignora el correo y tu acceso no cambia.</p>

            <button type="submit" class="lg-submit">Enviar enlace de recuperación →</button>
        </form>
    @endif

    <a class="lg-back" href="{{ route('login') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.4 5.3a1 1 0 0 1 .05 1.4L11.16 12l4.29 5.3a1 1 0 1 1-1.55 1.26l-4.8-5.93a1 1 0 0 1 0-1.26l4.8-5.93a1 1 0 0 1 1.5-.14Z"/></svg>
        Volver al inicio de sesión
    </a>
</x-auth-layout>

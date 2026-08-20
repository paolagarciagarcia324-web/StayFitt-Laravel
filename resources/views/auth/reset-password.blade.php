<x-auth-layout title="Nueva contraseña | FigueFit">
    <p class="lg-kicker">Acceso FigueFit</p>
    <h2>Define tu nueva contraseña</h2>
    <p class="lg-subtitle">Elige una clave segura. Al guardarla, volverás al inicio de sesión para entrar con tu nuevo acceso.</p>

    <form method="POST" action="{{ route('password.store') }}" class="lg-form">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <label>
            <span>Correo electrónico</span>
            <div class="lg-input">
                <span class="lg-input-icon" aria-hidden="true">✉</span>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" placeholder="nombre@correo.com" required autofocus autocomplete="username">
            </div>
            @error('email')
                <small class="lg-error">{{ $message }}</small>
            @enderror
        </label>

        <label>
            <span>Nueva contraseña</span>
            <div class="lg-input">
                <span class="lg-input-icon" aria-hidden="true">🔒</span>
                <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="new-password">
            </div>
            @error('password')
                <small class="lg-error">{{ $message }}</small>
            @enderror
        </label>

        <label>
            <span>Confirmar contraseña</span>
            <div class="lg-input">
                <span class="lg-input-icon" aria-hidden="true">🔒</span>
                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
            </div>
        </label>

        <button type="submit" class="lg-submit">Guardar y volver al inicio →</button>
    </form>

    <a class="lg-back" href="{{ route('login') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.4 5.3a1 1 0 0 1 .05 1.4L11.16 12l4.29 5.3a1 1 0 1 1-1.55 1.26l-4.8-5.93a1 1 0 0 1 0-1.26l4.8-5.93a1 1 0 0 1 1.5-.14Z"/></svg>
        Volver al inicio de sesión
    </a>
</x-auth-layout>

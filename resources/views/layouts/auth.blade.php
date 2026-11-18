<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'StayFit' }}</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="lg-body">
    <main class="lg-shell">
        <section class="lg-visual">
            <img class="lg-visual-bg" src="{{ asset('images/welcome/login.png') }}" alt="">

            <div class="lg-visual-content">
                <span class="lg-logo" aria-label="FigueFit">
                    <img src="{{ asset('images/welcome/Logo.png') }}" alt="FigueFit">
                </span>

                <div class="lg-visual-copy">
                    <h1>Tu proceso fitness,<br><span>empieza aquí.</span></h1>
                    <p>Entrena con propósito. Vive tu mejor versión con planes fitness personalizados, seguimiento profesional y bienestar diseñado para ti.</p>
                </div>

                <div class="lg-secure">
                    <span class="lg-secure-icon" aria-hidden="true">✓</span>
                    <div>
                        <strong>Acceso seguro</strong>
                        <small>Ingreso protegido para administradores, coaches, clientes e instituciones.</small>
                    </div>
                </div>
            </div>
        </section>

        <section class="lg-form-panel">
            {{ $slot }}
        </section>
    </main>

    <script>
        (function () {
            function initPasswordToggles() {
                var buttons = document.querySelectorAll('.lg-toggle-password');
                buttons.forEach(function (button) {
                    button.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                    });

                    button.addEventListener('click', function () {
                        var targetId = button.getAttribute('data-target');
                        var container = button.closest('.lg-input');
                        var input = targetId ? document.getElementById(targetId) : (container ? container.querySelector('input') : null);
                        if (!input) return;

                        var wasActive = (document.activeElement === input);
                        var start = input.selectionStart;
                        var end = input.selectionEnd;

                        var isPassword = (input.type === 'password');
                        input.type = isPassword ? 'text' : 'password';
                        button.classList.toggle('is-visible', isPassword);
                        button.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
                        var newLabel = isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña';
                        button.setAttribute('aria-label', newLabel);
                        button.setAttribute('title', newLabel);

                        if (wasActive) {
                            input.focus();
                            if (start !== null && end !== null) {
                                try {
                                    input.setSelectionRange(start, end);
                                } catch (err) {}
                            }
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPasswordToggles);
            } else {
                initPasswordToggles();
            }
        })();
    </script>
</body>
</html>

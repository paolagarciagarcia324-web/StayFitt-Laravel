<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma Digital | FigueFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body class="ff-body">
    <header class="ff-header">
        <a href="#inicio" class="ff-logo" aria-label="FigueFit">
            <img src="{{ asset('images/welcome/Logo.png') }}" alt="FigueFit">
        </a>

        <nav class="ff-nav" aria-label="Principal">
            <a href="#inicio" data-nav>INICIO</a>
            <a href="#servicios" data-nav>SERVICIOS</a>
            <a href="#planes" data-nav>PLANES</a>
            <a href="#instituciones" data-nav>INSTITUCIONES</a>
            <a href="#contacto" data-nav>CONTACTO</a>
        </nav>

        <div class="ff-actions">
            <a class="ff-btn" href="{{ route('login') }}">INICIAR SESIÓN</a>
        </div>

        <button class="ff-menu-toggle" type="button" aria-label="Abrir menú" aria-expanded="false">☰</button>
    </header>

    <main>
        <section id="inicio" class="ff-hero" data-section>
            <div class="ff-slider" aria-hidden="true">
                <div class="ff-slide is-active" style="background-image: url('{{ asset('images/welcome/slider1.png') }}')"></div>
                <div class="ff-slide" style="background-image: url('{{ asset('images/welcome/slider2.png') }}')"></div>
                <div class="ff-slide" style="background-image: url('{{ asset('images/welcome/slider3.png') }}')"></div>
            </div>
            <div class="ff-hero-overlay"></div>
            <div class="ff-hero-copy">
                <p class="ff-kicker">Plataforma fitness</p>
                <h1><span class="cyan">Rompe Tus</span> <span class="pink">Límites</span></h1>
                <p>Todo en un solo lugar</p>
            </div>
        </section>

        <section id="nosotros" class="ff-section" data-section>
            <article class="ff-card">
                <img src="{{ asset('images/welcome/Nosotras.jpg') }}" alt="Comunidad FigueFit">
                <div>
                    <div class="ff-meta">
                        <span class="ff-num">01</span>
                        <span class="ff-badge">LA MEJOR EXPERIENCIA</span>
                    </div>
                    <h2>NOSOTROS</h2>
                    <p class="ff-lead">FigueFit es una plataforma fitness enfocada en entrenamiento, nutrición, progreso físico y bienestar.</p>
                    <p class="ff-body">Nuestro propósito es acompañar principalmente a mujeres en su proceso de transformación física y personal, ofreciendo planes, programas virtuales, sesiones, eventos y seguimiento organizado.</p>
                    <a class="ff-cta" href="#servicios">CONOCER SERVICIOS →</a>
                </div>
            </article>
        </section>

        <section id="servicios" class="ff-section" data-section>
            <div class="ff-center">
                <div class="ff-meta">
                    <span class="ff-num">02</span>
                    <span class="ff-badge">TU PROCESO COMPLETO</span>
                </div>
                <h2>SERVICIOS</h2>
                <p class="ff-body">Todo lo que necesitas para avanzar en tu proceso fitness.</p>
                <div class="ff-service-grid">
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'entrenamiento'])</div>
                        <h3>ENTRENAMIENTO</h3>
                        <p>Rutinas, ejercicios y seguimiento con tu coach.</p>
                    </article>
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'nutricion'])</div>
                        <h3>NUTRICIÓN</h3>
                        <p>Planes alimentarios y acompañamiento en hábitos.</p>
                    </article>
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'video'])</div>
                        <h3>CONTENIDO VIRTUAL</h3>
                        <p>Videos y programas con desbloqueo progresivo.</p>
                    </article>
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'progreso'])</div>
                        <h3>PROGRESO</h3>
                        <p>Fotos, medidas, metas y evolución visible.</p>
                    </article>
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'agenda'])</div>
                        <h3>AGENDA</h3>
                        <p>Sesiones, recordatorios y reservas presenciales.</p>
                    </article>
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'comunidad'])</div>
                        <h3>COMUNIDAD</h3>
                        <p>Chat, eventos y acompañamiento constante.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="planes" class="ff-section" data-section>
            <div class="ff-center">
                <h2>PLANES Y PROGRAMAS</h2>
                <p class="ff-body">{{ $planes->count() }} planes activos disponibles. Elige el tuyo y empieza tu proceso con FigueFit.</p>
                <div class="ff-plan-grid">
                    @forelse ($planes as $plan)
                        <article class="ff-plan-card {{ $plan->popular ? 'is-popular' : '' }}">
                            @if ($plan->etiqueta)
                                <span class="ff-plan-tag">{{ $plan->etiqueta }}</span>
                            @endif
                            <p class="ff-plan-modalidad">{{ $plan->modalidad }} · {{ $plan->duracion_dias }} días</p>
                            <h3>{{ $plan->nombre }}</h3>
                            <p class="ff-plan-price">{{ $plan->precioFormateado() }}</p>
                            <p class="ff-plan-desc">{{ $plan->descripcion }}</p>
                            <ul class="ff-plan-features">
                                @foreach ($plan->caracteristicas_json ?? [] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                            @if ($plan->modulos->isNotEmpty())
                                <div class="ff-plan-modules">
                                    @foreach ($plan->modulos as $modulo)
                                        <span>{{ $modulo->nombre }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <a class="ff-cta ff-plan-btn" href="{{ route('pre-registro.create', $plan) }}">COMPRAR O OBTENER →</a>
                        </article>
                    @empty
                        <p class="ff-body">No hay planes publicados en este momento.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="instituciones" class="ff-section" data-section>
            <div class="ff-center">
                <div class="ff-meta">
                    <span class="ff-num">04</span>
                    <span class="ff-badge">CONVENIOS</span>
                </div>
                <h2>INSTITUCIONES</h2>
                <p class="ff-lead">Llevamos FigueFit a empresas, universidades y comunidades.</p>
                <p class="ff-body">Planes grupales, sesiones institucionales y seguimiento organizado para que más personas entrenen juntas.</p>
                <a class="ff-cta" href="#contacto">HABLAR CON NOSOTROS →</a>
            </div>
        </section>

        <section id="contacto" class="ff-section ff-contact" data-section>
            <div class="ff-contact-wrap">
                <div class="ff-contact-intro">
                    <div class="ff-meta">
                        <span class="ff-num">05</span>
                        <span class="ff-badge">ESTAMOS LISTOS</span>
                    </div>
                    <h2>CONTACTO</h2>
                    <p class="ff-lead">Entrena. Pregunta. Avanza.</p>
                    <p class="ff-body">Cuéntanos qué necesitas y te acompañamos en el siguiente paso de tu proceso fitness.</p>

                    <div class="ff-contact-channels">
                        <a class="ff-channel" href="mailto:hola@figuefit.com">
                            <span class="ff-channel-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/></svg>
                            </span>
                            <span>
                                <strong>Correo</strong>
                                <small>hola@figuefit.com</small>
                            </span>
                        </a>
                        <a class="ff-channel" href="https://wa.me/573000000000" target="_blank" rel="noopener">
                            <span class="ff-channel-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 8h10M7 12h6"/><path d="M5 19V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H9l-4 4Z"/></svg>
                            </span>
                            <span>
                                <strong>WhatsApp</strong>
                                <small>+57 300 000 0000</small>
                            </span>
                        </a>
                        <div class="ff-channel">
                            <span class="ff-channel-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M12 8v4l3 2"/></svg>
                            </span>
                            <span>
                                <strong>Horario</strong>
                                <small>Lun a Sáb · 6:00 a.m. – 8:00 p.m.</small>
                            </span>
                        </div>
                    </div>
                </div>

                <form class="ff-contact-form" action="mailto:hola@figuefit.com" method="post" enctype="text/plain">
                    <p class="ff-contact-form-label">ESCRÍBENOS</p>
                    <div class="ff-form-row">
                        <input type="text" name="nombre" placeholder="Nombre" required>
                        <input type="email" name="correo" placeholder="Correo" required>
                    </div>
                    <textarea name="mensaje" placeholder="Mensaje" required></textarea>
                    <button class="ff-cta ff-contact-submit" type="submit">ENVIAR MENSAJE →</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="ff-footer">
        <div class="ff-footer-inner">
            <div class="ff-footer-brand">
                <a href="#inicio" class="ff-logo" aria-label="FigueFit">
                    <img src="{{ asset('images/welcome/Logo.png') }}" alt="FigueFit">
                </a>
                <p>Plataforma fitness para entrenamiento, nutrición, progreso y bienestar. Virtual, presencial e institucional.</p>
            </div>

            <div class="ff-footer-col">
                <h4>Explorar</h4>
                <a href="#nosotros">Nosotros</a>
                <a href="#servicios">Servicios</a>
                <a href="#planes">Planes</a>
                <a href="#instituciones">Instituciones</a>
            </div>

            <div class="ff-footer-col">
                <h4>Acceso</h4>
                <a href="{{ route('login') }}">Iniciar sesión</a>
                <a href="#planes">Comprar o obtener plan</a>
                <a href="#contacto">Contacto</a>
            </div>

            <div class="ff-footer-col">
                <h4>Contacto</h4>
                <a href="mailto:hola@figuefit.com">hola@figuefit.com</a>
                <a href="https://wa.me/573000000000" target="_blank" rel="noopener">WhatsApp</a>
                <span>Colombia</span>
            </div>
        </div>
        <div class="ff-footer-bottom">
            <p>© {{ date('Y') }} FigueFit. Todos los derechos reservados.</p>
            <p>Rompe tus límites. Todo en un solo lugar.</p>
        </div>
    </footer>

    <nav class="ff-dock" aria-label="Secciones">
        <a href="#nosotros" data-nav><small>01</small>NOSOTROS</a>
        <a href="#servicios" data-nav><small>02</small>SERVICIOS</a>
        <a href="#planes" data-nav><small>03</small>PLANES</a>
        <a href="#instituciones" data-nav><small>04</small>INSTITUCIONES</a>
        <a href="#contacto" data-nav><small>05</small>CONTACTO</a>
    </nav>

    <script src="{{ asset('js/welcome.js') }}"></script>
</body>
</html>

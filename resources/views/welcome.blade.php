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
        <span class="ff-logo" aria-label="FigueFit">
            <img src="{{ asset('images/welcome/Logo.png') }}" alt="FigueFit">
        </span>

        <div class="ff-actions">
            @if (($novedades ?? collect())->isNotEmpty())
                <button type="button" class="ff-news-trigger" data-news-open aria-haspopup="dialog" aria-controls="ff-news-modal" aria-expanded="false">
                    <span class="ff-news-trigger-pulse" aria-hidden="true"></span>
                    <span class="ff-news-trigger-label">Novedades</span>
                    <span class="ff-news-trigger-badge">{{ $novedades->count() }}</span>
                </button>
            @endif
            @include('partials.ff-cta', ['href' => route('login'), 'label' => 'INICIAR SESIÓN', 'icon' => 'login', 'class' => 'ff-btn ff-btn-quiet'])
        </div>
    </header>

    @if (($novedades ?? collect())->isNotEmpty())
        <div class="ff-news-modal is-hidden" id="ff-news-modal" data-news-modal role="dialog" aria-modal="true" aria-labelledby="ff-news-title" aria-hidden="true">
            <div class="ff-news-dialog" role="document">
                <button type="button" class="ff-news-close" data-news-close aria-label="Cerrar novedades">
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.11L10.59 12 5.7 16.89a1 1 0 1 0 1.41 1.41L12 13.41l4.89 4.89a1 1 0 0 0 1.41-1.41L13.41 12l4.89-4.89a1 1 0 0 0 0-1.4Z"/></svg>
                </button>

                <header class="ff-news-dialog-head">
                    <p class="ff-news-kicker">Lo nuevo en FigueFit</p>
                    <h2 id="ff-news-title">Novedades</h2>
                    <p class="ff-news-lead">Promos, planes y actualizaciones de la plataforma.</p>
                </header>

                <div class="ff-news-grid">
                    @foreach ($novedades as $novedad)
                        <article class="ff-news-card">
                            <div class="ff-news-card-media" style="background-image: url('{{ $novedad->imagenSrc() }}')"></div>
                            <div class="ff-news-card-body">
                                <span class="ff-news-type">{{ $novedad->etiquetaTipo() }}</span>
                                <h3>{{ $novedad->titulo }}</h3>
                                @if ($novedad->subtitulo)
                                    <p class="ff-news-sub">{{ $novedad->subtitulo }}</p>
                                @endif
                                @if ($novedad->descripcion)
                                    <p class="ff-news-desc">{{ $novedad->descripcion }}</p>
                                @endif
                                <a class="ff-news-cta" href="{{ $novedad->destinoUrl() }}">{{ $novedad->boton_texto }}</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

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
                        <span class="ff-num">02</span>
                        <span class="ff-badge">LA MEJOR EXPERIENCIA</span>
                    </div>
                    <h2>NOSOTROS</h2>
                    <p class="ff-lead">FigueFit es una plataforma fitness enfocada en entrenamiento, nutrición, progreso físico y bienestar.</p>
                    <p class="ff-body">Nuestro propósito es acompañar principalmente a mujeres en su proceso de transformación física y personal, ofreciendo planes, programas virtuales, sesiones, eventos y seguimiento organizado.</p>
                    @include('partials.ff-cta', ['href' => '#servicios', 'label' => 'CONOCER SERVICIOS', 'icon' => 'servicios'])
                </div>
            </article>
        </section>

        <section id="servicios" class="ff-section" data-section>
            <div class="ff-card ff-catalog">
                <div class="ff-meta">
                    <span class="ff-num">03</span>
                    <span class="ff-badge">TU PROCESO COMPLETO</span>
                </div>
                <h2>SERVICIOS</h2>
                <p class="ff-lead">Todo lo que necesitas para avanzar en tu proceso fitness.</p>

                <div class="ff-service-grid">
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'entrenamiento'])</div>
                        <h3>ENTRENAMIENTO</h3>
                    </article>
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'nutricion'])</div>
                        <h3>NUTRICIÓN</h3>
                    </article>
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'video'])</div>
                        <h3>CONTENIDO VIRTUAL</h3>
                    </article>
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'progreso'])</div>
                        <h3>PROGRESO</h3>
                    </article>
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'agenda'])</div>
                        <h3>AGENDA</h3>
                    </article>
                    <article class="ff-service-card">
                        <div class="ff-service-icon">@include('partials.welcome.service-icon', ['icon' => 'comunidad'])</div>
                        <h3>COMUNIDAD</h3>
                    </article>
                </div>

                @include('partials.ff-cta', ['href' => '#planes', 'label' => 'VER PLANES', 'icon' => 'planes'])
            </div>
        </section>

        <section id="planes" class="ff-section" data-section>
            <div class="ff-card ff-catalog">
                <div class="ff-meta">
                    <span class="ff-num">04</span>
                    <span class="ff-badge">SOLO VIRTUAL</span>
                </div>
                <h2>PLANES</h2>
                <p class="ff-lead">Entrena desde donde estés. Elige tu plan virtual y cómpralo aquí.</p>
                <p class="ff-body">Publicados por Admin o Coach para personas individuales o instituciones. La modalidad presencial no aplica en este catálogo.</p>

                <div class="ff-plan-grid">
                    @forelse ($planes as $plan)
                        <article class="ff-plan-card {{ $plan->popular ? 'is-popular' : '' }}">
                            <div class="ff-plan-top">
                                <p class="ff-plan-modalidad">Virtual · {{ $plan->tipo_cliente }} · {{ $plan->duracion_dias }} días</p>
                                @if ($plan->etiqueta)
                                    <span class="ff-plan-tag">{{ $plan->etiqueta }}</span>
                                @endif
                            </div>
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
                            @include('partials.ff-cta', ['href' => route('pre-registro.create', $plan), 'label' => 'COMPRAR', 'icon' => 'comprar', 'class' => 'ff-plan-btn'])
                        </article>
                    @empty
                        <p class="ff-body">Aún no hay planes virtuales publicados.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="instituciones" class="ff-section" data-section>
            <article class="ff-card">
                <img src="{{ asset('images/welcome/instituciones.jpg') }}" alt="Equipos entrenando con FigueFit">
                <div>
                    <div class="ff-meta">
                        <span class="ff-num">05</span>
                        <span class="ff-badge">BIENESTAR CORPORATIVO</span>
                    </div>
                    <h2>INSTITUCIONES</h2>
                    <p class="ff-lead">FigueFit para empresas, universidades y comunidades.</p>
                    <p class="ff-body">Un plan grupal, un enlace de alta y un tablero de avance. RR. HH. o bienestar institucional ve el proceso; el equipo entrena junto.</p>
                    <ul class="ff-plan-features">
                        <li>Plan virtual institucional</li>
                        <li>Alta con enlace y cupos</li>
                        <li>Sesiones grupales y reportes</li>
                    </ul>
                    @include('partials.ff-cta', ['href' => 'https://wa.me/573000000000', 'label' => 'HABLAR CON NOSOTROS', 'icon' => 'whatsapp', 'class' => 'is-cyan', 'attrs' => ['target' => '_blank', 'rel' => 'noopener']])
                </div>
            </article>
        </section>

        <section id="registro" class="ff-section" data-section>
            <article class="ff-card">
                <img src="{{ asset('images/welcome/registro.jpg') }}" alt="Gimnasio funcional FigueFit en Campoalegre">
                <div>
                    <div class="ff-meta">
                        <span class="ff-num">06</span>
                        <span class="ff-badge">PRESENCIAL · CAMPOALEGRE</span>
                    </div>
                    <h2>REGISTRO</h2>
                    <p class="ff-lead">Entrena en sala. Paga aquí. Entra el mismo día.</p>
                    <p class="ff-body">Inscripción presencial en FigueFit, Campoalegre, Huila. Completas tu ficha, pagas con pasarela y el sistema te activa como cliente.</p>
                    <a
                        class="ff-sede"
                        href="https://www.google.com/maps/search/?api=1&query=FigueFit+Campoalegre+Huila"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Ver ubicación de FigueFit en Google Maps"
                    >
                        <span class="ff-sede-pin" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11Z"/>
                                <circle cx="12" cy="10" r="2.4"/>
                            </svg>
                        </span>
                        <div>
                            <strong>Sede principal</strong>
                            <span>Campoalegre, Huila</span>
                        </div>
                    </a>
                    @include('partials.ff-cta', ['href' => route('registro-presencial.create'), 'label' => 'REGISTRARME', 'icon' => 'registro'])
                </div>
            </article>
        </section>
    </main>

    <footer class="ff-footer">
        <div class="ff-footer-inner">
            <div class="ff-footer-brand">
                <span class="ff-logo" aria-label="FigueFit">
                    <img src="{{ asset('images/welcome/Logo.png') }}" alt="FigueFit">
                </span>
                <p>Plataforma fitness para entrenamiento, nutrición, progreso y bienestar. Virtual, presencial e institucional.</p>
            </div>

            <div class="ff-footer-col">
                <h4>Explorar</h4>
                <a href="#inicio">Inicio</a>
                <a href="#nosotros">Nosotros</a>
                <a href="#servicios">Servicios</a>
                <a href="#planes">Planes</a>
                <a href="#instituciones">Instituciones</a>
                <a href="#registro">Registro</a>
            </div>

            <div class="ff-footer-col">
                <h4>Acceso</h4>
                <a href="{{ route('login') }}">Iniciar sesión</a>
                <a href="#planes">Comprar plan virtual</a>
                <a href="#registro">Registro presencial</a>
            </div>

            <div class="ff-footer-col">
                <h4>Contáctanos</h4>
                <a href="mailto:hola@figuefit.com">hola@figuefit.com</a>
                <a href="https://wa.me/573000000000" target="_blank" rel="noopener">WhatsApp</a>
                <a
                    href="https://www.google.com/maps/search/?api=1&query=FigueFit+Campoalegre+Huila"
                    target="_blank"
                    rel="noopener noreferrer"
                >Campoalegre, Huila</a>
                <div class="ff-social" aria-label="Redes sociales">
                    <a href="https://www.instagram.com/figuefit" target="_blank" rel="noopener" aria-label="Instagram FigueFit">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor"/></svg>
                    </a>
                    <a href="https://www.facebook.com/figuefit" target="_blank" rel="noopener" aria-label="Facebook FigueFit">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M14.5 9.5V8.2c0-.7.5-1.2 1.2-1.2H17V4h-2.1C12.5 4 11 5.5 11 7.8v1.7H9v3h2V20h3.5v-7.5h2.3l.5-3H14.5Z"/></svg>
                    </a>
                    <a href="https://www.youtube.com/@figuefit" target="_blank" rel="noopener" aria-label="YouTube FigueFit">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21.6 8.2a2.5 2.5 0 0 0-1.8-1.8C18.2 6 12 6 12 6s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 8.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 3.8 2.5 2.5 0 0 0 1.8 1.8C5.8 18 12 18 12 18s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-3.8ZM10 14.8V9.2L15.2 12 10 14.8Z"/></svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="ff-footer-bottom">
            <p>© {{ date('Y') }} FigueFit. Todos los derechos reservados.</p>
            <p>Rompe tus límites. Todo en un solo lugar.</p>
        </div>
    </footer>

    <nav class="ff-dock" aria-label="Secciones">
        <a href="#inicio" data-nav><small>01</small>INICIO</a>
        <a href="#nosotros" data-nav><small>02</small>NOSOTROS</a>
        <a href="#servicios" data-nav><small>03</small>SERVICIOS</a>
        <a href="#planes" data-nav><small>04</small>PLANES</a>
        <a href="#instituciones" data-nav><small>05</small>INSTITUCIONES</a>
        <a href="#registro" data-nav><small>06</small>REGISTRO</a>
    </nav>

    <a
        class="ff-wa"
        href="https://wa.me/573000000000"
        target="_blank"
        rel="noopener"
        aria-label="Escribir por WhatsApp"
    >
        <svg viewBox="0 0 32 32" aria-hidden="true">
            <path fill="currentColor" d="M16.04 3C9.4 3 4 8.36 4 14.94c0 2.1.55 4.14 1.6 5.95L4 29l8.34-1.58A12.1 12.1 0 0 0 16.04 27C22.68 27 28 21.64 28 15.06 28 8.36 22.68 3 16.04 3Zm6.98 16.7c-.29.82-1.7 1.5-2.37 1.6-.61.09-1.38.13-2.23-.14-.51-.16-1.17-.38-2.02-.74-3.55-1.53-5.87-5.12-6.05-5.36-.18-.24-1.47-1.95-1.47-3.72s.93-2.64 1.26-3c.33-.36.72-.45.96-.45h.7c.22 0 .52-.08.81.62.29.72.99 2.48 1.08 2.66.09.18.15.4.03.64-.12.24-.18.4-.36.61-.18.22-.38.49-.54.66-.18.18-.36.37-.16.72.2.36.9 1.48 1.93 2.4 1.33 1.18 2.45 1.55 2.8 1.72.36.18.56.15.77-.09.2-.24.88-1.03 1.11-1.38.24-.36.47-.3.79-.18.33.12 2.07.98 2.42 1.15.36.18.6.27.69.42.09.15.09.86-.2 1.68Z"/>
        </svg>
    </a>

    <script src="{{ asset('js/welcome.js') }}"></script>
</body>
</html>

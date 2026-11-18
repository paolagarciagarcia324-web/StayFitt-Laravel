<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | StayFit</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-ds.css') }}?v={{ @filemtime(public_path('css/admin-ds.css')) ?: time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-fx.css') }}?v={{ @filemtime(public_path('css/admin-fx.css')) ?: time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-sol.css') }}?v={{ @filemtime(public_path('css/admin-sol.css')) ?: time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-safe.css') }}?v={{ @filemtime(public_path('css/admin-safe.css')) ?: time() }}">
    <style>
        /* Shell visible; las animaciones de gráficas viven en admin-fx */
        .ad-sidebar, .ad-wrap, .ad-header, .ad-main, .ad-content, .ad-page, .ad-top {
            opacity: 1 !important;
            visibility: visible !important;
        }
    </style>
    <script>
        (function () {
            try {
                if (localStorage.getItem('ff-admin-theme') === 'light') {
                    document.documentElement.classList.add('ad-theme-light');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="ad-body">
    <input class="ad-nav-check" type="checkbox" id="ad-nav-toggle" checked>

    <aside class="ad-sidebar">
        <div class="ad-brand">
            <div class="ad-brand-mark">
                <img src="{{ asset('images/admin/figuefit-logo-circle.png') }}" alt="FigueFit">
            </div>
            <div class="ad-brand-copy">
                <strong>StayFit</strong>
                <small>Admin</small>
            </div>
        </div>

        <nav class="ad-nav ad-nav-flat" data-admin-nav>
            @php
                $pendLatest = $navSolicitudesLatestAt ?? null;
                $pend = (int) ($navSolicitudesPendientes ?? 0);
            @endphp
            @foreach (($modules ?? []) as $key => $mod)
                <a href="{{ $mod['href'] }}" class="ad-nav-link {{ ! empty($mod['active']) ? 'is-active' : '' }}">
                    <span class="ad-nav-orb is-{{ $mod['tone'] }}">@include('partials.admin-icon', ['icon' => $mod['icon']])</span>
                    {{ $mod['label'] }}
                    @if ($key === 'operacion')
                        <span
                            class="ad-nav-badge"
                            data-seen-badge="solicitudes"
                            data-latest="{{ $pendLatest }}"
                            data-count="{{ $pend }}"
                            @if ($pend < 1) hidden @endif
                        >{{ $pend }}</span>
                    @elseif (! empty($mod['badge']))
                        <span class="ad-nav-badge">{{ $mod['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="ad-dock">
            <article class="ad-dock-card">
                <span>Solicitudes</span>
                <strong data-count="{{ $pend }}">{{ $pend }}</strong>
                <em>en cola humana</em>
            </article>
            <article class="ad-dock-card">
                <span>Ocupación sala</span>
                <strong data-count="{{ (int) ($navOcupacionPct ?? 0) }}" data-suffix="%">{{ (int) ($navOcupacionPct ?? 0) }}%</strong>
                <div class="ad-dock-bar"><i style="--w: {{ (int) ($navOcupacionPct ?? 0) }}%"></i></div>
            </article>
        </div>

        <div class="ad-sidebar-foot">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <div class="ad-wrap">
        <header class="ad-header">
            <label class="ad-nav-burger" for="ad-nav-toggle" aria-label="Menú">
                <span></span>
                <span></span>
                <span></span>
            </label>
            <div class="ad-header-actions">
                @include('partials.admin-header-tools')
            </div>
        </header>

        <div class="ad-main">
            <header class="ad-top">
                <div>
                    <h1>@yield('title', 'Dashboard')</h1>
                    @hasSection('subtitle')
                        <p>@yield('subtitle')</p>
                    @endif
                </div>
                <div class="ad-top-actions">
                    @yield('actions')
                </div>
            </header>

            @if (! empty($currentTabs ?? []))
                <nav class="ad-mod-tabs" aria-label="Secciones del módulo">
                    @foreach ($currentTabs as $tab)
                        <a href="{{ $tab['href'] }}" class="ad-mod-tab {{ ! empty($tab['active']) ? 'is-active' : '' }}">
                            {{ $tab['label'] }}
                            @if (! empty($tab['badge']))
                                <span class="ad-mod-tab-badge">{{ $tab['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            @endif

            @hasSection('flash')
                @yield('flash')
            @else
                @if (session('success'))
                    <div class="ad-alert">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="ad-alert ad-alert-error">{{ session('error') }}</div>
                @endif

                @if (isset($errors) && $errors->any())
                    <div class="ad-alert ad-alert-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif

            <main class="ad-content">
                <div class="ad-page ad-page--wide">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <script>
        (function () {
            var root = document.documentElement;
            var tools = document.querySelector('[data-tools]');
            var moon = document.querySelector('[data-theme-icon="moon"]');
            var sun = document.querySelector('[data-theme-icon="sun"]');

            function syncThemeIcons() {
                var light = root.classList.contains('ad-theme-light');
                if (moon) moon.hidden = light;
                if (sun) sun.hidden = !light;
            }

            syncThemeIcons();

            document.querySelector('[data-theme-toggle]')?.addEventListener('click', function () {
                var light = root.classList.toggle('ad-theme-light');
                try { localStorage.setItem('ff-admin-theme', light ? 'light' : 'dark'); } catch (e) {}
                syncThemeIcons();
            });

            tools?.addEventListener('click', function (event) {
                var btn = event.target.closest('[data-panel]');
                if (!btn) return;
                var id = btn.getAttribute('data-panel');
                tools.querySelectorAll('[data-box]').forEach(function (box) {
                    var open = box.getAttribute('data-box') === id && box.hasAttribute('hidden');
                    box.toggleAttribute('hidden', !open);
                });
                if (id === 'ventas' || id === 'alertas') {
                    markHeaderSeen(id);
                }
            });

            document.addEventListener('click', function (event) {
                if (!tools || tools.contains(event.target)) return;
                tools.querySelectorAll('[data-box]').forEach(function (box) {
                    box.setAttribute('hidden', '');
                });
            });

            // Nav accordion: persist open groups; route-active always wins
            (function () {
                var nav = document.querySelector('[data-admin-nav]');
                if (!nav) return;
                var KEY = 'ff-admin-nav';
                var stored = {};
                try { stored = JSON.parse(localStorage.getItem(KEY) || '{}') || {}; } catch (e) { stored = {}; }

                nav.querySelectorAll('[data-nav-group]').forEach(function (el) {
                    var id = el.getAttribute('data-nav-group');
                    if (el.classList.contains('is-route-active')) {
                        el.open = true;
                        return;
                    }
                    if (Object.prototype.hasOwnProperty.call(stored, id)) {
                        el.open = !!stored[id];
                    }
                });

                nav.addEventListener('toggle', function (event) {
                    var el = event.target;
                    if (!el || !el.getAttribute || !el.getAttribute('data-nav-group')) return;
                    stored[el.getAttribute('data-nav-group')] = el.open;
                    try { localStorage.setItem(KEY, JSON.stringify(stored)); } catch (e) {}
                }, true);
            })();

            // Badges: hide when viewed / until newer items appear
            var SEEN_KEY = 'ff-admin-seen';
            function readSeen() {
                try { return JSON.parse(localStorage.getItem(SEEN_KEY) || '{}') || {}; } catch (e) { return {}; }
            }
            function writeSeen(data) {
                try { localStorage.setItem(SEEN_KEY, JSON.stringify(data)); } catch (e) {}
            }
            function syncSolicitudesBadges() {
                var seen = readSeen();
                var seenAt = seen.solicitudesSeenAt || null;
                document.querySelectorAll('[data-seen-badge="solicitudes"]').forEach(function (el) {
                    var count = parseInt(el.getAttribute('data-count') || '0', 10);
                    var latest = el.getAttribute('data-latest') || '';
                    var show = count > 0 && (!seenAt || (latest && latest > seenAt));
                    el.toggleAttribute('hidden', !show);
                    if (show) el.textContent = String(count);
                });
            }
            function markSolicitudesSeen() {
                var seen = readSeen();
                var latest = '';
                document.querySelectorAll('[data-seen-badge="solicitudes"]').forEach(function (el) {
                    var v = el.getAttribute('data-latest') || '';
                    if (v && v > latest) latest = v;
                });
                seen.solicitudesSeenAt = latest || new Date().toISOString();
                writeSeen(seen);
                syncSolicitudesBadges();
            }
            function syncHeaderDots() {
                var seen = readSeen();
                document.querySelectorAll('[data-seen-dot]').forEach(function (el) {
                    var key = el.getAttribute('data-seen-dot');
                    var count = parseInt(el.getAttribute('data-count') || '0', 10);
                    var seenCount = parseInt(seen[key + 'SeenCount'] || '-1', 10);
                    var show = count > 0 && count !== seenCount;
                    el.toggleAttribute('hidden', !show);
                });
            }
            function markHeaderSeen(key) {
                var seen = readSeen();
                var el = document.querySelector('[data-seen-dot="' + key + '"]');
                var count = el ? parseInt(el.getAttribute('data-count') || '0', 10) : 0;
                seen[key + 'SeenCount'] = count;
                writeSeen(seen);
                syncHeaderDots();
            }

            document.querySelectorAll('.ad-stagger').forEach(function (box) {
                Array.prototype.forEach.call(box.children, function (child, i) {
                    child.style.setProperty('--d', (80 + i * 90) + 'ms');
                    child.classList.add('ad-rise');
                });
            });

            document.querySelectorAll('[data-count]').forEach(function (el) {
                var target = parseFloat(el.getAttribute('data-count'));
                if (isNaN(target)) return;
                var prefix = el.getAttribute('data-prefix') || '';
                var suffix = el.getAttribute('data-suffix') || '';
                var start = performance.now();
                function tick(now) {
                    var t = Math.min(1, (now - start) / 1100);
                    var eased = 1 - Math.pow(1 - t, 3);
                    el.textContent = prefix + Math.round(target * eased).toLocaleString('es-CO') + suffix;
                    if (t < 1) requestAnimationFrame(tick);
                }
                requestAnimationFrame(tick);
            });

            document.querySelectorAll('[data-carousel]').forEach(function (wrap) {
                var scroller = wrap.querySelector('.ad-catalog');
                if (!scroller) return;
                wrap.querySelectorAll('[data-carousel-dir]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var dir = btn.getAttribute('data-carousel-dir') === 'prev' ? -1 : 1;
                        scroller.scrollBy({ left: dir * (scroller.clientWidth * 0.7), behavior: 'smooth' });
                    });
                });
            });

            document.querySelectorAll('[data-cal-switch]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var view = btn.getAttribute('data-cal-switch');
                    document.querySelectorAll('[data-cal-switch]').forEach(function (b) { b.classList.toggle('is-on', b === btn); });
                    document.querySelectorAll('[data-cal-view]').forEach(function (panel) {
                        panel.toggleAttribute('hidden', panel.getAttribute('data-cal-view') !== view);
                    });
                });
            });

            syncSolicitudesBadges();
            syncHeaderDots();
            @if (!empty($onSolicitudes) && $onSolicitudes)
            markSolicitudesSeen();
            @endif

            document.querySelectorAll('[data-admin-search]').forEach(function (form) {
                var input = form.querySelector('[data-admin-search-input]');
                var clear = form.querySelector('[data-admin-search-clear]');
                if (!input || !clear) return;
                var live = form.hasAttribute('data-admin-search-live');
                var lastSubmitted = input.value;
                var timer = null;
                var board = form.closest('.ad-cli-board, .ad-users, .ad-inst-board, .ad-pay-detail, [data-pay-board]') || form.parentElement;
                var surface = board ? board.querySelector('.ad-cli-board-surface, .ad-inst-grid, .ad-pay-cards, table.ad-table') : null;

                function syncClear() {
                    var has = !!(input.value && String(input.value).trim());
                    clear.hidden = !has;
                }

                function normalize(text) {
                    return String(text || '')
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .trim();
                }

                function filterLocalRows(query) {
                    if (!board) return;
                    var q = normalize(query);
                    var cards = board.querySelectorAll('[data-inst-card]');
                    if (cards.length) {
                        var estadoBtn = board.querySelector('[data-inst-filter].is-active');
                        var estadoKey = estadoBtn ? estadoBtn.getAttribute('data-inst-filter') : 'ALL';
                        var visible = 0;
                        cards.forEach(function (card) {
                            var estadoOk = estadoKey === 'ALL' || card.getAttribute('data-estado') === estadoKey;
                            var hay = normalize(card.getAttribute('data-search') || card.textContent || '');
                            var textOk = !q || hay.indexOf(q) !== -1;
                            var show = estadoOk && textOk;
                            card.hidden = !show;
                            if (show) visible++;
                        });
                        var emptyFilter = board.querySelector('[data-inst-filter-empty]');
                        if (emptyFilter) emptyFilter.hidden = visible > 0 || cards.length === 0;
                        if (surface) surface.classList.toggle('is-filtering', !!q);
                        return;
                    }
                    var payCards = board.querySelectorAll('[data-pay-card]');
                    if (payCards.length) {
                        var visiblePay = 0;
                        payCards.forEach(function (card) {
                            var hay = normalize(card.getAttribute('data-search') || card.textContent || '');
                            var show = !q || hay.indexOf(q) !== -1;
                            card.hidden = !show;
                            if (show) visiblePay++;
                        });
                        var emptyPay = board.querySelector('[data-pay-search-empty]');
                        if (emptyPay) emptyPay.hidden = !(q && visiblePay === 0);
                        if (surface) surface.classList.toggle('is-filtering', !!q);
                        return;
                    }
                    var rows = board.querySelectorAll('table.ad-table tbody tr');
                    if (!rows.length) return;
                    var visibleRows = 0;
                    rows.forEach(function (row) {
                        if (row.querySelector('td.ad-empty, .ad-empty')) {
                            row.hidden = !!q;
                            return;
                        }
                        var hay = normalize(row.getAttribute('data-search') || row.textContent || '');
                        var show = !q || hay.indexOf(q) !== -1;
                        row.hidden = !show;
                        if (show) visibleRows++;
                    });
                    var emptyNote = board.querySelector('[data-live-search-empty]');
                    if (emptyNote) emptyNote.hidden = !(q && visibleRows === 0 && rows.length);
                    if (surface) surface.classList.toggle('is-filtering', !!q);
                }

                function submitSearch() {
                    if (input.value === lastSubmitted) return;
                    lastSubmitted = input.value;
                    if (typeof form.requestSubmit === 'function') form.requestSubmit();
                    else form.submit();
                }

                input.addEventListener('input', function () {
                    syncClear();
                    if (!live) return;
                    filterLocalRows(input.value);
                    clearTimeout(timer);
                    /* Instituciones / pagos: filtro local instantáneo (sin recarga). */
                    if (board && (board.querySelector('[data-inst-card]') || board.querySelector('[data-pay-card]'))) return;
                    timer = setTimeout(submitSearch, 380);
                });

                clear.addEventListener('click', function (e) {
                    e.preventDefault();
                    clearTimeout(timer);
                    input.value = '';
                    lastSubmitted = '';
                    syncClear();
                    filterLocalRows('');
                    window.location.href = clear.getAttribute('href') || form.getAttribute('action') || window.location.pathname;
                });

                syncClear();
                if (live) filterLocalRows(input.value);

                if (live && input.value) {
                    input.focus();
                    try {
                        var len = input.value.length;
                        input.setSelectionRange(len, len);
                    } catch (err) {}
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>

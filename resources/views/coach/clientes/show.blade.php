@extends('layouts.coach')

@section('title', 'Expediente cliente')

@section('content')
    @php
        $u = $cliente->usuario;
        $nombre = trim(($u?->nombres ?? '').' '.($u?->apellidos ?? ''));
        $plan = $cliente->planActivo()?->plan?->nombre ?? 'Sin plan activo';
        $progresos = $cliente->registrosProgreso ?? collect();
        $pesos = $progresos->pluck('peso_kg')->filter()->values();
    @endphp

    <div class="ch-page">
        <div style="margin-bottom:8px">
            <a class="ch-link" href="{{ route('coach.clientes.index') }}">← Mis clientes</a>
        </div>

        <section class="ch-hero">
            <div class="ch-avatar ch-avatar--lg">
                @if ($u?->avatarUrl())
                    <img src="{{ $u->avatarUrl() }}" alt="">
                @else
                    {{ $u?->iniciales() ?? '?' }}
                @endif
            </div>
            <div>
                <div class="ch-kicker">Expediente</div>
                <h1 class="ch-title">{{ $nombre }}</h1>
                <p class="ch-sub">
                    {{ $cliente->modalidad }} · {{ $cliente->estado_cliente }} · {{ $plan }}
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px">
                    @if ($cliente->objetivo_principal)
                        <span class="ch-badge is-pink">{{ $cliente->objetivo_principal }}</span>
                    @endif
                    @if ($cliente->diasRacha())
                        <span class="ch-badge is-ok">{{ $cliente->diasRacha() }} días racha</span>
                    @endif
                </div>
            </div>
            <div class="ch-hero__actions">
                @if ($chat)
                    <a class="ch-btn" href="{{ route('coach.comunicacion.show', $chat->id_chat) }}">Abrir chat</a>
                @endif
                <a class="ch-btn ch-btn-ghost" href="{{ route('coach.reportes.progreso', ['id_cliente' => $cliente->id_cliente]) }}">Progreso</a>
            </div>
        </section>

        <div class="ch-grid-12">
            <div class="ch-span-5">
                <x-coach.card title="Objetivos y metas">
                    @forelse ($cliente->metas as $meta)
                        <div class="ch-pending">
                            <span class="ch-pending__dot" style="background:var(--ch-cyan)"></span>
                            <div>
                                <strong style="font-size:12px">{{ $meta->titulo ?? $meta->tipo_meta ?? 'Meta' }}</strong>
                                <div style="font-size:11px;color:var(--ch-muted)">{{ $meta->descripcion ?? $meta->estado ?? '' }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="ch-empty">Sin metas registradas. Objetivo principal: {{ $cliente->objetivo_principal ?? '—' }}</p>
                    @endforelse
                </x-coach.card>

                <x-coach.card title="Datos físicos" subtitle="Línea base" style="margin-top:16px">
                    <div class="ch-metrics">
                        <x-coach.metric label="Peso inicial" :value="$cliente->peso_inicial_kg ? $cliente->peso_inicial_kg.' kg' : '—'" />
                        <x-coach.metric label="Estatura" :value="$cliente->estatura_cm ? $cliente->estatura_cm.' cm' : '—'" />
                    </div>
                </x-coach.card>
            </div>

            <div class="ch-span-7">
                <x-coach.card title="Evolución de peso" subtitle="Últimos registros">
                    @if ($pesos->count() >= 2)
                        <div class="ch-chart">
                            @php
                                $max = $pesos->max();
                                $min = $pesos->min();
                                $range = max(0.1, $max - $min);
                                $points = $pesos->take(12)->values()->map(function ($p, $i) use ($pesos, $range, $min) {
                                    $x = $pesos->count() > 1 ? ($i / max(1, $pesos->count() - 1)) * 100 : 50;
                                    $y = 100 - ((($p - $min) / $range) * 80 + 10);
                                    return round($x, 1).','.round($y, 1);
                                })->implode(' ');
                            @endphp
                            <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                                <polyline fill="none" stroke="url(#grad)" stroke-width="1.5" points="{{ $points }}"/>
                                <defs>
                                    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:#ff2d78"/>
                                        <stop offset="100%" style="stop-color:#2ef5e6"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    @else
                        <p class="ch-empty">Se necesitan al menos 2 registros para graficar.</p>
                    @endif

                    @forelse ($progresos->take(6) as $r)
                        <div class="ch-session">
                            <div class="ch-session__date">
                                <strong>{{ optional($r->fecha_registro)->format('d') }}</strong>
                                <small>{{ optional($r->fecha_registro)->format('M') }}</small>
                            </div>
                            <div>
                                @if ($r->peso_kg)<span>Peso {{ $r->peso_kg }} kg</span>@endif
                                @if ($r->cintura_cm) · Cintura {{ $r->cintura_cm }} cm @endif
                                @if ($r->observacion_coach)
                                    <div style="font-size:11px;color:var(--ch-muted);margin-top:4px">{{ $r->observacion_coach }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="ch-empty">Sin registros de progreso.</p>
                    @endforelse
                </x-coach.card>
            </div>
        </div>

        @if ($planEntrenamiento)
            <x-coach.card title="Plan de entrenamiento activo" subtitle="{{ $planEntrenamiento->nombre }}">
                <p style="font-size:13px;color:var(--ch-muted);margin:0 0 12px">Objetivo: {{ $planEntrenamiento->objetivo ?? '—' }}</p>
                @foreach ($planEntrenamiento->rutinas as $rutina)
                    <div class="ch-routine">
                        <h3>{{ $rutina->nombre }} @if($rutina->duracion_min)<small style="color:var(--ch-muted)">· {{ $rutina->duracion_min }} min</small>@endif</h3>
                        @foreach ($rutina->ejercicios as $re)
                            <div class="ch-exercise">
                                <span>{{ $re->ejercicio?->nombre ?? 'Ejercicio' }}</span>
                                <span>{{ $re->series }}×{{ $re->repeticiones }} · {{ $re->descanso_seg }}s desc.</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
                <a class="ch-link" href="{{ route('coach.entrenamiento.show', $planEntrenamiento->id_plan_entrenamiento) }}">Editar plan →</a>
            </x-coach.card>
        @else
            <x-coach.card title="Plan de entrenamiento">
                <p class="ch-empty">Este cliente aún no tiene plan asignado por ti.</p>
                <form method="POST" action="{{ route('coach.entrenamiento.planes.store') }}" style="margin-top:12px">
                    @csrf
                    <input type="hidden" name="id_cliente" value="{{ $cliente->id_cliente }}">
                    <div class="ch-form-row">
                        <div class="ch-field">
                            <label>Nombre del plan</label>
                            <input type="text" name="nombre" required placeholder="Plan fuerza 8 semanas">
                        </div>
                        <div class="ch-field">
                            <label>Objetivo</label>
                            <input type="text" name="objetivo" placeholder="Hipertrofia, resistencia...">
                        </div>
                    </div>
                    <button type="submit" class="ch-btn">Crear plan</button>
                </form>
            </x-coach.card>
        @endif
    </div>
@endsection

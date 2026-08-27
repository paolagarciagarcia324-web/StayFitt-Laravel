@extends('layouts.coach')

@section('title', 'Seguimiento de progreso')

@section('content')
    <div class="ch-page">
        <div class="ch-kicker">RF-046 · Seguimiento corporal</div>
        <h1 class="ch-title">Progreso de clientes</h1>
        <p class="ch-sub">Consulta evolución, registra observaciones y exporta reportes.</p>

        <x-coach.card title="Seleccionar cliente">
            <form method="GET" action="{{ route('coach.reportes.progreso') }}">
                <div class="ch-form-row">
                    <div class="ch-field">
                        <label>Cliente asignado</label>
                        <select name="id_cliente" onchange="this.form.submit()">
                            <option value="">Selecciona un cliente…</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id_cliente }}" @selected($idCliente == $c->id_cliente)>
                                    {{ $c->usuario?->nombres }} {{ $c->usuario?->apellidos }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </x-coach.card>

        @if ($idCliente)
            @php
                $clienteSel = $clientes->firstWhere('id_cliente', $idCliente);
                $pesos = $registros->pluck('peso_kg')->filter()->values();
            @endphp

            <div class="ch-grid-12">
                <div class="ch-span-8">
                    <x-coach.card title="Historial de registros" subtitle="{{ $registros->count() }} entradas">
                        <x-slot:actions>
                            <form method="POST" action="{{ route('coach.reportes.progreso.export') }}">
                                @csrf
                                <input type="hidden" name="id_cliente" value="{{ $idCliente }}">
                                <button class="ch-btn ch-btn-ghost" type="submit" style="padding:8px 14px;font-size:11px">Exportar CSV</button>
                            </form>
                        </x-slot:actions>

                        @if ($pesos->count() >= 2)
                            <div class="ch-chart" style="margin-bottom:16px">
                                @php
                                    $max = $pesos->max();
                                    $min = $pesos->min();
                                    $range = max(0.1, $max - $min);
                                    $points = $pesos->reverse()->values()->map(function ($p, $i) use ($pesos, $range, $min) {
                                        $x = $pesos->count() > 1 ? ($i / max(1, $pesos->count() - 1)) * 100 : 50;
                                        $y = 100 - ((($p - $min) / $range) * 80 + 10);
                                        return round($x, 1).','.round($y, 1);
                                    })->implode(' ');
                                @endphp
                                <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                                    <polyline fill="none" stroke="url(#gradProg)" stroke-width="1.5" points="{{ $points }}"/>
                                    <defs>
                                        <linearGradient id="gradProg" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" style="stop-color:#ff2d78"/>
                                            <stop offset="100%" style="stop-color:#2ef5e6"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                        @endif

                        @forelse ($registros as $r)
                            <div class="ch-session">
                                <div class="ch-session__date">
                                    <strong>{{ optional($r->fecha_registro)->format('d') }}</strong>
                                    <small>{{ optional($r->fecha_registro)->format('M/y') }}</small>
                                </div>
                                <div style="flex:1">
                                    <div style="font-size:13px">
                                        @if ($r->peso_kg) Peso <strong>{{ $r->peso_kg }} kg</strong> @endif
                                        @if ($r->imc) · IMC <strong>{{ $r->imc }}</strong> @endif
                                        @if ($r->cintura_cm) · Cintura <strong>{{ $r->cintura_cm }} cm</strong> @endif
                                    </div>
                                    @if ($r->observacion_coach)
                                        <p style="font-size:12px;color:var(--ch-muted);margin:6px 0 0">{{ $r->observacion_coach }}</p>
                                    @else
                                        <form method="POST" action="{{ route('coach.reportes.progreso.observacion') }}" style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap">
                                            @csrf
                                            <input type="hidden" name="id_registro_progreso" value="{{ $r->id_registro_progreso }}">
                                            <input type="text" name="observacion_coach" required placeholder="Observación del coach..." style="flex:1;min-width:180px;padding:8px 10px;border-radius:8px;border:1px solid var(--ch-line);background:rgba(0,0,0,.2);color:var(--ch-text)">
                                            <button type="submit" class="ch-btn" style="padding:8px 14px;font-size:11px">Guardar</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="ch-empty">Sin registros de progreso para este cliente.</p>
                        @endforelse
                    </x-coach.card>
                </div>

                <div class="ch-span-4">
                    <x-coach.card title="Resumen" subtitle="{{ $clienteSel?->usuario?->nombres ?? 'Cliente' }}">
                        <div class="ch-metrics">
                            <x-coach.metric label="Registros" :value="$registros->count()" />
                            <x-coach.metric label="Último peso" :value="$registros->first()?->peso_kg ? $registros->first()->peso_kg.' kg' : '—'" />
                        </div>
                        <a class="ch-link" href="{{ route('coach.clientes.show', $idCliente) }}" style="display:inline-block;margin-top:12px">Ver expediente →</a>
                    </x-coach.card>
                </div>
            </div>
        @endif
    </div>
@endsection

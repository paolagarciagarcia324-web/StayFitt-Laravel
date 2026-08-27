@extends('layouts.cliente')

@section('content')
    <x-cliente.page-head
        kicker="Progreso"
        title="Mi evolución"
        subtitle="Peso, medidas corporales y metas con tu coach."
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-sm" href="{{ route('cliente.progreso.create') }}">Nuevo registro</a>
        </x-slot:actions>
    </x-cliente.page-head>

    <div class="cv-metrics">
        <x-cliente.metric label="Último peso" :value="$ultimo?->peso_kg ? $ultimo->peso_kg.' kg' : '—'" />
        <x-cliente.metric label="IMC" :value="$ultimo?->imc ?? '—'" />
        <x-cliente.metric label="Registros" :value="$registros->total()" />
        <x-cliente.metric label="Metas activas" :value="$metas->count()" />
    </div>

    @if ($seriePeso->isNotEmpty())
        <x-cliente.card title="Evolución de peso" subtitle="Últimos {{ $seriePeso->count() }} registros">
            @php $max = max(1, (float) $seriePeso->max('peso_kg')); @endphp
            <div class="cv-chart" role="img" aria-label="Gráfico de evolución de peso">
                @foreach ($seriePeso as $punto)
                    <span style="height: {{ max(8, ((float) $punto->peso_kg / $max) * 100) }}%" title="{{ $punto->peso_kg }} kg · {{ optional($punto->fecha_registro)->format('d/m') }}"></span>
                @endforeach
            </div>
        </x-cliente.card>
    @endif

    <div class="cv-grid-12">
        <div class="cv-span-7">
            <x-cliente.card title="Historial" subtitle="Registros de bienestar y medidas">
                <div class="cv-table-wrap">
                    <table class="cv-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Peso</th>
                                <th>IMC</th>
                                <th>Energía</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($registros as $reg)
                                <tr>
                                    <td>{{ optional($reg->fecha_registro)->format('d/m/Y') }}</td>
                                    <td>{{ $reg->peso_kg ? $reg->peso_kg.' kg' : '—' }}</td>
                                    <td>{{ $reg->imc ?? '—' }}</td>
                                    <td>{{ $reg->nivel_energia ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="cv-empty">Sin registros. Crea el primero.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($registros->hasPages())
                    <div style="padding-top:12px">{{ $registros->links() }}</div>
                @endif
            </x-cliente.card>
        </div>

        <div class="cv-span-5">
            <x-cliente.card title="Metas" subtitle="Objetivos definidos por tu coach">
                <div class="cv-list-feed">
                    @forelse ($metas as $meta)
                        @php
                            $actual = (float) ($meta->valor_actual ?? 0);
                            $objetivo = max(0.01, (float) ($meta->valor_objetivo ?? 1));
                            $pctMeta = min(100, round(($actual / $objetivo) * 100));
                        @endphp
                        <div class="cv-feed-item">
                            <div class="cv-tile__ico is-pink" aria-hidden="true">🎯</div>
                            <div style="flex:1">
                                <strong>{{ $meta->nombre }}</strong>
                                <p style="margin:4px 0 8px;font-size:12px;color:var(--cv-muted)">
                                    {{ $meta->valor_actual }} / {{ $meta->valor_objetivo }} {{ $meta->unidad }}
                                </p>
                                <div class="cv-progress">
                                    <i style="width: {{ $pctMeta }}%"></i>
                                </div>
                            </div>
                            <span class="cv-badge">{{ $meta->estado }}</span>
                        </div>
                    @empty
                        <p class="cv-empty">Sin metas definidas por tu coach.</p>
                    @endforelse
                </div>
            </x-cliente.card>
        </div>
    </div>
@endsection

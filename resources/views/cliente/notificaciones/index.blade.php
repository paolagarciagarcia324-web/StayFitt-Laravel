@extends('layouts.cliente')

@section('title', 'Notificaciones')

@section('content')
    @php
        $noLeidas = $lista->where('leida', false)->count();
        $leidas = $lista->where('leida', true)->count();
    @endphp

    <x-cliente.page-head
        kicker="Centro de avisos"
        title="Notificaciones"
        subtitle="Actualizaciones de tu plan, pagos, entrenamiento y actividad en FigueFit"
    >
        <x-slot:actions>
            @if ($lista->isNotEmpty() && $noLeidas > 0)
                <form method="POST" action="{{ route('cliente.notificaciones.todas') }}">
                    @csrf
                    <button class="cv-btn cv-btn-ghost cv-btn-sm" type="submit">Marcar todas leídas</button>
                </form>
            @endif
        </x-slot:actions>
    </x-cliente.page-head>

    <div class="cv-metrics">
        <x-cliente.metric label="Total" :value="$lista->count()" hint="Avisos en tu bandeja" />
        <x-cliente.metric label="Sin leer" :value="$noLeidas" hint="Pendientes de revisar" />
        <x-cliente.metric label="Leídas" :value="$leidas" hint="Ya revisadas" />
        <x-cliente.metric
            label="Estado"
            :value="$noLeidas > 0 ? 'Pendientes' : 'Al día'"
            :hint="$noLeidas > 0 ? 'Tienes avisos nuevos' : 'No hay avisos pendientes'"
        />
    </div>

    <x-cliente.card title="Bandeja de avisos" subtitle="Toca «Leída» para archivar cada notificación">
        <div class="cv-list-feed">
            @forelse ($lista as $notif)
                <article
                    class="cv-feed-item"
                    style="{{ $notif->leida ? 'opacity:.72' : 'border-color:rgba(255,45,120,.35);background:rgba(255,45,120,.06)' }}"
                >
                    <div
                        class="cv-tile__ico {{ $notif->leida ? '' : 'is-pink' }}"
                        aria-hidden="true"
                        style="font-size:11px;font-weight:800"
                    >
                        {{ $notif->leida ? '✓' : '●' }}
                    </div>
                    <div>
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px">
                            <strong>{{ $notif->titulo }}</strong>
                            @if (! $notif->leida)
                                <span class="cv-badge is-pink">Nueva</span>
                            @endif
                            @if ($notif->tipo)
                                <span class="cv-badge">{{ $notif->tipo }}</span>
                            @endif
                        </div>
                        <p style="margin:8px 0 0;color:var(--cv-muted);font-size:13px;line-height:1.5">
                            {{ $notif->mensaje }}
                        </p>
                        <time
                            datetime="{{ optional($notif->creado_en)->toIso8601String() }}"
                            style="display:block;margin-top:8px;font-size:11px;color:var(--cv-faint)"
                        >
                            {{ optional($notif->creado_en)->format('d/m/Y H:i') }}
                        </time>
                    </div>
                    @if (! $notif->leida)
                        <form method="POST" action="{{ route('cliente.notificaciones.leida', $notif->id_notificacion) }}">
                            @csrf
                            <button class="cv-btn cv-btn-ghost cv-btn-sm" type="submit">Leída</button>
                        </form>
                    @else
                        <span class="cv-badge is-ok">Leída</span>
                    @endif
                </article>
            @empty
                <div class="cv-empty">No tienes notificaciones. Te avisaremos aquí cuando haya novedades.</div>
            @endforelse
        </div>
    </x-cliente.card>
@endsection

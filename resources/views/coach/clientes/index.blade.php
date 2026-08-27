@extends('layouts.coach')

@section('title', 'Mis clientes')

@section('content')
    <div class="ch-page">
        <div class="ch-kicker">Cartera de atletas</div>
        <h1 class="ch-title">Mis clientes</h1>
        <p class="ch-sub">{{ $clientes->count() }} miembros bajo tu supervisión directa.</p>

        @if ($clientes->isEmpty())
            <x-coach.card title="Sin asignaciones">
                <p class="ch-empty">No tienes clientes asignados actualmente. El administrador puede vincular clientes desde el panel de usuarios.</p>
            </x-coach.card>
        @else
            <div class="ch-clients">
                @foreach ($clientes as $c)
                    @php
                        $u = $c->usuario;
                        $meta = $c->metas->first();
                        $pct = min(100, max(15, ($c->diasRacha() ?? 0) * 10 + 25));
                    @endphp
                    <article class="ch-client">
                        <div class="ch-client__top">
                            <div class="ch-avatar">
                                @if ($u?->avatarUrl())
                                    <img src="{{ $u->avatarUrl() }}" alt="">
                                @else
                                    {{ $u?->iniciales() ?? '?' }}
                                @endif
                            </div>
                            <div class="ch-client__meta">
                                <strong>{{ trim(($u?->nombres ?? '').' '.($u?->apellidos ?? '')) }}</strong>
                                <small>{{ $c->plan_nombre }} · {{ $c->estado_cliente }}</small>
                            </div>
                        </div>

                        <div style="font-size:12px;color:var(--ch-muted)">
                            @if ($c->objetivo_principal)
                                <span class="ch-badge is-cyan">{{ $c->objetivo_principal }}</span>
                            @endif
                            @if ($c->ultimo_peso)
                                <span style="margin-left:6px">Último peso: <strong style="color:var(--ch-text)">{{ $c->ultimo_peso }} kg</strong></span>
                            @endif
                        </div>

                        <div class="ch-client__progress"><span style="width:{{ $pct }}%"></span></div>

                        <div class="ch-client__actions">
                            <a class="ch-btn" style="padding:8px 14px;font-size:11px" href="{{ route('coach.clientes.show', $c->id_cliente) }}">Ver expediente</a>
                            <a class="ch-link" href="{{ route('coach.entrenamiento.index') }}?cliente={{ $c->id_cliente }}">Rutinas</a>
                            <a class="ch-link" href="{{ route('coach.comunicacion.index') }}">Chat</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection

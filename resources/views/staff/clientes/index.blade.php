@extends('layouts.staff')

@section('title', 'Clientes de apoyo')

@section('content')
    <div class="st-page">
        <div class="st-kicker">Cartera operativa</div>
        <h1 class="st-title">Clientes asignados</h1>
        <p class="st-sub">Consulta de apoyo para {{ $coach->usuario?->nombres }} — solo lectura y observaciones.</p>

        @if ($clientes->isEmpty())
            <x-staff.card title="Sin clientes"><p class="st-empty">No hay clientes en la cartera del coach seleccionado.</p></x-staff.card>
        @else
            <div class="st-clients">
                @foreach ($clientes as $c)
                    @php $u = $c->usuario; @endphp
                    <article class="st-client">
                        <div class="st-client__top">
                            <div class="st-avatar">{{ $u?->iniciales() ?? '?' }}</div>
                            <div class="st-client__meta">
                                <strong>{{ trim(($u?->nombres ?? '').' '.($u?->apellidos ?? '')) }}</strong>
                                <small>{{ $c->estado_cliente }} · {{ $c->modalidad }}</small>
                            </div>
                        </div>
                        @if ($c->objetivo_principal)<span class="st-badge is-cyan">{{ $c->objetivo_principal }}</span>@endif
                        @if ($c->ultimo_peso)<small style="color:var(--st-muted)">Peso: {{ $c->ultimo_peso }} kg</small>@endif
                        <div class="st-client__actions">
                            <a class="st-btn" style="padding:8px 14px;font-size:11px" href="{{ route('staff.clientes.show', $c->id_cliente) }}">Ver expediente</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection

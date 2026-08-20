@extends('layouts.admin')

@section('title', 'Calendario')
@section('subtitle', $mes->format('m/Y'))

@section('actions')
    <a class="ad-btn ad-btn-ghost" href="{{ route('admin.eventos.index') }}">Eventos</a>
@endsection

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-cal">
            @foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dow)
                <div class="ad-cal-dow">{{ $dow }}</div>
            @endforeach

            @foreach ($celdas as $celda)
                @php
                    /** @var \Illuminate\Support\Carbon $fecha */
                    $fecha = $celda['fecha'];
                    $evs = $celda['eventos'];
                @endphp
                <div class="ad-cal-cell {{ $celda['enMes'] ? '' : 'is-muted' }} {{ $celda['hoy'] ? 'is-today' : '' }} {{ $evs->isNotEmpty() ? 'has-events' : '' }}">
                    <span class="ad-cal-day">{{ $fecha->day }}</span>
                    @foreach ($evs->take(3) as $i => $evento)
                        <span class="ad-cal-chip {{ $i % 2 ? 'is-pink' : '' }}" title="{{ $evento->nombre }}">
                            {{ $evento->nombre }}
                        </span>
                    @endforeach
                </div>
            @endforeach
        </div>

        @if ($eventos->isEmpty())
            <div class="ad-empty-hero">
                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'calendario'])</span>
                <strong>Sin eventos este mes</strong>
                <a class="ad-btn" href="{{ route('admin.eventos.index') }}">Crear evento</a>
            </div>
        @endif
    </section>

    <section class="ad-card ad-users" style="margin-top:16px">
        <div class="ad-card-head"><h2>Sesiones</h2></div>
        <table class="ad-table ad-users-table">
            <thead>
                <tr><th>Fecha</th><th>Título</th><th>Horario</th><th>Modalidad</th><th>Estado</th></tr>
            </thead>
            <tbody>
                @forelse ($sesiones as $sesion)
                    <tr>
                        <td>{{ $sesion->fecha }}</td>
                        <td><strong>{{ $sesion->titulo }}</strong></td>
                        <td>{{ substr((string) $sesion->hora_inicio, 0, 5) }} – {{ substr((string) $sesion->hora_fin, 0, 5) }}</td>
                        <td>{{ $sesion->modalidad }}</td>
                        <td>{{ $sesion->estado }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="ad-empty-hero" style="padding:24px">
                                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'horarios'])</span>
                                <strong>Sin sesiones</strong>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection

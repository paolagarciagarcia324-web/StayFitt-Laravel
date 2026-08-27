@extends('layouts.staff')

@section('title', 'Expediente cliente')

@section('content')
    @php $u = $cliente->usuario; $nombre = trim(($u?->nombres ?? '').' '.($u?->apellidos ?? '')); @endphp
    <div class="st-page">
        <a class="st-link" href="{{ route('staff.clientes.index') }}">← Clientes apoyo</a>

        <section class="st-hero" style="margin-top:12px">
            <div class="st-avatar st-avatar--lg">{{ $u?->iniciales() ?? '?' }}</div>
            <div>
                <div class="st-kicker">Solo consulta + observaciones</div>
                <h1 class="st-title">{{ $nombre }}</h1>
                <p class="st-sub">{{ $cliente->modalidad }} · {{ $cliente->estado_cliente }}</p>
            </div>
        </section>

        <div class="st-grid-12">
            <div class="st-span-6">
                <x-staff.card title="Progreso reciente">
                    @forelse ($cliente->registrosProgreso ?? collect() as $r)
                        <div class="st-session">
                            <div>
                                <strong>{{ optional($r->fecha_registro)->format('d/m/Y') }}</strong>
                                @if ($r->peso_kg) · {{ $r->peso_kg }} kg @endif
                                @if ($r->observacion_coach)<p style="font-size:11px;color:var(--st-muted);margin:4px 0 0">{{ $r->observacion_coach }}</p>@endif
                            </div>
                            @if (!$r->observacion_coach)
                                <form method="POST" action="{{ route('staff.clientes.observacion') }}" style="display:flex;gap:6px;flex:1">
                                    @csrf
                                    <input type="hidden" name="id_registro_progreso" value="{{ $r->id_registro_progreso }}">
                                    <input type="text" name="observacion_coach" required placeholder="Observación..." style="flex:1;padding:6px 8px;border-radius:8px;border:1px solid var(--st-line);background:rgba(0,0,0,.2);color:var(--st-text);font-size:11px">
                                    <button class="st-btn" type="submit" style="padding:6px 10px;font-size:10px">Enviar</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="st-empty">Sin registros.</p>
                    @endforelse
                </x-staff.card>
            </div>
            <div class="st-span-6">
                <x-staff.card title="Rutina activa">
                    @if ($planEntrenamiento)
                        <strong>{{ $planEntrenamiento->nombre }}</strong>
                        <p style="font-size:12px;color:var(--st-muted)">{{ $planEntrenamiento->objetivo }} · {{ $planEntrenamiento->estado }}</p>
                        @foreach ($planEntrenamiento->rutinas->take(3) as $r)
                            <div class="st-routine"><h3>{{ $r->nombre }}</h3></div>
                        @endforeach
                    @else
                        <p class="st-empty">Sin plan de entrenamiento.</p>
                    @endif
                </x-staff.card>
            </div>
        </div>
    </div>
@endsection

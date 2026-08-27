@extends('layouts.institucional')

@section('title', 'Detalle miembro')

@section('content')
    @php $u = $miembro->usuario; $plan = $miembro->planesCliente->sortByDesc('id_plan_cliente')->first(); @endphp
    <div class="in-page">
        <section class="in-hero">
            <div class="in-avatar" style="width:72px;height:72px;font-size:22px">
                @if ($u?->avatarUrl())
                    <img src="{{ $u->avatarUrl() }}" alt="">
                @else
                    {{ $u?->iniciales() ?? '?' }}
                @endif
            </div>
            <div>
                <div class="in-kicker">Miembro institucional</div>
                <h1 class="in-title">{{ trim(($u?->nombres ?? '').' '.($u?->apellidos ?? '')) }}</h1>
                <p class="in-sub">{{ $u?->correo ?? $u?->email }} · {{ $miembro->objetivo_principal ?? 'Sin objetivo' }}</p>
            </div>
            <div class="in-hero__actions">
                <a class="in-btn in-btn-ghost" href="{{ route('institucional.usuarios.index') }}">← Volver</a>
            </div>
        </section>

        <div class="in-profile-grid">
            <div class="in-info-pill"><span>Estado</span><strong>{{ $miembro->estado_cliente }}</strong></div>
            <div class="in-info-pill"><span>Modalidad</span><strong>{{ $miembro->modalidad }}</strong></div>
            <div class="in-info-pill"><span>Plan</span><strong>{{ $plan?->plan?->nombre ?? '—' }}</strong></div>
            <div class="in-info-pill"><span>Alta</span><strong>{{ $miembro->fecha_alta?->format('d/m/Y') ?? '—' }}</strong></div>
            <div class="in-info-pill"><span>Activación</span><strong>{{ $miembro->fecha_activacion?->format('d/m/Y') ?? '—' }}</strong></div>
            <div class="in-info-pill"><span>Último acceso</span><strong>{{ $ultimoAcceso ? \Illuminate\Support\Carbon::parse($ultimoAcceso)->format('d/m/Y H:i') : '—' }}</strong></div>
        </div>

        <div class="in-grid-12">
            <div class="in-span-6">
                <x-institucional.card title="Actividad reciente" subtitle="Registros de progreso (datos permitidos)">
                    @if ($progresos->isEmpty())
                        <p class="in-empty">Sin registros de progreso aún.</p>
                    @else
                        <div class="in-timeline">
                            @foreach ($progresos as $p)
                                <div class="in-timeline-item">
                                    <span class="in-timeline-dot"></span>
                                    <div>
                                        <strong style="font-size:12px">Progreso {{ $p->fecha_registro?->format('d/m/Y') }}</strong>
                                        <div style="font-size:11px;color:var(--in-muted)">
                                            @if ($p->peso_kg)
                                                Peso: {{ $p->peso_kg }} kg
                                            @endif
                                            @if ($p->porcentaje_grasa)
                                                · Grasa: {{ $p->porcentaje_grasa }}%
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-institucional.card>
            </div>
            <div class="in-span-6">
                <x-institucional.card title="Rachas y hábitos">
                    @if ($miembro->rachas->isEmpty())
                        <p class="in-empty">Sin rachas registradas aún.</p>
                    @else
                        <div class="in-timeline">
                            @foreach ($miembro->rachas->take(5) as $r)
                                <div class="in-timeline-item">
                                    <span class="in-timeline-dot"></span>
                                    <div>
                                        <strong style="font-size:12px">{{ $r->tipo_racha ?? 'Racha general' }}</strong>
                                        <div style="font-size:11px;color:var(--in-muted)">{{ $r->dias_consecutivos ?? 0 }} días consecutivos</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-institucional.card>
            </div>
        </div>
    </div>
@endsection

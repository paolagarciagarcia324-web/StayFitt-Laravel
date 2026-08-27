@extends('layouts.institucional')

@section('title', 'Invitaciones')

@section('content')
    <div class="in-page">
        <section class="in-hero" style="grid-template-columns:1fr auto">
            <div>
                <div class="in-kicker">Alta de colaboradores</div>
                <h1 class="in-title">Invitaciones y enlaces</h1>
                <p class="in-sub">Cupos disponibles: {{ $institucion->cuposDisponibles() === PHP_INT_MAX ? 'Ilimitados' : $institucion->cuposDisponibles() }}</p>
            </div>
        </section>

        <div class="in-grid-12">
            <div class="in-span-6">
                <x-institucional.card title="Invitación individual" subtitle="Correo específico · estados Pendiente / Aceptada / Expirada">
                    <form method="POST" action="{{ route('institucional.invitaciones.store') }}">
                        @csrf
                        <div class="in-form-grid">
                            <div class="in-field full"><label>Correo del colaborador</label><input type="email" name="correo" required placeholder="colaborador@empresa.com"></div>
                            <div class="in-field"><label>Nombre sugerido</label><input type="text" name="nombre_sugerido" placeholder="Opcional"></div>
                            <div class="in-field">
                                <label>Plan</label>
                                <select name="id_plan">
                                    <option value="">Plan del convenio</option>
                                    @foreach ($planes as $p)
                                        <option value="{{ $p->id_plan }}">{{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="in-field"><label>Días de validez</label><input type="number" name="dias_validez" value="14" min="1" max="90" required></div>
                        </div>
                        <div style="margin-top:12px"><button type="submit" class="in-btn">Generar invitación</button></div>
                    </form>
                </x-institucional.card>
            </div>
            <div class="in-span-6">
                <x-institucional.card title="Enlace grupal" subtitle="Token compartible para múltiples registros">
                    <form method="POST" action="{{ route('institucional.invitaciones.enlace') }}">
                        @csrf
                        <div class="in-form-grid">
                            <div class="in-field full">
                                <label>Plan</label>
                                <select name="id_plan" required>
                                    @foreach ($planes as $p)
                                        <option value="{{ $p->id_plan }}" @selected($institucion->id_plan_contratado == $p->id_plan)>{{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="in-field"><label>Cupo máximo</label><input type="number" name="cupos_maximos" value="10" min="1" max="500" required></div>
                            <div class="in-field"><label>Días validez</label><input type="number" name="dias_validez" value="30" min="1" max="365" required></div>
                        </div>
                        <div style="margin-top:12px"><button type="submit" class="in-btn">Crear enlace</button></div>
                    </form>
                </x-institucional.card>
            </div>
        </div>

        <x-institucional.card title="Invitaciones enviadas">
            @forelse ($invitaciones as $inv)
                @php
                    $estado = $inv->estadoEfectivo();
                    $badge = match($estado) { 'ACEPTADA' => 'is-ok', 'EXPIRADA' => 'is-warn', default => 'is-blue' };
                @endphp
                <div class="in-invite-row">
                    <div>
                        <strong>{{ $inv->correo }}</strong>
                        <div style="font-size:11px;color:var(--in-muted)">Expira {{ $inv->fecha_expiracion->format('d/m/Y H:i') }}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                        <span class="in-badge {{ $badge }}">{{ $inv->etiquetaEstado() }}</span>
                        @if ($estado === 'PENDIENTE')
                            <code>{{ url('/registro-institucional/invitacion/'.$inv->token) }}</code>
                            <form method="POST" action="{{ route('institucional.invitaciones.destroy', $inv->id_invitacion) }}" onsubmit="return confirm('¿Eliminar invitación?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="in-btn in-btn-ghost in-btn-danger" style="padding:6px 12px">Eliminar</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="in-empty">Sin invitaciones individuales.</p>
            @endforelse
            <div class="in-pagination">{{ $invitaciones->links() }}</div>
        </x-institucional.card>

        <x-institucional.card title="Enlaces grupales activos">
            @forelse ($enlaces as $item)
                @php $e = $item['model']; $badge = match($item['estado']) { 'ACEPTADA' => 'is-ok', 'EXPIRADA' => 'is-warn', default => 'is-teal' }; @endphp
                <div class="in-invite-row">
                    <div>
                        <strong>{{ $e->plan?->nombre ?? 'Plan' }}</strong>
                        <div style="font-size:11px;color:var(--in-muted)">{{ $e->usos_actuales }} / {{ $e->cupos_maximos }} usos · expira {{ $e->fecha_expiracion->format('d/m/Y') }}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                        <span class="in-badge {{ $badge }}">{{ $item['estado'] }}</span>
                        @if ($item['estado'] === 'PENDIENTE')
                            <code>{{ $item['url'] }}</code>
                        @endif
                    </div>
                </div>
            @empty
                <p class="in-empty">Sin enlaces grupales.</p>
            @endforelse
        </x-institucional.card>
    </div>
@endsection

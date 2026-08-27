@extends('layouts.staff')

@section('title', 'Comunicación operativa')

@section('content')
    <div class="st-page">
        <div class="st-kicker">Ops · Coach</div>
        <h1 class="st-title">Centro de comunicación</h1>
        <p class="st-sub">Notas operativas y seguimiento con el coach {{ $coach->usuario?->nombres }}.</p>

        <x-staff.card title="Enviar nota al coach">
            <form method="POST" action="{{ route('staff.comunicacion.nota') }}">
                @csrf
                <div class="st-form-row">
                    <div class="st-field">
                        <label>Propuesta relacionada</label>
                        <select name="id_propuesta" required>
                            @foreach ($propuestasSelect as $p)
                                <option value="{{ $p->id_propuesta }}">{{ $p->titulo_interno }}</option>
                            @endforeach
                            @if ($propuestasSelect->isEmpty())
                                <option value="">— Crea una propuesta primero —</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="st-field" style="margin-bottom:12px">
                    <label>Mensaje operativo</label>
                    <textarea name="nota" rows="3" required placeholder="Sugerencia, recordatorio o consulta para el coach..."></textarea>
                </div>
                <button type="submit" class="st-btn" @disabled($propuestasSelect->isEmpty())>Enviar nota</button>
            </form>
        </x-staff.card>

        <x-staff.card title="Historial de notas y feedback">
            @forelse ($propuestasConNotas as $p)
                <div class="st-routine">
                    <h3>{{ $p->titulo_interno }} <span class="st-badge is-violet">{{ $p->etiquetaEstado() }}</span></h3>
                    @if ($p->notas_coach)<p style="font-size:12px"><strong>Coach:</strong> {{ $p->notas_coach }}</p>@endif
                    @if ($p->notas_admin)<p style="font-size:12px;color:var(--st-muted)"><strong>Admin:</strong> {{ $p->notas_admin }}</p>@endif
                    @if ($p->motivo_rechazo)<p style="font-size:12px;color:var(--st-danger)"><strong>Rechazo:</strong> {{ $p->motivo_rechazo }}</p>@endif
                </div>
            @empty
                <p class="st-empty">Sin comunicaciones registradas aún.</p>
            @endforelse
        </x-staff.card>

        <x-staff.card title="Coaches del equipo">
            <div class="st-clients">
                @foreach ($coaches as $c)
                    <article class="st-client">
                        <strong>{{ $c->usuario?->nombres }} {{ $c->usuario?->apellidos }}</strong>
                        <small>{{ $c->especialidad }}</small>
                    </article>
                @endforeach
            </div>
        </x-staff.card>
    </div>
@endsection

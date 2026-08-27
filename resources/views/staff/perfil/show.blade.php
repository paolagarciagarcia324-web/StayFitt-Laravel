@extends('layouts.staff')

@section('title', 'Mi perfil operativo')

@section('content')
    <div class="st-page">
        <div class="st-kicker">Perfil asistente técnico</div>
        <h1 class="st-title">{{ $user->nombres }} {{ $user->apellidos }}</h1>

        <div class="st-profile-grid">
            <aside class="st-profile-side">
                <div class="st-avatar st-avatar--lg" style="margin:0 auto 16px">{{ $user->iniciales() }}</div>
                <h2 style="margin:0;font-size:1.1rem">{{ $user->nombres }}</h2>
                <p style="color:var(--st-muted);font-size:13px">{{ session('staff_especialidad', 'Apoyo operativo fitness') }}</p>
                <span class="st-badge is-ok">ACTIVO</span>
                <div class="st-stat-row">
                    <div class="st-stat-box"><strong>{{ $totalClientes }}</strong><span>Clientes apoyo</span></div>
                    <div class="st-stat-box"><strong>{{ $coaches->count() }}</strong><span>Coaches</span></div>
                </div>
                <p style="font-size:12px;color:var(--st-muted);margin-top:16px;text-align:left">
                    Coach actual: <strong style="color:var(--st-text)">{{ $coach->usuario?->nombres }}</strong><br>
                    {{ $user->correo ?? $user->email }}
                </p>
            </aside>
            <div>
                <x-staff.card title="Datos personales">
                    <form method="POST" action="{{ route('staff.perfil.update') }}">
                        @csrf @method('PUT')
                        <div class="st-form-row">
                            <div class="st-field"><label>Nombres</label><input name="nombres" value="{{ old('nombres', $user->nombres) }}"></div>
                            <div class="st-field"><label>Apellidos</label><input name="apellidos" value="{{ old('apellidos', $user->apellidos) }}"></div>
                        </div>
                        <div class="st-form-row">
                            <div class="st-field"><label>Teléfono</label><input name="telefono" value="{{ old('telefono', $user->telefono) }}"></div>
                            <div class="st-field"><label>Especialidad ops</label><input name="especialidad" value="{{ old('especialidad', session('staff_especialidad')) }}" placeholder="Videos, rutinas, nutrición..."></div>
                        </div>
                        <button type="submit" class="st-btn">Guardar</button>
                    </form>
                </x-staff.card>
            </div>
        </div>
    </div>
@endsection

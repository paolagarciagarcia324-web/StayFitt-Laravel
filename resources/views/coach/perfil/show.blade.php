@extends('layouts.coach')

@section('title', 'Mi perfil profesional')

@section('content')
    @php
        $user = $coach->usuario;
        $nombre = trim(($user?->nombres ?? '').' '.($user?->apellidos ?? ''));
    @endphp

    <div class="ch-page">
        <div class="ch-kicker">Perfil del entrenador</div>
        <h1 class="ch-title">{{ $nombre ?: 'Coach FigueFit' }}</h1>
        <p class="ch-sub">Tu carta de presentación ante clientes y staff.</p>

        <div class="ch-profile-grid">
            <aside class="ch-profile-side">
                <div class="ch-avatar ch-avatar--lg" style="margin:0 auto 16px">
                    @if ($coach->foto_url)
                        <img src="{{ $coach->foto_url }}" alt="">
                    @elseif ($user?->avatarUrl())
                        <img src="{{ $user->avatarUrl() }}" alt="">
                    @else
                        {{ $user?->iniciales() ?? 'C' }}
                    @endif
                </div>
                <h2 style="margin:0;font-size:1.1rem">{{ $nombre }}</h2>
                <p style="color:var(--ch-muted);font-size:13px;margin:6px 0 0">{{ $coach->especialidad ?? 'Entrenamiento general' }}</p>
                <span class="ch-badge is-ok" style="margin-top:10px">{{ $coach->estado_coach ?? 'ACTIVO' }}</span>

                <div class="ch-stat-row">
                    <div class="ch-stat-box">
                        <strong>{{ $totalClientes }}</strong>
                        <span>Clientes</span>
                    </div>
                    <div class="ch-stat-box">
                        <strong>{{ $proximasSesiones->count() }}</strong>
                        <span>Sesiones</span>
                    </div>
                </div>

                <div style="margin-top:20px;text-align:left;font-size:12px;color:var(--ch-muted)">
                    <p><strong style="color:var(--ch-text)">Contacto</strong></p>
                    <p>{{ $user?->correo ?? $user?->email }}</p>
                    @if ($user?->telefono)
                        <p>{{ $user->telefono }}</p>
                    @endif
                </div>
            </aside>

            <div>
                <x-coach.card title="Información profesional" subtitle="Visible para el equipo y clientes">
                    <form method="POST" action="{{ route('coach.perfil.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="ch-form-row">
                            <div class="ch-field">
                                <label>Especialidad</label>
                                <input type="text" name="especialidad" value="{{ old('especialidad', $coach->especialidad) }}" placeholder="Ej. Fuerza, HIIT, Rehabilitación">
                            </div>
                            <div class="ch-field">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" value="{{ old('telefono', $user?->telefono) }}">
                            </div>
                        </div>
                        <div class="ch-field" style="margin-bottom:12px">
                            <label>URL foto profesional</label>
                            <input type="url" name="foto_url" value="{{ old('foto_url', $coach->foto_url) }}" placeholder="https://...">
                        </div>
                        <div class="ch-field" style="margin-bottom:12px">
                            <label>Descripción / experiencia</label>
                            <textarea name="biografia" rows="4" placeholder="Cuéntanos tu trayectoria, metodología y enfoque...">{{ old('biografia', $coach->biografia) }}</textarea>
                        </div>
                        <div class="ch-field" style="margin-bottom:16px">
                            <label>Certificaciones</label>
                            <input type="text" name="certificaciones" value="{{ old('certificaciones', $coach->certificaciones) }}" placeholder="NSCA-CPT, CrossFit L2, Nutrición deportiva...">
                        </div>
                        <button type="submit" class="ch-btn">Guardar perfil</button>
                    </form>
                </x-coach.card>

                @if ($coach->biografia || $coach->certificaciones)
                    <x-coach.card title="Vista previa pública" subtitle="Así te ven tus clientes" style="margin-top:16px">
                        @if ($coach->biografia)
                            <p style="font-size:14px;line-height:1.6;margin:0 0 12px">{{ $coach->biografia }}</p>
                        @endif
                        @if ($coach->certificaciones)
                            <p style="font-size:12px;color:var(--ch-muted);margin:0"><strong style="color:var(--ch-cyan)">Certificaciones:</strong> {{ $coach->certificaciones }}</p>
                        @endif
                    </x-coach.card>
                @endif
            </div>
        </div>
    </div>
@endsection

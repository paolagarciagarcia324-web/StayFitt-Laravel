@extends('layouts.admin')

@section('title', 'Coaches')
@section('subtitle', 'Equipo de entrenadores FigueFit')

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <form class="ad-users-search" method="GET" action="{{ route('admin.coaches.index') }}">
                <span>⌕</span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por nombre">
            </form>
            <button class="ad-btn ad-btn-add" type="button" data-open-coach-form>+ Crear coach</button>
        </div>

        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Perfil</th>
                    <th>Coach</th>
                    <th>Especialidad</th>
                    <th>Clientes</th>
                    <th>Valoración</th>
                    <th>Estado</th>
                    <th>Último acceso</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coaches as $index => $c)
                    @php $u = $c->usuario; @endphp
                    <tr>
                        <td>{{ $coaches->firstItem() + $index }}</td>
                        <td>@include('partials.admin-avatar', ['user' => $u])</td>
                        <td>
                            <strong>{{ $u?->nombres }} {{ $u?->apellidos }}</strong>
                            <em class="ad-user-mail">{{ $u?->correo }}</em>
                        </td>
                        <td>{{ $c->especialidad ?: '—' }}</td>
                        <td>{{ $c->clientes->count() }}</td>
                        <td>{{ number_format((float) $c->calificacion_promedio, 1) }} ★</td>
                        <td><span class="ad-badge ad-badge-ok">{{ $c->estado_coach }}</span></td>
                        <td>{{ $u?->ultimo_acceso_en ? $u->ultimo_acceso_en->diffForHumans() : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="ad-empty">No hay coaches registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ad-pagination">{{ $coaches->links() }}</div>
    </section>

    <div class="ad-modal {{ $errors->any() ? 'is-open' : '' }}" data-coach-modal {{ $errors->any() ? '' : 'hidden' }}>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Nuevo coach</h2>
                    <p>Cuenta de acceso + perfil profesional.</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-coach-form aria-label="Cerrar">×</button>
            </header>
            @if ($errors->any())
                <div class="ad-alert ad-alert-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form class="ad-form" method="POST" action="{{ route('admin.coaches.store') }}">
                @csrf
                <div class="ad-form-block"><h3>Datos personales</h3>
                    <div class="ad-form-row">
                        <label><span>Nombres *</span><input type="text" name="nombres" value="{{ old('nombres') }}" required></label>
                        <label><span>Apellidos *</span><input type="text" name="apellidos" value="{{ old('apellidos') }}" required></label>
                    </div>
                    <div class="ad-form-row">
                        <label><span>Correo *</span><input type="email" name="correo" value="{{ old('correo') }}" required></label>
                        <label><span>Documento *</span><input type="text" name="documento_identidad" value="{{ old('documento_identidad') }}" required></label>
                    </div>
                    <label><span>Teléfono</span><input type="text" name="telefono" value="{{ old('telefono') }}"></label>
                </div>
                <div class="ad-form-block"><h3>Perfil coach</h3>
                    <div class="ad-form-row">
                        <label><span>Código</span><input type="text" name="codigo_coach" value="{{ old('codigo_coach') }}" placeholder="Auto si vacío"></label>
                        <label><span>Especialidad *</span><input type="text" name="especialidad" value="{{ old('especialidad') }}" required></label>
                    </div>
                    <label><span>Biografía</span><textarea name="biografia" rows="2">{{ old('biografia') }}</textarea></label>
                    <label><span>Certificaciones</span><input type="text" name="certificaciones" value="{{ old('certificaciones') }}"></label>
                    <label><span>Estado *</span>
                        <select name="estado_coach" required>
                            @foreach (['ACTIVO', 'INACTIVO', 'VACACIONES'] as $e)
                                <option value="{{ $e }}" @selected(old('estado_coach', 'ACTIVO') === $e)>{{ $e }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-coach-form>Cancelar</button>
                    <button class="ad-btn" type="submit">Crear coach</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.querySelector('[data-coach-modal]');
            function open() { if (modal) { modal.hidden = false; modal.classList.add('is-open'); } }
            function close() { if (modal) { modal.hidden = true; modal.classList.remove('is-open'); } }
            document.querySelector('[data-open-coach-form]')?.addEventListener('click', open);
            document.querySelectorAll('[data-close-coach-form]').forEach(function (b) { b.addEventListener('click', close); });
            modal?.addEventListener('click', function (e) { if (e.target === modal) close(); });
        })();
    </script>
@endsection

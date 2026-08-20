@extends('layouts.admin')

@section('title', 'Horarios')
@section('subtitle', 'Cupos y grupos de WhatsApp de la sala presencial')

@section('content')
    <section class="ad-card ad-users">
        <p class="ad-empty" style="margin-bottom:16px">Estos horarios alimentan el registro presencial. El plan de sala se asigna automático; aquí controlas cupo, enlace y disponibilidad.</p>
        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>Horario</th>
                    <th>Días / hora</th>
                    <th>Cupo</th>
                    <th>WhatsApp</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($horarios as $horario)
                    <tr>
                        <td>
                            <strong>{{ $horario->nombre }}</strong>
                            <em class="ad-user-mail">{{ $horario->codigo }}</em>
                        </td>
                        <td>
                            {{ $horario->dias }}
                            <em class="ad-user-mail">{{ substr((string) $horario->hora_inicio, 0, 5) }} – {{ substr((string) $horario->hora_fin, 0, 5) }}</em>
                        </td>
                        <td><span class="ad-pill-days">{{ $horario->cupo_ocupado }} / {{ $horario->cupo_maximo }}</span></td>
                        <td>
                            @if ($horario->link_whatsapp)
                                <a class="ad-horario-link" href="{{ $horario->link_whatsapp }}" target="_blank" rel="noopener">Grupo WhatsApp</a>
                            @else
                                <span class="ad-user-mail">Sin enlace</span>
                            @endif
                        </td>
                        <td>
                            <span class="ad-badge {{ $horario->activo ? 'ad-badge-ok' : 'ad-badge-bad' }}">
                                {{ $horario->activo ? 'ACTIVO' : 'INACTIVO' }}
                            </span>
                        </td>
                        <td>
                            <button class="ad-btn ad-btn-ghost" type="button"
                                data-open-horario
                                data-id="{{ $horario->id_horario }}"
                                data-nombre="{{ $horario->nombre }}"
                                data-link="{{ $horario->link_whatsapp }}"
                                data-cupo="{{ $horario->cupo_maximo }}"
                                data-activo="{{ $horario->activo ? '1' : '0' }}">Editar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ad-empty">No hay horarios. Ejecuta las migraciones de sala presencial.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="ad-modal" data-horario-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2 data-horario-title>Editar horario</h2>
                    <p>Cupo, enlace de WhatsApp y disponibilidad.</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-horario aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-horario-form>
                @csrf
                @method('PUT')
                <label><span>Enlace WhatsApp</span><input type="url" name="link_whatsapp" data-field="link" placeholder="https://chat.whatsapp.com/..."></label>
                <label><span>Cupo máximo</span><input type="number" name="cupo_maximo" data-field="cupo" min="1" max="80" required></label>
                <label style="display:flex;gap:8px;align-items:center;margin:12px 0">
                    <input type="checkbox" name="activo" value="1" data-field="activo">
                    <span style="margin:0">Horario activo en registro</span>
                </label>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-horario>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.querySelector('[data-horario-modal]');
            var form = document.querySelector('[data-horario-form]');
            var title = document.querySelector('[data-horario-title]');
            function open(btn) {
                if (!modal || !form) return;
                var id = btn.getAttribute('data-id');
                form.action = '{{ url('admin/horarios') }}/' + id;
                title.textContent = btn.getAttribute('data-nombre');
                form.querySelector('[data-field="link"]').value = btn.getAttribute('data-link') || '';
                form.querySelector('[data-field="cupo"]').value = btn.getAttribute('data-cupo') || '';
                form.querySelector('[data-field="activo"]').checked = btn.getAttribute('data-activo') === '1';
                modal.hidden = false;
                modal.classList.add('is-open');
            }
            function close() {
                if (modal) { modal.hidden = true; modal.classList.remove('is-open'); }
            }
            document.querySelectorAll('[data-open-horario]').forEach(function (b) {
                b.addEventListener('click', function () { open(b); });
            });
            document.querySelectorAll('[data-close-horario]').forEach(function (b) {
                b.addEventListener('click', close);
            });
            modal?.addEventListener('click', function (e) { if (e.target === modal) close(); });
        })();
    </script>
@endsection

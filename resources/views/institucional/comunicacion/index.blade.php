@extends('layouts.institucional')

@section('title', 'Comunicación')

@section('content')
    <div class="in-page">
        <section class="in-hero" style="grid-template-columns:1fr auto">
            <div>
                <div class="in-kicker">Centro de comunicación</div>
                <h1 class="in-title">Comunicados institucionales</h1>
                <p class="in-sub">Notificaciones a {{ $miembrosActivos }} miembros activos · In-app, WhatsApp y comunidad</p>
            </div>
        </section>

        <div class="in-grid-12">
            <div class="in-span-5">
                <x-institucional.card title="Nuevo comunicado">
                    <form method="POST" action="{{ route('institucional.comunicacion.store') }}">
                        @csrf
                        <div class="in-form-grid" style="grid-template-columns:1fr">
                            <div class="in-field"><label>Título</label><input type="text" name="titulo" required maxlength="200" placeholder="Ej. Reto wellness marzo"></div>
                            <div class="in-field"><label>Mensaje</label><textarea name="mensaje" required maxlength="2000" placeholder="Contenido del aviso para los colaboradores"></textarea></div>
                            <div class="in-field">
                                <label>Canal</label>
                                <select name="canal" required>
                                    <option value="IN_APP">Notificación in-app</option>
                                    <option value="WHATSAPP">WhatsApp (integración)</option>
                                    <option value="COMUNIDAD">Comunidad</option>
                                </select>
                            </div>
                        </div>
                        <div style="margin-top:12px"><button type="submit" class="in-btn">Enviar comunicado</button></div>
                    </form>
                </x-institucional.card>
            </div>
            <div class="in-span-7">
                <x-institucional.card title="Historial de comunicados">
                    @forelse ($comunicados as $com)
                        <div class="in-invite-row">
                            <div>
                                <strong>{{ $com->titulo }}</strong>
                                <div style="font-size:11px;color:var(--in-muted)">{{ \Illuminate\Support\Str::limit($com->mensaje, 80) }}</div>
                            </div>
                            <div style="text-align:right">
                                <span class="in-badge is-teal">{{ $com->canal }}</span>
                                <div style="font-size:10px;color:var(--in-muted);margin-top:4px">{{ $com->destinatarios }} enviados</div>
                            </div>
                        </div>
                    @empty
                        <p class="in-empty">Sin comunicados enviados.</p>
                    @endforelse
                    <div class="in-pagination">{{ $comunicados->links() }}</div>
                </x-institucional.card>
            </div>
        </div>
    </div>
@endsection

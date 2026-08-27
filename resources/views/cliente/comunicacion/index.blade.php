@extends('layouts.cliente')

@section('title', 'Comunidad')

@section('content')
    @php
        $esPresencial = $cliente && strtoupper((string) $cliente->modalidad) === 'PRESENCIAL';
        $totalMensajes = $listaChats->sum(fn ($c) => $c->mensajes_count ?? $c->mensajes()->count());
    @endphp

    <x-cliente.page-head
        kicker="Comunidad"
        title="Centro de mensajes"
        :subtitle="$esPresencial ? 'WhatsApp presencial e hilos con tu equipo FigueFit' : 'Conversaciones con tu coach y soporte'"
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.notificaciones.index') }}">Avisos</a>
        </x-slot:actions>
    </x-cliente.page-head>

    <div class="cv-metrics">
        <x-cliente.metric
            label="Conversaciones"
            :value="$listaChats->count()"
            hint="Hilos disponibles en tu cuenta"
        />
        <x-cliente.metric
            label="Mensajes"
            :value="$totalMensajes"
            hint="Total acumulado en todos los chats"
        />
        <x-cliente.metric
            label="Invitaciones WA"
            :value="$invitaciones->count()"
            :hint="$invitaciones->isNotEmpty() ? 'Grupos presenciales pendientes' : 'Sin invitaciones pendientes'"
        />
        <x-cliente.metric
            label="Modalidad"
            :value="$cliente?->modalidad ?? '—'"
            hint="Tipo de membresía activa"
        />
    </div>

    @if ($invitaciones->isNotEmpty())
        <x-cliente.card title="Invitaciones WhatsApp" subtitle="Únete al grupo de tu horario presencial">
            <div class="cv-list-feed">
                @foreach ($invitaciones as $inv)
                    <article class="cv-feed-item">
                        <div class="cv-tile__ico is-pink" aria-hidden="true">WA</div>
                        <div>
                            <strong>Grupo presencial disponible</strong>
                            <p style="margin:6px 0 0;color:var(--cv-muted);font-size:13px;line-height:1.45">
                                {{ $inv->grupoWa?->nombre ?? 'Tu horario tiene un canal de WhatsApp para avisos y coordinación.' }}
                            </p>
                            @if ($inv->mostrada_en)
                                <small style="display:block;margin-top:6px;color:var(--cv-faint);font-size:11px">
                                    Invitación · {{ $inv->mostrada_en->format('d/m/Y H:i') }}
                                </small>
                            @endif
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end">
                            <form method="POST" action="{{ route('cliente.comunicacion.whatsapp.aceptar', $inv->id_invitacion_wa) }}">
                                @csrf
                                <button class="cv-btn cv-btn-sm" type="submit">Unirme</button>
                            </form>
                            <form method="POST" action="{{ route('cliente.comunicacion.whatsapp.cerrar', $inv->id_invitacion_wa) }}">
                                @csrf
                                <button class="cv-btn cv-btn-ghost cv-btn-sm" type="submit">Ahora no</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        </x-cliente.card>
    @endif

    <x-cliente.card title="Mis conversaciones" subtitle="Abre un hilo para continuar el chat">
        <div class="cv-chat-list" style="margin:-18px;border:0;border-radius:0 0 var(--cv-radius) var(--cv-radius);background:transparent">
            @forelse ($listaChats as $chat)
                @php
                    $nombre = $chat->nombre ?: ('Chat #'.$chat->id_chat);
                    $mensajesCount = $chat->mensajes_count ?? $chat->mensajes()->count();
                    $partes = preg_split('/\s+/u', trim($nombre)) ?: [];
                    $iniciales = collect($partes)->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('');
                    $tipoBadge = str_contains(strtoupper((string) $chat->tipo), 'COACH') ? 'is-cyan' : 'is-pink';
                @endphp
                <a class="cv-chat-item" href="{{ route('cliente.comunicacion.show', $chat->id_chat) }}">
                    <div class="cv-avatar" style="width:42px;height:42px;border-radius:12px;font-size:13px">
                        {{ $iniciales ?: '💬' }}
                    </div>
                    <div>
                        <strong>{{ $nombre }}</strong>
                        <p>
                            {{ $chat->tipo }}
                            · {{ $mensajesCount }} {{ $mensajesCount === 1 ? 'mensaje' : 'mensajes' }}
                        </p>
                    </div>
                    <span class="cv-badge {{ $tipoBadge }}">{{ $chat->tipo }}</span>
                </a>
            @empty
                <div class="cv-empty">
                    Aún no tienes conversaciones.
                    @if (! $esPresencial)
                        Se crean al activar tu plan virtual.
                    @else
                        Revisa las invitaciones de WhatsApp o contacta a recepción.
                    @endif
                </div>
            @endforelse
        </div>
    </x-cliente.card>
@endsection

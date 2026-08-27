@extends('layouts.cliente')

@section('title', $chat->nombre ?: ('Chat #'.$chat->id_chat))

@section('content')
    @php
        $authId = auth()->user()?->id_user ?? auth()->id();
        $tituloChat = $chat->nombre ?: ('Chat #'.$chat->id_chat);
    @endphp

    <x-cliente.page-head
        kicker="Conversación"
        :title="$tituloChat"
        :subtitle="$chat->tipo.' · Estado '.$chat->estado"
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.comunicacion.index') }}">← Conversaciones</a>
            @if ($chat->estado === 'ACTIVO')
                <span class="cv-badge is-ok">Activo</span>
            @else
                <span class="cv-badge">{{ $chat->estado }}</span>
            @endif
        </x-slot:actions>
    </x-cliente.page-head>

    <div class="cv-chat-layout" style="grid-template-columns:1fr;min-height:480px">
        <div class="cv-chat-thread">
            <div class="cv-card__head" style="padding:14px 16px;border-bottom:1px solid var(--cv-line)">
                <div>
                    <h3 style="margin:0;font-size:15px;font-weight:800">{{ $tituloChat }}</h3>
                    <p style="margin:4px 0 0;color:var(--cv-faint);font-size:12px">
                        {{ $chat->tipo }} · {{ $mensajes->count() }} {{ $mensajes->count() === 1 ? 'mensaje' : 'mensajes' }}
                    </p>
                </div>
                <span class="cv-badge {{ $chat->estado === 'ACTIVO' ? 'is-ok' : '' }}">{{ $chat->estado }}</span>
            </div>

            <div class="cv-bubble-wrap">
                @forelse ($mensajes as $mensaje)
                    @php
                        $isMe = (int) $mensaje->id_user_emisor === (int) $authId;
                        $nombreEmisor = trim(($mensaje->emisor?->nombres ?? '').' '.($mensaje->emisor?->apellidos ?? ''));
                    @endphp
                    <article @class(['cv-bubble', 'is-me' => $isMe])>
                        @unless ($isMe)
                            <strong>{{ $nombreEmisor ?: 'Participante' }}</strong>
                        @endunless
                        <p>{{ $mensaje->contenido }}</p>
                        <time datetime="{{ optional($mensaje->enviado_en)->toIso8601String() }}">
                            {{ optional($mensaje->enviado_en)->format('d/m/Y H:i') }}
                        </time>
                    </article>
                @empty
                    <div class="cv-empty">Sin mensajes todavía. Escribe el primero abajo.</div>
                @endforelse
            </div>

            @if ($chat->estado === 'ACTIVO')
                <form
                    class="cv-composer"
                    method="POST"
                    action="{{ route('cliente.comunicacion.mensajes.store', $chat->id_chat) }}"
                >
                    @csrf
                    <textarea
                        name="contenido"
                        rows="2"
                        required
                        maxlength="5000"
                        placeholder="Escribe tu mensaje…"
                    >{{ old('contenido') }}</textarea>
                    <button class="cv-btn cv-btn-sm" type="submit">Enviar</button>
                </form>
            @else
                <div class="cv-empty" style="border-top:1px solid var(--cv-line);margin:0">
                    Este chat está cerrado. No puedes enviar nuevos mensajes.
                </div>
            @endif
        </div>
    </div>
@endsection

@extends('layouts.admin')

@section('title', $chat->nombre ?: ('Chat #'.$chat->id_chat))
@section('subtitle', $chat->tipo.' · '.($chat->plan?->nombre ?: 'Sin plan'))

@section('actions')
    <span class="ad-chip {{ $chat->estado === 'ACTIVO' ? 'is-ok' : 'is-off' }}">{{ $chat->estado }}</span>
    <form method="POST" action="{{ route('admin.comunidad.update', $chat->id_chat) }}" style="display:inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="estado" value="{{ $chat->estado === 'ACTIVO' ? 'CERRADO' : 'ACTIVO' }}">
        <button class="ad-btn ad-btn-ghost" type="submit">
            {{ $chat->estado === 'ACTIVO' ? 'Cerrar chat' : 'Reabrir chat' }}
        </button>
    </form>
    <a class="ad-btn ad-btn-ghost" href="{{ route('admin.comunidad.index') }}">Volver</a>
@endsection

@section('content')
    <section class="ad-chat-shell" style="grid-template-columns:1fr">
        <div class="ad-chat-main">
            <header class="ad-chat-head">
                <div>
                    <strong>{{ $chat->nombre ?: ('Chat #'.$chat->id_chat) }}</strong>
                    <em class="ad-user-mail">{{ $chat->coach?->usuario?->nombres }} {{ $chat->coach?->usuario?->apellidos }}</em>
                </div>
            </header>
            <div class="ad-chat-bubbles">
                @forelse ($mensajes as $mensaje)
                    <article class="ad-bubble {{ $loop->even ? 'out' : 'in' }}">
                        <strong>{{ $mensaje->emisor?->nombres }} {{ $mensaje->emisor?->apellidos }}</strong>
                        <span>{{ $mensaje->contenido }}</span>
                        <em class="ad-user-mail">{{ optional($mensaje->enviado_en)->format('d/m/Y H:i') }}</em>
                        <form method="POST" action="{{ route('admin.comunidad.mensajes.destroy', [$chat->id_chat, $mensaje->id_mensaje]) }}" onsubmit="return confirm('¿Eliminar mensaje?')" style="margin-top:6px">
                            @csrf
                            @method('DELETE')
                            <button class="ad-btn ad-btn-ghost" type="submit">Eliminar</button>
                        </form>
                    </article>
                @empty
                    <div class="ad-bubble in">Sin mensajes en este chat.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

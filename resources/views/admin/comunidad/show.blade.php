@extends('layouts.admin')

@section('title', 'Chat #'.$chat->id_chat)
@section('subtitle', $chat->nombre ?: $chat->tipo)

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <div>
                <strong>{{ $chat->nombre ?: ('Chat #'.$chat->id_chat) }}</strong>
                <em class="ad-user-mail">{{ $chat->tipo }} · {{ $chat->plan?->nombre }}</em>
            </div>
            <a class="ad-btn ad-btn-ghost" href="{{ route('admin.comunidad.index') }}">Volver</a>
        </div>

        <div style="display:grid;gap:10px">
            @forelse ($mensajes as $mensaje)
                <article style="padding:12px;border:1px solid var(--line);border-radius:12px">
                    <strong>{{ $mensaje->emisor?->nombres }} {{ $mensaje->emisor?->apellidos }}</strong>
                    <em class="ad-user-mail">{{ optional($mensaje->enviado_en)->format('d/m/Y H:i') }}</em>
                    <p style="margin:8px 0 0">{{ $mensaje->contenido }}</p>
                </article>
            @empty
                <p class="ad-empty">Sin mensajes en este chat.</p>
            @endforelse
        </div>
    </section>
@endsection

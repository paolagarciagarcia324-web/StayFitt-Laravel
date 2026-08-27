@extends('layouts.admin')

@section('title', 'Chat #'.$chat->id_chat)
@section('subtitle', $chat->nombre ?: $chat->tipo)

@section('actions')
    <span class="ad-chip {{ $chat->estado === 'ACTIVO' ? 'is-ok' : 'is-off' }}">{{ $chat->estado }}</span>
    <a class="ad-btn ad-btn-ghost" href="{{ route('admin.comunidad.index') }}">Volver</a>
@endsection

@section('content')
    <x-admin.panel title="{{ $chat->nombre ?: ('Chat #'.$chat->id_chat) }}" subtitle="{{ $chat->tipo }} · {{ $chat->plan?->nombre }}">
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.comunidad.update', $chat->id_chat) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="estado" value="{{ $chat->estado === 'ACTIVO' ? 'CERRADO' : 'ACTIVO' }}">
                <button class="ad-btn ad-btn-ghost" type="submit">
                    {{ $chat->estado === 'ACTIVO' ? 'Cerrar chat' : 'Reabrir chat' }}
                </button>
            </form>
        </x-slot:actions>

        <div class="ad-stack" style="max-height:min(480px,60vh);overflow:auto;padding-right:4px">
            @forelse ($mensajes as $mensaje)
                <article class="ad-tile" style="min-height:0;padding:12px 14px">
                    <div class="ad-tile__top">
                        <div>
                            <strong>{{ $mensaje->emisor?->nombres }} {{ $mensaje->emisor?->apellidos }}</strong>
                            <em class="ad-user-mail">{{ optional($mensaje->enviado_en)->format('d/m/Y H:i') }}</em>
                        </div>
                        <form method="POST" action="{{ route('admin.comunidad.mensajes.destroy', [$chat->id_chat, $mensaje->id_mensaje]) }}" onsubmit="return confirm('¿Eliminar mensaje?')">
                            @csrf
                            @method('DELETE')
                            <button class="ad-btn ad-btn-ghost" type="submit">Eliminar</button>
                        </form>
                    </div>
                    <p>{{ $mensaje->contenido }}</p>
                </article>
            @empty
                <p class="ad-empty">Sin mensajes en este chat.</p>
            @endforelse
        </div>
    </x-admin.panel>
@endsection

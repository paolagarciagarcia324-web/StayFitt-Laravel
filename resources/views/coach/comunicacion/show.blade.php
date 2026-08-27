@extends('layouts.coach')

@section('title', $chat->nombre ?: ('Chat #'.$chat->id_chat))
@section('subtitle', 'Conversación con cliente')

@section('actions')
    <a class="ad-btn ad-btn-ghost" href="{{ route('coach.comunicacion.index') }}">Volver</a>
@endsection

@section('content')
    <section class="ad-card ad-users">
        <div style="display:grid;gap:10px;max-height:420px;overflow-y:auto;margin-bottom:16px">
            @forelse ($mensajes as $mensaje)
                <article style="padding:12px;border:1px solid var(--line);border-radius:12px">
                    <strong>{{ $mensaje->emisor?->nombres }} {{ $mensaje->emisor?->apellidos }}</strong>
                    <em class="ad-user-mail">{{ optional($mensaje->enviado_en)->format('d/m/Y H:i') }}</em>
                    <p style="margin:8px 0 0">{{ $mensaje->contenido }}</p>
                </article>
            @empty
                <p class="ad-empty">Sin mensajes aún.</p>
            @endforelse
        </div>

        @if ($chat->estado === 'ACTIVO')
            <form class="ad-form" method="POST" action="{{ route('coach.comunicacion.mensajes.store', $chat->id_chat) }}">
                @csrf
                <label>
                    <span>Responder</span>
                    <textarea name="contenido" rows="3" required maxlength="5000">{{ old('contenido') }}</textarea>
                </label>
                <button class="ad-btn" type="submit">Enviar</button>
            </form>
        @else
            <p class="ad-empty">Chat cerrado por moderación.</p>
        @endif
    </section>
@endsection

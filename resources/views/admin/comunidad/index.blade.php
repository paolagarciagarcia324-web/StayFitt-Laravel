@extends('layouts.admin')

@section('title', 'Comunidad')
@section('subtitle', 'Chats internos entre coach, cliente y staff')

@section('content')
    <section class="ad-chat-shell">
        <aside class="ad-chat-side">
            <strong style="padding:4px 6px">Conversaciones</strong>
            @forelse ($chats as $chat)
                <a class="ad-chat-item" href="{{ route('admin.comunidad.show', $chat->id_chat) }}" style="text-decoration:none;color:inherit">
                    <strong>{{ $chat->nombre ?: ('Chat #'.$chat->id_chat) }}</strong>
                    <em class="ad-user-mail">{{ $chat->tipo }} · {{ $chat->mensajes_count }} mensajes</em>
                </a>
            @empty
                <p class="ad-empty">Aún no hay chats internos.</p>
            @endforelse
            <div style="margin-top:auto">{{ $chats->links() }}</div>
        </aside>
        <div class="ad-chat-main">
            <header class="ad-chat-head">
                <div>
                    <strong>Moderación StayFit</strong>
                    <em class="ad-user-mail">Elige un chat a la izquierda para leer y cerrar hilos</em>
                </div>
            </header>
            <div class="ad-chat-bubbles">
                @php $preview = $chats->first(); @endphp
                @if ($preview)
                    <div class="ad-bubble in">Último hilo: {{ $preview->nombre ?: ('Chat #'.$preview->id_chat) }} · {{ $preview->tipo }}</div>
                    <div class="ad-bubble out">Coach {{ $preview->coach?->usuario?->nombres }} · Plan {{ $preview->plan?->nombre ?: '—' }}</div>
                    <div class="ad-bubble in">{{ $preview->mensajes_count }} mensajes · {{ $preview->estado }}</div>
                @else
                    <div class="ad-bubble in">Cuando un coach abra un chat con un cliente, aparecerá aquí para supervisión.</div>
                @endif
            </div>
            <div class="ad-chat-compose">
                <input type="text" disabled placeholder="La moderación se hace dentro de cada hilo">
            </div>
        </div>
    </section>
@endsection

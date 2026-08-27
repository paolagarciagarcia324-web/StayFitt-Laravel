@extends('layouts.coach')

@section('title', 'Comunicación')

@section('content')
    <div class="ch-page">
        <div class="ch-kicker">Coach · Cliente</div>
        <h1 class="ch-title">Comunicación</h1>
        <p class="ch-sub">Chats activos con tus clientes y consultas del plan.</p>

        @if ($listaChats->isEmpty())
            <x-coach.card title="Sin conversaciones">
                <p class="ch-empty">Los chats se crean al abrir el expediente de un cliente o cuando el cliente escribe desde su panel.</p>
                <a class="ch-btn" href="{{ route('coach.clientes.index') }}">Ir a mis clientes</a>
            </x-coach.card>
        @else
            <div class="ch-clients">
                @foreach ($listaChats as $chat)
                    <article class="ch-client">
                        <div class="ch-client__top">
                            <div class="ch-avatar">💬</div>
                            <div class="ch-client__meta">
                                <strong>{{ $chat->nombre ?: ('Chat #'.$chat->id_chat) }}</strong>
                                <small>{{ $chat->plan?->nombre ?: 'Sin plan vinculado' }}</small>
                            </div>
                        </div>
                        <span class="ch-badge is-cyan">{{ $chat->mensajes_count ?? 0 }} mensajes</span>
                        <div class="ch-client__actions">
                            <a class="ch-btn" style="padding:8px 14px;font-size:11px" href="{{ route('coach.comunicacion.show', $chat->id_chat) }}">Abrir chat</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection

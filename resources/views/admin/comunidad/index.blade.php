@extends('layouts.admin')

@section('title', 'Comunidad')
@section('subtitle', 'Chats internos')

@section('content')
    <section class="ad-card ad-users">
        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>Chat</th>
                    <th>Tipo</th>
                    <th>Coach / Plan</th>
                    <th>Mensajes</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($chats as $chat)
                    <tr>
                        <td><strong>{{ $chat->nombre ?: ('Chat #'.$chat->id_chat) }}</strong></td>
                        <td>{{ $chat->tipo }}</td>
                        <td>
                            {{ $chat->coach?->usuario?->nombres }} {{ $chat->coach?->usuario?->apellidos }}
                            <em class="ad-user-mail">{{ $chat->plan?->nombre }}</em>
                        </td>
                        <td>{{ $chat->mensajes_count }}</td>
                        <td><span class="ad-badge ad-badge-ok">{{ $chat->estado }}</span></td>
                        <td><a class="ad-btn ad-btn-ghost" href="{{ route('admin.comunidad.show', $chat->id_chat) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ad-empty">No hay chats registrados todavía.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ad-pagination">{{ $chats->links() }}</div>
    </section>
@endsection

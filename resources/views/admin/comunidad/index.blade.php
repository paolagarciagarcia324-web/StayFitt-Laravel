@extends('layouts.admin')

@section('title', 'Comunidad')
@section('subtitle', 'Supervisión de chats internos')

@section('content')
    <x-admin.panel title="Chats registrados" subtitle="Moderación y seguimiento de conversaciones" flush>
        <div class="ad-table-wrap">
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
                            <td><span class="ad-chip">{{ $chat->tipo }}</span></td>
                            <td>
                                {{ $chat->coach?->usuario?->nombres }} {{ $chat->coach?->usuario?->apellidos }}
                                <em class="ad-user-mail">{{ $chat->plan?->nombre }}</em>
                            </td>
                            <td>{{ $chat->mensajes_count }}</td>
                            <td>
                                <span class="ad-badge {{ $chat->estado === 'ACTIVO' ? 'ad-badge-ok' : 'ad-badge-bad' }}">{{ $chat->estado }}</span>
                            </td>
                            <td>
                                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.comunidad.show', $chat->id_chat) }}">Abrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="ad-empty">No hay chats registrados todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ad-panel__foot" style="justify-content:center">
            {{ $chats->links() }}
        </div>
    </x-admin.panel>
@endsection

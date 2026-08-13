@extends('layouts.admin')

@section('title', 'Coaches')
@section('subtitle', 'Perfiles coach')

@section('content')
    <section class="ad-card">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Coach</th>
                    <th>Especialidad</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coaches as $c)
                    <tr>
                        <td>{{ $c->usuario?->nombres }} {{ $c->usuario?->apellidos }}</td>
                        <td>{{ $c->especialidad ?: '—' }}</td>
                        <td><span class="ad-badge ad-badge-ok">{{ $c->estado_coach }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="ad-empty">No hay coaches registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ad-pagination">{{ $coaches->links() }}</div>
    </section>
@endsection

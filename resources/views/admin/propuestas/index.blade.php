@extends('layouts.admin')

@section('title', 'Propuestas de plan')
@section('subtitle', 'Contenido del coach listo para publicar en el catálogo')

@section('content')
    <form class="ad-toolbar ad-glass-card" method="GET" style="margin-bottom:16px">
        <input type="search" name="q" value="{{ $q }}" placeholder="Buscar título o coach…">
        <select name="estado">
            <option value="TODOS" @selected($estado === 'TODOS')>Todos</option>
            @foreach ([
                'PENDIENTE_REVISION' => 'Pendiente revisión',
                'BORRADOR_ENTRENAMIENTO' => 'Borrador entrenamiento',
                'BORRADOR_NUTRICION' => 'Borrador nutrición',
                'RECHAZADO' => 'Rechazado',
                'PUBLICADO' => 'Publicado',
            ] as $val => $label)
                <option value="{{ $val }}" @selected($estado === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="ad-btn" type="submit">Filtrar</button>
        @if ($pendientes > 0)
            <span class="ad-tag">{{ $pendientes }} pendientes</span>
        @endif
    </form>

    <div class="ad-carousel" data-carousel>
        <button class="ad-carousel-btn" type="button" data-carousel-dir="prev" aria-label="Anterior">‹</button>
    <div class="ad-catalog">
        @forelse ($propuestas as $p)
            @php
                $coach = trim(($p->coach?->usuario?->nombres).' '.($p->coach?->usuario?->apellidos));
            @endphp
            <article class="ad-catalog-card">
                <div class="ad-cat-cover ad-prop-cover">
                    <span>{{ $p->etiquetaEstado() }}</span>
                </div>
                <div class="ad-cat-body">
                    <strong>{{ $p->titulo_interno }}</strong>
                    <em class="ad-user-mail">{{ $coach ?: 'Coach sin nombre' }}</em>
                    <p class="ad-user-mail">Enviado {{ $p->enviado_en?->format('d/m/Y H:i') ?: '—' }}</p>
                    <div class="ad-people-actions">
                        <a class="ad-btn" href="{{ route('admin.propuestas.show', $p) }}">Revisar</a>
                    </div>
                </div>
            </article>
        @empty
            <p class="ad-empty">No hay propuestas con ese filtro.</p>
            @endforelse
    </div>
        <button class="ad-carousel-btn" type="button" data-carousel-dir="next" aria-label="Siguiente">›</button>
    </div>
@endsection

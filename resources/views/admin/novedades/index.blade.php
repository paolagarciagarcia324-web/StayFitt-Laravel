@extends('layouts.admin')

@section('title', 'Novedades')
@section('subtitle', 'Anuncios del welcome: foto, plan y vigencia')

@section('actions')
    <a class="ad-btn" href="{{ route('admin.novedades.create') }}">Nueva novedad</a>
@endsection

@section('content')
    <div class="ad-carousel" data-carousel>
        <button class="ad-carousel-btn" type="button" data-carousel-dir="prev" aria-label="Anterior">‹</button>
    <div class="ad-catalog">
        @forelse ($novedades as $n)
            <article class="ad-catalog-card">
                <div class="ad-cat-cover">
                    @if ($n->imagenSrc())
                        <img src="{{ $n->imagenSrc() }}" alt="{{ $n->titulo }}">
                    @endif
                </div>
                <div class="ad-cat-body">
                    <span class="ad-badge {{ $n->activo ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $n->activo ? 'En welcome' : 'Oculta' }}</span>
                    <strong>{{ $n->titulo }}</strong>
                    <em class="ad-user-mail">{{ $n->etiquetaTipo() }} · orden {{ $n->orden }}</em>
                    <p class="ad-user-mail">{{ $n->subtitulo }}</p>
                    <div class="ad-people-actions">
                        <a class="ad-btn" href="{{ route('admin.novedades.edit', $n) }}">Editar</a>
                        <form method="POST" action="{{ route('admin.novedades.destroy', $n) }}" onsubmit="return confirm('¿Eliminar novedad?')">
                            @csrf
                            @method('DELETE')
                            <button class="ad-btn ad-btn-ghost" type="submit">Eliminar</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="ad-empty">Crea una novedad con foto para el welcome.</p>
            @endforelse
    </div>
        <button class="ad-carousel-btn" type="button" data-carousel-dir="next" aria-label="Siguiente">›</button>
    </div>
@endsection

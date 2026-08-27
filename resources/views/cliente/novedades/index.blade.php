@extends('layouts.cliente')

@section('content')
    <x-cliente.page-head
        kicker="Comunidad"
        title="Novedades"
        subtitle="Anuncios, lanzamientos y actualizaciones de FigueFit."
    />

    @if ($novedades->isNotEmpty())
        <div class="cv-media-grid">
            @foreach ($novedades as $nov)
                @php $img = $nov->imagenSrc(); @endphp
                <article class="cv-media">
                    <div class="cv-media__cover">
                        @if ($img)
                            <img src="{{ $img }}" alt="{{ $nov->titulo }}" loading="lazy">
                        @endif
                        <div class="cv-media__cover-meta">
                            <span class="cv-badge is-pink">{{ $nov->etiquetaTipo() }}</span>
                        </div>
                    </div>
                    <div class="cv-media__body">
                        <h3>{{ $nov->titulo }}</h3>
                        @if ($nov->subtitulo)
                            <p><strong>{{ $nov->subtitulo }}</strong></p>
                        @endif
                        @if ($nov->descripcion)
                            <p>{{ \Illuminate\Support\Str::limit($nov->descripcion, 160) }}</p>
                        @endif
                        <a class="cv-btn cv-btn-sm" href="{{ $nov->destinoUrl() }}">{{ $nov->boton_texto ?: 'Ver más' }}</a>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($novedades->hasPages())
            <div style="margin-top:8px">{{ $novedades->links() }}</div>
        @endif
    @else
        <x-cliente.card title="Sin novedades" subtitle="Vuelve pronto para ver las últimas actualizaciones">
            <p class="cv-empty">No hay novedades visibles en este momento.</p>
        </x-cliente.card>
    @endif
@endsection

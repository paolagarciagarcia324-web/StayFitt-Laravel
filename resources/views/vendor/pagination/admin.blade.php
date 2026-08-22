@if ($paginator->hasPages())
    <nav class="ad-pagination" role="navigation" aria-label="Paginación">
        <ul class="ad-page-list">
            @if ($paginator->onFirstPage())
                <li><span class="ad-page-btn is-disabled" aria-disabled="true">&lt;</span></li>
            @else
                <li><a class="ad-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lt;</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="ad-page-btn is-ellipsis">…</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="ad-page-btn is-active" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a class="ad-page-btn" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a class="ad-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">&gt;</a></li>
            @else
                <li><span class="ad-page-btn is-disabled" aria-disabled="true">&gt;</span></li>
            @endif
        </ul>
    </nav>
@endif

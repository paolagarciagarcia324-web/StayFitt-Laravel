@php
    $action = $action ?? url()->current();
    $name = $name ?? 'q';
    $value = $value ?? request($name, '');
    $placeholder = $placeholder ?? 'Buscar…';
    $live = ! empty($live);
    $clearQuery = request()->except([$name, 'page']);
    $clearUrl = $action.(count($clearQuery) ? ('?'.http_build_query($clearQuery)) : '');
    $hasQuery = filled($value);
@endphp
<form class="ad-users-search" method="GET" action="{{ $action }}" data-admin-search @if ($live) data-admin-search-live @endif>
    @foreach ($clearQuery as $key => $val)
        @if (! is_array($val))
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endif
    @endforeach
    <span class="ad-users-search-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'search'])</span>
    <input type="search" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}" data-admin-search-input autocomplete="off">
    <a
        class="ad-users-search-clear"
        href="{{ $clearUrl }}"
        title="Limpiar búsqueda"
        aria-label="Limpiar búsqueda"
        data-admin-search-clear
        @if (! $hasQuery) hidden @endif
    >
        @include('partials.admin-icon', ['icon' => 'close'])
    </a>
</form>

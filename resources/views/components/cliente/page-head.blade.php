@props([
    'title',
    'subtitle' => null,
    'kicker' => null,
])

<header class="cv-page-head">
    <div>
        @if ($kicker)
            <div class="cv-kicker">{{ $kicker }}</div>
        @endif
        <h1 class="cv-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="cv-sub">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="cv-page-head__actions">{{ $actions }}</div>
    @endisset
</header>

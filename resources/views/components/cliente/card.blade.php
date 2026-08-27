@props([
    'title',
    'subtitle' => null,
])

<section {{ $attributes->class(['cv-card']) }}>
    <div class="cv-card__head">
        <div>
            <h3>{{ $title }}</h3>
            @if ($subtitle)
                <p>{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div>{{ $actions }}</div>
        @endisset
    </div>
    <div class="cv-card__body">
        {{ $slot }}
    </div>
</section>

@props([
    'title',
    'subtitle' => null,
    'flush' => false,
])

<section {{ $attributes->merge(['class' => 'ad-panel']) }}>
    <div class="ad-panel__head">
        <div>
            <h2 class="ad-panel__title">{{ $title }}</h2>
            @if ($subtitle)
                <p class="ad-panel__sub">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="ad-panel__actions">{{ $actions }}</div>
        @endisset
    </div>
    <div class="{{ $flush ? 'ad-panel__body ad-panel__body--flush' : 'ad-panel__body' }}">
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="ad-panel__foot">{{ $footer }}</div>
    @endisset
</section>

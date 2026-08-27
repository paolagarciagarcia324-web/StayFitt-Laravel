@props(['title', 'subtitle' => null])
<section {{ $attributes->class(['in-card']) }}>
    <div class="in-card__head">
        <div>
            <h2>{{ $title }}</h2>
            @if ($subtitle)<p>{{ $subtitle }}</p>@endif
        </div>
        @isset($actions)<div>{{ $actions }}</div>@endisset
    </div>
    <div class="in-card__body">{{ $slot }}</div>
</section>

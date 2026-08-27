@props(['title', 'subtitle' => null])
<section {{ $attributes->class(['st-card']) }}>
    <div class="st-card__head">
        <div>
            <h2>{{ $title }}</h2>
            @if ($subtitle)<p>{{ $subtitle }}</p>@endif
        </div>
        @isset($actions)<div>{{ $actions }}</div>@endisset
    </div>
    <div class="st-card__body">{{ $slot }}</div>
</section>

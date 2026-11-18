@props([
    'label',
    'value',
    'tone' => 'cyan',
    'hint' => null,
])

@php
    $numeric = is_numeric($value) ? (float) $value : null;
@endphp
<article {{ $attributes->class(['ad-statx', 'is-'.$tone]) }}>
    <i class="ad-statx-orb" aria-hidden="true"></i>
    <div>
        @if ($numeric !== null)
            <strong data-count="{{ (int) $numeric }}">{{ $value }}</strong>
        @else
            <strong>{{ $value }}</strong>
        @endif
        <span>{{ $label }}</span>
        @if ($hint)
            <em>{{ $hint }}</em>
        @endif
    </div>
</article>

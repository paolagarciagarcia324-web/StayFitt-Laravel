@props([
    'label',
    'value',
    'hint' => null,
])

<div {{ $attributes->class(['cv-metric']) }}>
    <span>{{ $label }}</span>
    <strong>{{ $value }}</strong>
    @if ($hint)
        <small>{{ $hint }}</small>
    @endif
</div>

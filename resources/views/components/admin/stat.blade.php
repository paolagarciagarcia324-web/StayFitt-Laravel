@props([
    'label',
    'value',
    'tone' => null,
])

<div {{ $attributes->class(['ad-stat', $tone ? 'is-'.$tone : null]) }}>
    <strong>{{ $value }}</strong>
    <span>{{ $label }}</span>
</div>

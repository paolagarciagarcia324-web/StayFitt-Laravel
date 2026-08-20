@php
    $icon = $icon ?? 'arrow';
    $href = $href ?? null;
    $type = $type ?? 'submit';
    $class = trim('ff-cta '.($class ?? ''));
    $label = $label ?? '';
    $attrs = $attrs ?? [];
    $tag = $href ? 'a' : 'button';
@endphp
@if ($tag === 'a')
<a class="{{ $class }}" href="{{ $href }}" @foreach ($attrs as $k => $v) {{ $k }}="{{ $v }}" @endforeach>
@else
<button class="{{ $class }}" type="{{ $type }}">
@endif
    <span class="ff-cta-badge" aria-hidden="true">
        @switch($icon)
            @case('login')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M5.5 19.2c.8-3.2 3.4-5 6.5-5s5.7 1.8 6.5 5"/></svg>
                @break
            @case('servicios')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 8h10M7 16h10"/><path d="M9 6v4M15 6v4M9 14v4M15 14v4"/></svg>
                @break
            @case('planes')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M8 9h8M8 13h8M8 17h5"/></svg>
                @break
            @case('comprar')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 8h15l-1.4 8.2a2 2 0 0 1-2 1.6H9.2a2 2 0 0 1-2-1.5L5.2 5H3"/><circle cx="10" cy="20" r="1.2"/><circle cx="17" cy="20" r="1.2"/></svg>
                @break
            @case('whatsapp')
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3.2A8.8 8.8 0 0 0 4.7 16.6L4 20.8l4.3-.7A8.8 8.8 0 1 0 12 3.2Zm4.9 12.5c-.2.6-1.2 1.1-1.9 1.2-.5.1-1.1.2-3.5-.7-3-1.2-4.9-4.2-5-4.4-.2-.2-1.3-1.8-1.3-3.4 0-1.6.8-2.4 1.1-2.7.3-.3.6-.4.8-.4h.6c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.4.6c-.1.2-.3.4-.1.7.2.3.8 1.3 1.7 2.1 1.2 1 2.2 1.3 2.5 1.5.3.1.5.1.7-.1l.9-1.2c.2-.2.4-.2.6-.1l2 .9c.2.1.4.2.4.4 0 .1 0 .7-.2 1.3Z"/></svg>
                @break
            @case('registro')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="8" r="3"/><path d="M4.6 19c.7-3 3-4.7 5.4-4.7 1.2 0 2.3.4 3.3 1.1M17 11v6M14 14h6"/></svg>
                @break
            @case('pago')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                @break
            @default
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        @endswitch
    </span>
    <span class="ff-cta-text">{{ $label }}</span>
@if ($tag === 'a')
</a>
@else
</button>
@endif

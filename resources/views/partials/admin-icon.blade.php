@php
    $icon = $icon ?? 'dot';
@endphp
<svg class="ad-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($icon)
    @case('dashboard')
        <rect x="3.5" y="3.5" width="7" height="7" rx="1.6"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.6"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.6"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.6"/>
        @break
    @case('usuarios')
        <circle cx="9" cy="8" r="3"/><path d="M3.8 19.2c.8-3.1 3-4.8 5.2-4.8s4.4 1.7 5.2 4.8"/><circle cx="17.2" cy="8.2" r="2.2"/><path d="M16.2 13.2h2v2M17.2 12.2v4"/>
        @break
    @case('personas')
        <circle cx="9" cy="7.5" r="2.8"/>
        <path d="M3.5 19c.75-3 2.9-4.7 5.5-4.7S13.75 16 14.5 19"/>
        <circle cx="16.5" cy="8.2" r="2.3"/>
        <path d="M14.2 14.2c1.7-.35 3.5.15 4.7 1.45.7.75 1.15 1.7 1.35 2.7"/>
        @break
    @case('clientes')
        <circle cx="12" cy="8" r="3.2"/><path d="M5.4 19.2c.8-3.2 3.4-5 6.6-5s5.8 1.8 6.6 5"/>
        @break
    @case('clientes-virtual')
        <rect x="3.5" y="5" width="17" height="12" rx="2"/><path d="M8 21h8M12 17v4"/><circle cx="12" cy="11" r="2.2"/>
        @break
    @case('clientes-presencial')
        <circle cx="12" cy="8" r="3"/><path d="M6 19.2c.7-3 2.8-4.7 6-4.7s5.3 1.7 6 4.7"/><path d="M17.6 6.2 19 4.8M19.8 8.4h2M17.6 10.6 19 12"/>
        @break
    @case('instituciones')
        <path d="M4 20V8.5L12 4l8 4.5V20"/><path d="M9 20v-6h6v6M10 10h.01M14 10h.01M12 13h.01"/>
        @break
    @case('coaches')
        <circle cx="12" cy="12" r="8"/><path d="M12 8v4.5l3 1.7"/>
        @break
    @case('planes')
        <rect x="5" y="3.5" width="14" height="17" rx="2"/><path d="M8.5 8h7M8.5 12h7M8.5 16h4.5"/>
        @break
    @case('horarios')
        <circle cx="12" cy="13" r="7"/><path d="M12 9.5V13l2.4 1.6M9 3.5h6"/>
        @break
    @case('eventos')
        <rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3.5v3M16 3.5v3M4 10h16"/><path d="M9 14.2 11 16l4-4"/>
        @break
    @case('pagos')
        <rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18M7 15h4"/>
        @break
    @case('tienda')
        <path d="M6 8h15l-1.4 8.2a2 2 0 0 1-2 1.6H9.2a2 2 0 0 1-2-1.5L5.2 5H3"/><circle cx="10" cy="20" r="1.05"/><circle cx="17" cy="20" r="1.05"/>
        @break
    @case('calendario')
        <rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3.5v3M16 3.5v3M4 10h16M9 14h.01M12 14h.01M15 14h.01M9 17h.01M12 17h.01"/>
        @break
    @case('comunidad')
        <path d="M5 16.5 3.6 20l3.6-1.2"/><path d="M7.2 15.2A6.4 6.4 0 1 1 18.5 12"/><path d="M14.2 17.8a5 5 0 0 0 6.2 1.4L21.8 21l-2.1-.7"/>
        @break
    @case('estadisticas')
        <path d="M4 19V5M4 19h16"/><path d="M8 15v-4M12 15V8M16 15v-6"/>
        @break
    @case('reportes')
        <path d="M7 3.5h7.5L20 9v11.5H7a1.5 1.5 0 0 1-1.5-1.5V5A1.5 1.5 0 0 1 7 3.5Z"/><path d="M14.5 3.5V9H20M9 13h6M9 16.5h4"/>
        @break
    @case('actividad')
        <rect x="7" y="4" width="12" height="16.5" rx="2"/><path d="M9 2.8h8M10.5 11h5M10.5 14.5h3.5"/><path d="M10.2 8.2l1.2 1.2 2.4-2.4"/>
        @break
    @case('bolso')
        <path d="M6 9.2h12l-.7 10.2a2 2 0 0 1-2 1.8H8.7a2 2 0 0 1-2-1.8L6 9.2Z"/><path d="M9 9.2V7.4a3 3 0 0 1 6 0v1.8"/>
        @break
    @case('alerta')
        <path d="M12 4.2 3.8 18.6h16.4L12 4.2Z"/><path d="M12 10v4.2M12 16.8h.01"/>
        @break
    @case('luna')
        <path d="M15.2 4.4A7.6 7.6 0 1 0 19.6 14 6.2 6.2 0 0 1 15.2 4.4Z"/>
        @break
    @case('sol')
        <circle cx="12" cy="12" r="4"/><path d="M12 3.2v1.8M12 19v1.8M4.1 12H2.3M21.7 12h-1.8M5.6 5.6l1.3 1.3M17.1 17.1l1.3 1.3M18.4 5.6l-1.3 1.3M6.9 17.1l-1.3 1.3"/>
        @break
    @case('pasarelas')
        <path d="M4 8h16M7 5h10M6 12h5M6 16h8"/>
        @break
    @case('edit')
        <path d="M12 20h9"/>
        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
        @break
    @case('upload')
        <path d="M12 16V5"/>
        <path d="M8 8.5 12 4.5 16 8.5"/>
        <path d="M5 19h14"/>
        @break
    @case('clock')
        <circle cx="12" cy="12" r="8"/>
        <path d="M12 8v4.2l2.6 1.6"/>
        @break
    @case('cupo')
        <circle cx="9" cy="8.5" r="2.6"/>
        <path d="M4 18.5c.7-2.6 2.4-4 5-4s4.3 1.4 5 4"/>
        <circle cx="16.5" cy="9.5" r="2.1"/>
        <path d="M13.5 18.5c.5-2 1.8-3.2 3-3.2s2.5 1.2 3 3.2"/>
        @break
    @case('fecha')
        <rect x="4" y="5" width="16" height="15" rx="2"/>
        <path d="M8 3.5v3M16 3.5v3M4 10h16"/>
        @break
    @case('search')
        <circle cx="11" cy="11" r="6.5"/>
        <path d="M16.2 16.2 20.5 20.5"/>
        @break
    @case('close')
        <path d="M6.5 6.5 17.5 17.5M17.5 6.5 6.5 17.5"/>
        @break
    @case('chevron-down')
        <path d="M6 9.5 12 15.5 18 9.5"/>
        @break
    @case('trash')
        <path d="M3 6h18"/>
        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
        <path d="M10 11v6M14 11v6"/>
        @break
    @default
        <circle cx="12" cy="12" r="3"/>
@endswitch
</svg>

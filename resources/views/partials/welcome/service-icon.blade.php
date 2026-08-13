@switch($icon)
    @case('entrenamiento')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M6.5 6.5 3 10v4l3.5 3.5M17.5 6.5 21 10v4l-3.5 3.5"/>
            <path d="M8 12h8M10.5 9.5v5M13.5 9.5v5"/>
        </svg>
        @break
    @case('nutricion')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 3c-2 3-4 5-4 8a4 4 0 0 0 8 0c0-3-2-5-4-8Z"/>
            <path d="M12 14v7"/>
        </svg>
        @break
    @case('video')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="6" width="14" height="12" rx="2"/>
            <path d="m17 10 4-2v8l-4-2"/>
        </svg>
        @break
    @case('progreso')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 19V5"/>
            <path d="M4 19h16"/>
            <path d="M8 15V11M12 15V8M16 15v-3"/>
        </svg>
        @break
    @case('agenda')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="4" y="5" width="16" height="16" rx="2"/>
            <path d="M8 3v4M16 3v4M4 10h16"/>
        </svg>
        @break
    @case('comunidad')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M16 11a3 3 0 1 0-6 0"/>
            <path d="M12 14c-4 0-6 2-6 4v1h12v-1c0-2-2-4-6-4Z"/>
            <circle cx="18" cy="8" r="2"/>
            <path d="M20 14c1.5 0 3 .7 3 2v1h-3"/>
        </svg>
        @break
@endswitch

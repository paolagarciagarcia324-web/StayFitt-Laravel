@php
    $steps = [
        1 => ['label' => 'Entrenamiento', 'route' => 'coach.propuestas.entrenamiento'],
        2 => ['label' => 'Nutrición', 'route' => 'coach.propuestas.nutricion'],
        3 => ['label' => 'Resumen', 'route' => 'coach.propuestas.resumen'],
    ];
@endphp
<nav class="ad-wizard" aria-label="Pasos de la propuesta">
    @foreach ($steps as $n => $s)
        <a
            class="ad-wizard-step {{ ($step ?? 0) === $n ? 'is-active' : '' }} {{ ($step ?? 0) > $n ? 'is-done' : '' }}"
            href="{{ route($s['route'], $propuesta) }}"
        >
            <span>{{ $n }}</span>
            {{ $s['label'] }}
        </a>
    @endforeach
</nav>

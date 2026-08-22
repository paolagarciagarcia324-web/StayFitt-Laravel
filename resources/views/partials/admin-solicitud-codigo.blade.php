@php
    $solicitudCodigo = $codigo ?? ($solicitud->codigo_solicitud ?? '');
    $partes = null;
    if (preg_match('/^(Fit)(\d+)$/i', (string) $solicitudCodigo, $m)) {
        $partes = ['Fit', $m[2]];
    }
@endphp
@if ($partes)
    <span class="ad-code"><span class="ad-code-fit">{{ $partes[0] }}</span>{{ $partes[1] }}</span>
@else
    <span class="ad-code">{{ $solicitudCodigo }}</span>
@endif

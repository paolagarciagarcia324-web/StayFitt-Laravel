@php
    $vals = array_values($vals ?? []);
    $max = max(1, ...array_map('floatval', $vals ?: [1]));
    $tone = $tone ?? 'mix';
@endphp
<div class="ad-spark {{ $tone === 'cyan' ? 'is-cyan' : ($tone === 'pink' ? 'is-pink' : '') }}" role="img" aria-hidden="true">
    @foreach ($vals as $v)
        @php $h = max(8, (int) round(((float) $v / $max) * 100)); @endphp
        <i style="--h: {{ $h }}%"></i>
    @endforeach
</div>

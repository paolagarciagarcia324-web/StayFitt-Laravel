@extends('layouts.admin')

@section('title', 'Usos del cupón')
@section('subtitle', $cupon->codigo)

@section('actions')
    <a class="ad-btn ad-btn-ghost" href="{{ route('admin.cupones.index') }}">← Volver a cupones</a>
@endsection

@section('content')
    <section class="ad-card ad-users">
        <p class="ad-muted-note">
            {{ $cupon->tipo ?: $cupon->tipo_descuento }} · {{ $cupon->valor }}{{ ($cupon->tipo ?: $cupon->tipo_descuento) === 'PORCENTAJE' ? '%' : ' COP' }}
            · Usos: {{ $cupon->usos_actuales }}/{{ $cupon->usos_maximos ?: $cupon->uso_maximo ?: '∞' }}
        </p>

        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Correo</th>
                    <th>Solicitud</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usos as $uso)
                    <tr>
                        <td>{{ optional($uso->usado_en)->format('d/m/Y H:i') ?: '—' }}</td>
                        <td>{{ $uso->correo ?: $uso->solicitud?->correo ?: '—' }}</td>
                        <td>
                            @if ($uso->id_solicitud)
                                <a class="ad-link" href="{{ route('admin.solicitudes.show', $uso->id_solicitud) }}">
                                    {{ $uso->solicitud?->codigo_solicitud ?: '#'.$uso->id_solicitud }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="ad-empty">Este cupón aún no tiene usos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $usos->links() }}
    </section>
@endsection

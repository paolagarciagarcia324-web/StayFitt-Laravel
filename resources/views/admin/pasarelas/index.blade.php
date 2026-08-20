@extends('layouts.admin')

@section('title', 'Pasarelas')
@section('subtitle', 'Canales de cobro disponibles en registro y pre-registro')

@section('content')
    <section class="ad-card ad-users">
        <p class="ad-empty" style="margin-bottom:16px">Simulación: el cobro intenta Bold, si falla pasa a ePayco y luego a Mercado Pago. El cliente no ve esta cadena.</p>

        <div class="ad-pas-grid">
            @forelse ($pasarelas as $pasarela)
                <article class="ad-pas-card">
                    <h3>{{ $pasarela->nombre }}</h3>
                    <small>{{ $pasarela->codigo_pasarela }}</small>
                    <p style="margin:10px 0 0">
                        <span class="ad-badge {{ $pasarela->activa ? 'ad-badge-ok' : 'ad-badge-bad' }}">
                            {{ $pasarela->activa ? 'ACTIVA' : 'INACTIVA' }}
                        </span>
                        <span class="ad-pill-days" style="margin-left:6px">{{ $pasarela->modo }}</span>
                    </p>
                    <form class="ad-form" method="POST" action="{{ route('admin.pasarelas.update', $pasarela->id_config_pasarela) }}">
                        @csrf
                        @method('PUT')
                        <label><span>Modo</span>
                            <select name="modo">
                                <option value="SANDBOX" @selected($pasarela->modo === 'SANDBOX')>SANDBOX</option>
                                <option value="LIVE" @selected($pasarela->modo === 'LIVE')>LIVE</option>
                            </select>
                        </label>
                        <label style="display:flex;gap:8px;align-items:center;margin:10px 0">
                            <input type="checkbox" name="activa" value="1" @checked($pasarela->activa)>
                            <span style="margin:0">Disponible en checkout</span>
                        </label>
                        <button class="ad-btn ad-btn-ghost" type="submit">Guardar</button>
                    </form>
                </article>
            @empty
                <p class="ad-empty">No hay pasarelas configuradas.</p>
            @endforelse
        </div>
    </section>
@endsection

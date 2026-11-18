@extends('layouts.admin')

@section('title', 'Pasarelas')
@section('subtitle', 'Canales de cobro: sandbox o live, visibles en el checkout')

@section('content')
    <div class="ad-gate-grid">
        @forelse ($pasarelas as $pasarela)
            @php $logo = $pasarela->logoSrc(); @endphp
            <article class="ad-gate-card">
                <div class="ad-gate-logo">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="{{ $pasarela->nombre }}">
                    @else
                        @include('partials.admin-icon', ['icon' => 'pasarelas'])
                    @endif
                </div>
                <h3>{{ $pasarela->nombre }}</h3>
                <p class="ad-user-mail">{{ $pasarela->codigo_pasarela }} · {{ $pasarela->modo }}</p>
                <span class="ad-badge {{ $pasarela->activa ? 'ad-badge-ok' : 'ad-badge-bad' }}">
                    {{ $pasarela->activa ? 'Disponible' : 'Oculta' }}
                </span>
                <form class="ad-form" method="POST" action="{{ route('admin.pasarelas.update', $pasarela->id_config_pasarela) }}" enctype="multipart/form-data" style="margin-top:12px">
                    @csrf
                    @method('PUT')
                    <label class="ad-ml-upload"><span>Cambiar logo</span><input type="file" name="logo" accept="image/*"></label>
                    <label><span>O URL del logo</span><input type="url" name="logo_url" value="{{ $pasarela->logo_url }}" placeholder="https://..."></label>
                    <label><span>Modo</span>
                        <select name="modo">
                            <option value="SANDBOX" @selected($pasarela->modo === 'SANDBOX')>SANDBOX</option>
                            <option value="LIVE" @selected($pasarela->modo === 'LIVE')>LIVE</option>
                        </select>
                    </label>
                    <label class="ad-check">
                        <input type="checkbox" name="activa" value="1" @checked($pasarela->activa)>
                        <span>Disponible en checkout</span>
                    </label>
                    <button class="ad-btn" type="submit">Guardar</button>
                </form>
            </article>
        @empty
            <div class="ad-empty-hero" style="grid-column:1/-1">
                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'pasarelas'])</span>
                <strong>Sin pasarelas configuradas</strong>
            </div>
        @endforelse
    </div>
@endsection

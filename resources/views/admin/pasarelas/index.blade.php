@extends('layouts.admin')

@section('title', 'Pasarelas')
@section('subtitle', 'Canales de cobro')

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-pas-grid">
            @forelse ($pasarelas as $pasarela)
                @php $logo = $pasarela->logoSrc(); @endphp
                <article class="ad-pas-card">
                    <div class="ad-pas-logo">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="{{ $pasarela->nombre }}">
                        @else
                            <span class="ad-ml-placeholder">@include('partials.admin-icon', ['icon' => 'pasarelas'])</span>
                        @endif
                    </div>
                    <h3>{{ $pasarela->nombre }}</h3>
                    <small>{{ $pasarela->codigo_pasarela }}</small>
                    <p style="margin:10px 0 0">
                        <span class="ad-badge {{ $pasarela->activa ? 'ad-badge-ok' : 'ad-badge-bad' }}">
                            {{ $pasarela->activa ? 'ACTIVA' : 'INACTIVA' }}
                        </span>
                        <span class="ad-pill-days" style="margin-left:6px">{{ $pasarela->modo }}</span>
                    </p>
                    <form class="ad-form" method="POST" action="{{ route('admin.pasarelas.update', $pasarela->id_config_pasarela) }}" enctype="multipart/form-data">
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
                        <label style="display:flex;gap:8px;align-items:center;margin:10px 0">
                            <input type="checkbox" name="activa" value="1" @checked($pasarela->activa)>
                            <span style="margin:0">Disponible en checkout</span>
                        </label>
                        <button class="ad-btn ad-btn-ghost" type="submit">Guardar</button>
                    </form>
                </article>
            @empty
                <div class="ad-empty-hero">
                    <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'pasarelas'])</span>
                    <strong>Sin pasarelas</strong>
                </div>
            @endforelse
        </div>
    </section>
@endsection

@extends('layouts.admin')

@section('title', 'Pasarelas')
@section('subtitle', 'Canales de cobro y modo sandbox/live')

@section('content')
    <div class="ad-grid-3">
        @forelse ($pasarelas as $pasarela)
            @php $logo = $pasarela->logoSrc(); @endphp
            <article class="ad-tile">
                <div class="ad-tile__top">
                    <div class="ad-pas-logo" style="width:48px;height:48px">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="{{ $pasarela->nombre }}" style="max-width:100%;max-height:100%;object-fit:contain">
                        @else
                            <span class="ad-ml-placeholder">@include('partials.admin-icon', ['icon' => 'pasarelas'])</span>
                        @endif
                    </div>
                    <span class="ad-chip {{ $pasarela->activa ? 'is-ok' : 'is-off' }}">
                        {{ $pasarela->activa ? 'ACTIVA' : 'OFF' }}
                    </span>
                </div>
                <div>
                    <h3>{{ $pasarela->nombre }}</h3>
                    <p>{{ $pasarela->codigo_pasarela }} · {{ $pasarela->modo }}</p>
                </div>
                <form class="ad-form" method="POST" action="{{ route('admin.pasarelas.update', $pasarela->id_config_pasarela) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <details class="ad-form-section">
                        <summary>Configurar</summary>
                        <div class="ad-form-section__body">
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
                        </div>
                    </details>
                </form>
            </article>
        @empty
            <div class="ad-empty-hero" style="grid-column:1/-1">
                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'pasarelas'])</span>
                <strong>Sin pasarelas</strong>
            </div>
        @endforelse
    </div>
@endsection

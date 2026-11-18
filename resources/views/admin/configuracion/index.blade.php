@extends('layouts.admin')

@section('title', 'Configuración')
@section('subtitle', 'Parámetros del sistema StayFit')

@section('content')
    <form class="ad-form" method="POST" action="{{ route('admin.configuracion.update') }}">
        @csrf
        @method('PUT')
        <div class="ad-cfg-grid">
            @forelse ($configs as $config)
                <article class="ad-gate-card">
                    <em class="ad-user-mail">{{ $config->clave }}</em>
                    <label>
                        <span>{{ $config->descripcion ?: 'Valor' }}</span>
                        <input type="text" name="valores[{{ $config->id_config }}]" value="{{ old('valores.'.$config->id_config, $config->valor) }}">
                    </label>
                </article>
            @empty
                <p class="ad-empty">No hay parámetros en configuracion_sistema.</p>
            @endforelse
        </div>
        @if ($configs->isNotEmpty())
            <div class="ad-form-actions" style="margin-top:16px">
                <button class="ad-btn" type="submit">Guardar configuración</button>
            </div>
        @endif
    </form>
@endsection

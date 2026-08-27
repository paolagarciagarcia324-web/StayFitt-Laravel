@extends('layouts.admin')

@section('title', 'Configuración')
@section('subtitle', 'Parámetros generales del sistema')

@section('content')
    <x-admin.panel title="Parámetros del sistema" subtitle="Claves de configuracion_sistema" flush>
        <form class="ad-form" method="POST" action="{{ route('admin.configuracion.update') }}">
            @csrf
            @method('PUT')
            <div class="ad-table-wrap">
                <table class="ad-table ad-users-table">
                    <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Valor</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($configs as $config)
                            <tr>
                                <td><code>{{ $config->clave }}</code></td>
                                <td>
                                    <input type="text" name="valores[{{ $config->id_config }}]" value="{{ old('valores.'.$config->id_config, $config->valor) }}" style="width:100%">
                                </td>
                                <td class="ad-user-mail">{{ $config->descripcion ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="ad-empty">No hay parámetros en configuracion_sistema.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($configs->isNotEmpty())
                <div class="ad-panel__foot">
                    <button class="ad-btn" type="submit">Guardar configuración</button>
                </div>
            @endif
        </form>
    </x-admin.panel>
@endsection

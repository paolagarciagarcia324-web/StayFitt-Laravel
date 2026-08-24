@extends('layouts.admin')

@section('title', 'Publicar plan')
@section('subtitle', 'Define nombre, precio e imagen comercial · '.$propuesta->titulo_interno)

@section('content')
    <section class="ad-card">
        <form class="ad-form" method="POST" action="{{ route('admin.propuestas.publicar', $propuesta) }}" enctype="multipart/form-data">
            @csrf
            @php $selected = old('modulos', []); @endphp
            @include('admin.planes._fields', ['plan' => $plan, 'modulos' => $modulos, 'selected' => $selected, 'readonlyCupos' => false])

            <label class="ad-span-2" style="display:block;margin-top:12px">
                Notas internas de revisión
                <textarea name="notas_admin" rows="2">{{ old('notas_admin') }}</textarea>
            </label>

            <div class="ad-modal-actions">
                <button class="ad-btn" type="submit">Publicar en welcome</button>
                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.propuestas.show', $propuesta) }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection

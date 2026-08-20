@extends('layouts.admin')

@section('title', $title)
@section('subtitle', $subtitle)

@section('content')
    <section class="ad-card ad-proximo">
        <div class="ad-proximo-icon">◌</div>
        <h2>{{ $title }}</h2>
        <p>{{ $subtitle }}. Este módulo está reservado en el menú y se conectará en la siguiente iteración del roadmap StayFit.</p>
        <div class="ad-proximo-tags">
            <span>Próximamente</span>
            <span>StayFit Admin</span>
            <span>FigueFit</span>
        </div>
    </section>
@endsection

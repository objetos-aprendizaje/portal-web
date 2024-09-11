@extends('layouts.app')
@section('content')
    <div class="poa-container">

        <h3>Listado de categorías</h3>
        <p class="mb-4">Selecciona las categorías de tu interés para recibir notificaciones sobre cursos o programas formativos relacionados y mantenerte al tanto de todas las novedades.</p>
        @foreach ($categories as $category)
            @include('profile.categories.category', ['category' => $category, 'level' => 0])
        @endforeach

        <div class="flex justify-center">
            <button type="button" class="btn btn-primary-profile mt-[88px]" id="save-categories-btn">Guardar
                {{ e_heroicon('paper-airplane', 'outline') }}
            </button>
        </div>
    </div>
@endsection

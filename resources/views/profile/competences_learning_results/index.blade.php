@extends('layouts.app')
@section('content')
    <div class="poa-container">
        <h3>Competencias y resultados de aprendizaje</h3>

        <p class="mb-4">Selecciona los resultados de aprendizaje de tu interés para recibir notificaciones cada vez que haya un nuevo programa formativo o curso que los contenga.</p>
        <div class="mb-4" id="tree-competences-learning-results"></div>


        <p id="selected-learning-results" class="hidden">Has seleccionado <span id="selected-learning-results-count">0</span> resultados de aprendizaje de 100</p>
        <div class="flex justify-center mt-[84px]">
            <button class="btn btn-primary-profile" id="save-competences-learning-results-btn">Guardar
                {{ e_heroicon('paper-airplane', 'outline') }}
            </button>
        </div>


    </div>

@endsection

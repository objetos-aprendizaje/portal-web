@extends('profile.layouts.app')
@section('content')
    <div class="poa-container">
        <h3>Competencias y resultados de aprendizaje</h3>

        <div  id="tree-competences-learning-results"></div>


        <div class="flex justify-center mt-[84px]">
            <button class="btn btn-primary-profile" id="save-competences-learning-results-btn">Guardar
                {{ e_heroicon('paper-airplane', 'outline') }}
            </button>
        </div>


    </div>

@endsection

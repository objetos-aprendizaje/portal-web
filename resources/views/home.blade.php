@extends('layouts.app')
@section('content')
    <div class="home-img flex flex-col items-center pt-[80px] h-[550px] lg:h-[700px]">
        <div class=" w-[300px] lg:w-[855px] mx-auto flex flex-col gap-[50px] text-center text-white ">
            <h1 class="text-[28px] lg:text-[54px] font-bold">Lorem ipsum dolor sit amet consectetur. Dui at integer vel.</h1>
            <p class="w-[258px] lg:w-[508px] mx-auto font-[400]">Lorem ipsum dolor sit amet consectetur. At integer et
                parturient commodo. Egestas dolor suscipit fringilla senectus.</p>
            <a href="#" class="border mx-auto rounded-[6px] justify-center text-white button-register-home">
                <div class="flex gap-[10px] px-[20px] py-[10px] m-auto">
                    <span>Registrarme</span>
                    {{ e_heroicon('chevron-right', 'outline', 'white') }}
                </div>

            </a>
        </div>
    </div>

    <div class="container mx-auto relative mt-[-160px] mb-6">
        <x-carrousel-component :items="$featured_courses" type="courses"></x-carrousel-component>
    </div>

    <div class=" container mx-auto justify-center items-end flex">
        <div class="w-[333px] md:w-[509px] flex gap-[26px] flex-row flex-wrap	">
            <button id="cursoButton"
                class=" w-[150px] m-auto border rounded-[6px] bg-[--primary-color] text-center justify-center text-white px-[26px] py-[10px] button-register flex items-center gap-[20px]">
                <input type="checkbox" id="miCheckbox" class="form-checkbox" checked>
                Curso
            </button>
            <button id="programaButton"
                class=" w-[150px] m-auto border rounded-[6px] bg-[--primary-color] text-center justify-center text-white px-[26px] py-[10px] button-register flex items-center gap-[20px]">
                <input type="checkbox" id="miCheckbox" class="form-checkbox" checked>
                Programa
            </button>
            <button id="recursoButton"
                class=" w-[150px] m-auto border rounded-[6px] bg-[--primary-color] text-center justify-center text-white px-[26px] py-[10px] button-register flex items-center gap-[20px]">
                <input type="checkbox" id="miCheckbox" class="form-checkbox" checked>
                Recurso
            </button>
        </div>

    </div>


    <div class="container mx-auto my-[48px] lg:my-[119px] cursos-container">
        <div class="flex text-center flex-wrap justify-between mx-auto items-center mb-[40px] gap-[20px]">
            <h1 class="font-bold font-roboto-bold text-[36px] text-primary leading-[120%]">Nuestros cursos más destacados
            </h1>
            <div
                class="flex gap-[10px] text-primary arrow-see-more items-center hover:scale-110 cursor-pointer mx-auto md:mx-0">
                <p class="leading-[150%] text-center ">Ver todos los cursos</p>
                {{ e_heroicon('chevron-right', 'outline', 'black') }}
            </div>
        </div>

        <x-carrousel-component :items="$featured_courses" type="courses"></x-carrousel-component>
    </div>

    <div class="container mx-auto my-[48px] lg:my-[119px] programas-container">
        <div class="flex text-center flex-wrap justify-between mx-auto items-center mb-[40px] gap-[20px]">
            <h1 class="font-bold font-roboto-bold text-[36px] text-primary leading-[120%]">Programas formativos destacados
            </h1>
            <div
                class="flex gap-[10px] text-primary arrow-see-more items-center hover:scale-110 cursor-pointer mx-auto md:mx-0">
                <p class="leading-[150%] text-center">Ver todos los cursos</p>
                {{ e_heroicon('chevron-right', 'outline', 'black') }}
            </div>
        </div>

        <x-carrousel-component :items="$featured_courses" type="educational_programs"></x-carrousel-component>
    </div>

    <div class="container mx-auto my-[48px] lg:my-[119px] recursos-container">
        <div class="flex text-center flex-wrap justify-between mx-auto items-center mb-[40px] gap-[20px]">
            <h1 class="font-bold font-roboto-bold text-[36px] text-primary leading-[120%]">Recursos educativos destacados
            </h1>
            <div
                class="flex gap-[10px] text-primary arrow-see-more items-center hover:scale-110 cursor-pointer mx-auto md:mx-0">
                <p class="leading-[150%] text-center">Ver todos los cursos</p>
                {{ e_heroicon('chevron-right', 'outline', 'black') }}
            </div>
        </div>

        <x-carrousel-component :items="$educational_resources" type="educational_resources"></x-carrousel-component>
    </div>
@endsection

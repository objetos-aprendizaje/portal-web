@extends('layouts.app')
@section('content')
    <div class="container mx-auto">
        <div class="my-[65px]">
            <h1 class="font-bold font-roboto-bold text-[36px] text-primary leading-[120%] mb-[36px]">Buscador</h1>


            <div class="flex">

                <div class="flex gap-4">
                    <div>
                        <label>
                            <input type="checkbox" class="accent-[red]" checked> Curso
                        </label>
                    </div>

                    <div>
                        <label>
                            <input type="checkbox" class="accent-[red]" checked> Programa
                        </label>
                    </div>

                    <div>
                        <label>
                            <input type="checkbox" class="accent-[red]" checked> Recurso
                        </label>
                    </div>

                    <div class="relative flex justify-end">
                        <input type="text" class="w-full rounded-lg border-gray-200 border-2 h-10 outline-[#D9D9D9] bg-[#F4F4F6] pl-[46px] focus:outline-[#D9D9D9]" placeholder="Buscar...">
                        <button type="button" class="absolute h-[32px] w-[32px] p-1.5 top-0 bottom-0 m-auto  rounded-lg bg-white left-[8px] ">
                            {{ e_heroicon('magnifying-glass', 'solid') }}
                        </button>
                    </div>

                    <div>
                        <button class="w-[32px] h-[32px bg-[#F4F4F6] p-2 rounded-lg" type="button">
                            {{ e_heroicon('bars-3-bottom-left', 'solid') }}
                        </button>
                    </div>

                    <div>Ordenar por</div>
                </div>
            </div>

        </div>
    </div>
@endsection

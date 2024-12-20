@extends('layouts.app')
@section('content')
    <div class="poa-container">

        <div class="flex md:flex-row flex-col justify-between mb-8">
            <h3>Histórico de cursos</h3>

            <div class="relative ">
                <input id="search-course-input" type="text"
                    class="rounded-[10px] pr-[44px] border border-[#D9D9D9] md:w-[330px] placeholder:text-[#C4C4C4] input-border-focus w-full"
                    placeholder="Buscar curso" />
                <button
                    class="absolute right-[4px] w-[32px] h-[32px] flex justify-center items-center top-[4px] bg-[#C7C7C7] p-2 cursor-pointer mx-1 rounded-[10px] input-search hover:bg-[#2B4C7E33] transition duration-300"
                    type="button" id="search-course-btn">
                    {{ e_heroicon('magnifying-glass', 'solid', 'white', 16, 16) }}
                </button>
            </div>
        </div>

        <div id="no-courses-historic" class="hidden">
            <h2 class="text-center">No hay histórico de cursos</h2>
        </div>

        <div id="courses-historic-container" class="hidden">
            <div id="courses-historic-list">
            </div>

            <div class="flex md:flex-row flex-col gap-2 justify-between items-center mt-[55px]">
                <div class="order-2 md:order-1 font-bold text-[18px]">Nº de resultados: <span
                        id="number-total-results"></span></div>
                <div class="order-1 md:order-2" id="pagination-historic-courses">
                    @include('partials.pagination')
                </div>
            </div>
        </div>

    </div>


    <template id="course-template">
        <div
            class="flex items-center shadow-md rounded-[8px] course-block lg:flex-row flex-col gap-[11px] lg:gap-[28px] mb-[16px] lg:h-[190px] flex-grow p-[12px] lg:p-0">

            <div class="h-auto w-full lg:h-[190px] lg:w-[190px] flex-none">
                <a aria-label="enlace" class="course-link" href="#">
                    <img alt="imagen" src="{{ asset('images/articulo1.png') }}"
                        class="image w-full lg:w-[190px] h-full object-cover lg:rounded-tl-[8px] lg:rounded-bl-[8px]">
                </a>
            </div>

            <div class="flex-grow">
                <a aria-label="enlace" class="course-link" href="#">
                    <h2 class="title line-clamp line-clamp-2 mb-[12px]"></h2>
                </a>

                <div class="mb-[12px]">
                    <p class="realization-date text-[14px] text-[#979797]">
                        <span class="lg:hidden">Realización:</span>
                        <span class="hidden lg:inline-block">Realización:</span>
                        <span class="date"></span>
                    </p>
                </div>

                <div class="flex gap-[19px]">
                    <div class="block-status">
                        <div class="indicator openned"></div>
                        <div class="indicator-label text-[14px] text-[#979797]">Finalizado</div>
                    </div>

                </div>
            </div>

            <div class="separator border-t-[1px] lg:border-l-[1px] border-dashed w-full lg:w-auto lg:h-3/4"></div>

            <div
                class="btn-action-course-container h-full flex-none w-full lg:w-[180px] xl:w-[335px] flex flex-col lg:pr-[18px] lg:py-[8px] gap-[10px] justify-center">
                <div class="w-full">
                    <button type="button" class="btn btn-primary w-full btn-action-course" data-course_uid="">Ir al
                        curso</button>
                </div>
            </div>

        </div>
    </template>
@endsection

@extends('layouts.app')
@section('content')
    <div class="poa-container">

        <div class="flex md:flex-row flex-col justify-between mb-8">
            <h3>Cursos inscritos</h3>

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

        <div id="no-courses-inscribed" class="hidden">
            <h2 class="text-center">No te has inscrito en ningún curso</h2>
        </div>

        <div id="courses-inscribed-container" class="hidden">
            <div id="courses-inscribed-list">
            </div>

            <div class="flex md:flex-row flex-col gap-2 justify-between items-center mt-[55px]">
                <div class="order-2 md:order-1 font-bold text-[18px]">Nº de resultados: <span id="number-total-results"></span></div>
                <div class="order-1 md:order-2" id="pagination-inscribed-courses">
                    @include('partials.pagination')
                </div>
            </div>
        </div>

        <h2 class="text-center hidden" id="no-courses-found">No te has inscrito en ningún curso</h2>

    </div>


    <template id="course-template">
        <div
            class="flex items-center shadow-md rounded-[8px] course-block lg:flex-row flex-col gap-[11px] lg:gap-[28px] mb-[16px] lg:h-[210px] flex-grow p-[12px] lg:p-0">

            <div class="h-auto w-full lg:h-[210px] lg:w-[210px] flex-none">
                <a aria-label="enlace" class="course-link" href="#">
                    <img alt="imagen" src="{{ asset('images/articulo1.png') }}"
                        class="image w-full lg:w-[190px] h-full object-cover lg:rounded-tl-[8px] lg:rounded-bl-[8px]">
                </a>
            </div>

            <div class="flex-grow w-full ">
                <div aria-label="enlace" class="flex justify-between gap-1">
                    <a aria-label="enlace" class="course-link" href="#">
                        <div class="hidden lg:block">
                            <h2 class="title line-clamp line-clamp-2 mb-[12px] text-color_1"></h2>
                        </div>

                        <div class="lg:hidden block">
                            <h5 class="title line-clamp line-clamp-2 mb-[12px] text-color_1"></h5>
                        </div>
                    </a>

                    <div class="lg:hidden relative more-options-btn">
                        <div class="cursor-pointer">
                            {{ e_heroicon('ellipsis-vertical', 'outline') }}
                        </div>
                    </div>
                </div>

                <div class="mb-[12px]">
                    <section class="enrolling-dates-section hidden">
                        <p class="enrolling-date text-[14px] text-color_4">
                            <span class="lg:hidden">Matriculación:</span>
                            <span class="hidden lg:inline-block">Matriculación:</span>
                            <span class="date"></span>
                        </p>
                    </section>

                    <p class="realization-date text-[14px] text-color_4">
                        <span class="lg:hidden">Realización:</span>
                        <span class="hidden lg:inline-block">Realización:</span>
                        <span class="date"></span>
                    </p>
                </div>

                <div class="flex lg:gap-[19px] lg:flex-row flex-col">
                    <div>
                        <section class="indicator-course-status-section hidden">
                            <div class="block-status">
                                <div class="indicator-course-status indicator"></div>
                                <div class="indicator-course-status-label text-[14px] text-color_4"></div>
                            </div>
                        </section>

                        <section class="indicator-student-status-section hidden">
                            <div class="block-status">
                                <div class="indicator-student-status indicator"></div>
                                <div class="indicator-student-status-label text-[14px] text-color_4"></div>
                            </div>
                        </section>
                    </div>

                    <div class="require-documentation">
                        <div class="border-l"></div>
                        <div class="flex items-center text-[14px] text-color_2 gap-[6px]">
                            {{ e_heroicon('paper-clip', 'outline', null, 15, 15) }}
                            Requiere
                            documentación</div>
                    </div>

                </div>
            </div>

            <div class="border-t-[1px] lg:border-l-[1px] border-dashed w-full lg:w-auto lg:h-3/4"></div>

            <div
                class="relative h-full flex-none w-full lg:w-[180px] xl:w-[335px] flex flex-col lg:pr-[18px] lg:py-[8px] gap-[10px] justify-center">

                <div class="absolute top-[8px] right-[8px] hidden lg:block more-options-btn">
                    <div class="cursor-pointer">
                        {{ e_heroicon('ellipsis-vertical', 'outline') }}
                    </div>
                    <div
                        class="absolute right-[8px] top-[30px] p-[12px] bg-white rounded-[8px] shadow-lg hidden options-list">
                        <ul>
                            <li
                                class="cancel-inscription-btn select-none p-[10px] hover:bg-[#2b4c7e0d] rounded-[8px] cursor-pointer whitespace-nowrap">
                                Cancelar inscripción</li>
                        </ul>
                    </div>
                </div>

                <div class="w-full">
                    <input type="hidden" name="course_uid" class="course_uid" value="">
                    <button type="button" class="btn btn-primary w-full btn-action-course">Matricularse</button>
                </div>

                <div class="documentation-btn-container w-full">
                    <button type="button" class="btn btn-secondary documentation-btn w-full">Documentación
                        pendiente</button>
                </div>
            </div>

            <input type="hidden" class="course-uid" value="" />
        </div>
    </template>

    <template id="options-list">
        <div class="absolute right-[8px] top-[30px] p-[12px] bg-white rounded-[8px] shadow-lg options-list">
            <ul>
                <li
                    class="cancel-inscription-btn select-none p-[10px] hover:bg-[#2b4c7e0d] rounded-[8px] cursor-pointer whitespace-nowrap">
                    Cancelar inscripción</li>
            </ul>
        </div>
    </template>

    @include('profile.partials.modal-confirmation')
    @include('profile.partials.upload-documents-modal')
    @include('profile.partials.redsys')
@endsection

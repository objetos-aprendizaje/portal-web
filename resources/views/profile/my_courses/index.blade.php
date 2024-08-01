@extends('profile.layouts.app')
@section('content')
    @include('profile.partials.menu')
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

        <div class="tabs">
            <div class="tab" tab-content="tab-courses-realization">
                <h4>Cursos en realización</h4>
            </div>

            <div class="tab tab-selected" tab-content="tab-courses-inscribed">
                <h4>Cursos inscritos</h4>
            </div>

            <div class="tab" tab-content="tab-courses-enrolled">
                <h4>Cursos matriculados</h4>
            </div>

            <div class="tab" tab-content="tab-courses-finished">
                <h4>Cursos finalizados</h4>
            </div>
        </div>

        <div class="tab-content" id="tab-courses-realization">
            realizaiton
        </div>

        <div class="tab-content" id="tab-courses-inscribed">

            <div id="no-courses-inscribed" class="hidden">
                <h2 class="text-center">Aún no te has inscrito en ningún curso</h2>
            </div>

            <div id="courses-inscribed-container" class="hidden">
                <div id="courses-inscribed-list">

                </div>

                <div class="flex justify-center mt-[55px]" id="pagination-inscribed-courses">
                    @include('partials.pagination')
                </div>
            </div>

        </div>

        <div class="tab-content" id="tab-courses-enrolled">
            <div id="no-courses-enrolled" class="hidden">
                <h2 class="text-center">Aún no te has matriculado en ningún curso</h2>
            </div>

            <div id="courses-enrolled-container" class="hidden">
                <div id="courses-enrolled-list"></div>

                <div class="flex justify-center mt-[55px]" id="pagination-enrolled-courses">
                    @include('partials.pagination')
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-courses-finished">
            finalizados
        </div>

        <h2 class="text-center hidden" id="no-courses-found">Aún no te has inscrito en ningún curso</h2>

        <template id="course-template">
            <div
                class="h-auto flex items-center shadow-md rounded-[8px] course-block lg:flex-row flex-col gap-[28px] mb-[16px]">

                <div class="flex gap-[21px] mb-[16px] w-3/4">
                    <div class="w-[140px] flex-none">
                        <div>
                            <img src="{{ asset('images/articulo1.png') }}" alt="curso1"
                                class="image w-auto lg:w-[140px] h-[140px] object-cover rounded-tl-[8px] rounded-bl-[8px]">
                        </div>
                    </div>

                    <div class="flex-grow">
                        <h2 class="title line-clamp line-clamp-1 mb-[12px]"></h2>

                        <div class="mb-[12px]">
                            <p class="inscription-date text-[14px] text-[#979797]">
                                <span class="lg:hidden">Inscripción:</span>
                                <span class="hidden lg:inline-block">Período de inscripción:</span>
                                <span class="date"></span>
                            </p>

                            <p class="enrolling-date text-[14px] text-[#979797]">
                                <span class="lg:hidden">Matriculación:</span>
                                <span class="hidden lg:inline-block">Período de matriculación:</span>
                                <span class="date"></span>
                            </p>

                            <p class="realization-date text-[14px] text-[#979797]">
                                <span class="lg:hidden">Realización:</span>
                                <span class="hidden lg:inline-block">Período de realización:</span>
                                <span class="date"></span>
                            </p>
                        </div>

                        <div class="flex gap-[19px]">
                            <div class="block-status">
                                <div class="indicator"></div>
                                <div class="indicator-label text-[14px] text-[#979797]">En matriculación</div>
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
                </div>

                <div class="lg:w-[300px] w-1/4">
                    <div class="w-full lg:w-[300px] flex flex-col gap-[19px]">
                        <button type="button" class="btn btn-primary w-full btn-action-course">Ir al curso</button>

                        <div class="documentation-btn-container w-full">
                            <button type="button" class="btn btn-secondary documentation-btn w-full">Documentación
                                pendiente</button>
                        </div>
                    </div>
                </div>

                <input type="hidden" class="course-uid" value="" />
            </div>
        </template>


    </div>

    @include('profile.partials.upload-documents-modal')
@endsection

@extends('layouts.app')
@section('content')
    <div class="poa-container">

        @include('profile.partials.learning-object-payment-indicator')

        <div class="flex md:flex-row flex-col justify-between mb-8">
            <h3>Programas formativos inscrito</h3>

            <div class="relative ">
                <input id="search-educational-program-input" type="text"
                    class="rounded-[10px] pr-[44px] border border-[#D9D9D9] md:w-[330px] placeholder:text-[#C4C4C4] input-border-focus w-full"
                    placeholder="Buscar programa formativo" />
                <button
                    class="absolute right-[4px] w-[32px] h-[32px] flex justify-center items-center top-[4px] bg-[#C7C7C7] p-2 cursor-pointer mx-1 rounded-[10px] input-search hover:bg-[#2B4C7E33] transition duration-300"
                    type="button" id="search-educational-program-btn">
                    {{ e_heroicon('magnifying-glass', 'solid', 'white', 16, 16) }}
                </button>
            </div>
        </div>

        <div id="no-educational-programs-inscribed" class="hidden">
            <h2 class="text-center">Aún no te has inscrito en ningún programa formativo</h2>
        </div>

        <div id="educational-programs-inscribed-container" class="hidden">
            <div id="educational-programs-inscribed-list">
            </div>

            <div class="flex justify-center mt-[55px]" id="pagination-inscribed-educational-programs">
                @include('partials.pagination')
            </div>
        </div>

        <h2 class="text-center hidden" id="no-educational-programs-found">Aún no te has inscrito en ningún programa
            formativo</h2>


    </div>


    <template id="educational-program-template">
        <div class="shadow-md rounded-[8px] mb-[16px] pl-[12px] pt-[12px] pr-[20px] pb-[20px]">

            <div
                class="flex items-center educational-program-block lg:flex-row flex-col gap-[11px] lg:gap-[28px]  lg:h-[210px] flex-grow mb-6">

                <div class="w-full">
                    <div class="flex w-full gap-[11px] items-center lg:flex-row flex-col">
                        <div class="h-auto w-full lg:h-[210px] lg:w-[210px] flex-none">
                            <a aria-label="enlace" class="educational-program-link" href="#">
                                <img alt="imagen" src="{{ asset('images/articulo1.png') }}"
                                    class="image w-full lg:w-[190px] h-full object-cover lg:rounded-tl-[8px] lg:rounded-bl-[8px]">
                            </a>
                        </div>

                        <div class="flex-grow w-full">
                            <div class="flex justify-between gap-1">
                                <a aria-label="enlace" class="educational-program-link" href="#">
                                    <div class="hidden lg:block">
                                        <h2 class="title line-clamp line-clamp-2 mb-[12px]"></h2>
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

                            <div class="flex gap-[19px]">
                                <div>
                                    <section class="indicator-educational-program-status-section hidden">
                                        <div class="block-status">
                                            <div class="indicator-educational-program-status indicator"></div>
                                            <div
                                                class="indicator-educational-program-status-label text-[14px] text-color_4">
                                            </div>
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

                        <div
                            class="bg-[#F9F9F9] py-[10px] px-[15px] rounded-[6px] hidden accordion payment-terms-container mt-[12px]">
                            <div
                                class="text-[14px] text-color_1 font-bold flex gap-[6px] items-center cursor-pointer accordion-header">
                                <div>{{ e_heroicon('banknotes', 'outline') }}</div>
                                <span>Información de pago</span>
                                <div class="ml-auto">
                                    <div class="arrow-up hidden">{{ e_heroicon('chevron-up', 'outline', null, 12, 12) }}
                                    </div>
                                    <div class="arrow-down">{{ e_heroicon('chevron-down', 'outline', null, 12, 12) }}</div>
                                </div>
                            </div>

                            <div class="accordion-uncollapsed">
                                <div class="accordion-body payment-terms-list">
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="border-t-[1px] lg:border-l-[1px] border-dashed w-full lg:w-auto lg:h-3/4"></div>

                <div
                    class="relative h-full flex-none w-full lg:w-[180px] xl:w-[335px] flex flex-col lg:pr-[18px] lg:py-[8px] gap-[10px] justify-center">

                    <div class="absolute top-0 right-0 hidden lg:block more-options-btn">
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
                        <input type="hidden" name="educational_program_uid" class="educational_program_uid" value="">
                        <button type="button"
                            class="btn btn-primary w-full btn-action-educational-program">Matricularse</button>
                    </div>

                    <div class="documentation-btn-container w-full">
                        <button type="button" class="btn btn-secondary documentation-btn w-full"
                            data-educational_program_uid="">Documentación
                            pendiente</button>
                    </div>
                </div>

            </div>

            <div class="courses-container"></div>

            <input type="hidden" class="educational-program-uid" value="" />
        </div>

    </template>

    <template id="course-template">
        <div class="accordion">
            <div class="group" tabindex="1">
                <div
                    class="accordion-header bg-[#F8F9FA] flex justify-between px-[12px] py-[10px] items-center mb-[12px] rounded-[8px] cursor-pointer">
                    <h5 class="course-title text-color_1"></h5>
                    <div class="arrow-up hidden">
                        {{ e_heroicon('chevron-up', 'outline', null, 14, 14) }}
                    </div>
                    <div class="arrow-down">
                        {{ e_heroicon('chevron-down', 'outline', null, 14, 14) }}
                    </div>
                </div>

                <div class="accordion-collapsed">
                    <div class="accordion-body px-[12px]  mb-[12px]">
                        <p class="course-description text-color_4"></p>

                        <div class="border-t-[1px] lg:border-l-[1px] border-dashed w-full lg:w-auto lg:h-3/4 my-[14px]">
                        </div>

                        <div class="flex justify-between items-center">
                            <div class="text-color_4"><span class="course-ects-workload"></span> ECT</div>
                        </div>
                    </div>
                </div>
            </div>
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

@extends('layouts.app')
@section('content')
    <div class="poa-container">


        @include('profile.partials.learning-object-payment-indicator')

        <div class="flex md:flex-row flex-col justify-between mb-8">
            <h3>Cursos matriculados</h3>



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

        <div id="no-courses-enrolled" class="hidden">
            <h2 class="text-center">Aún no te has matriculado en ningún curso</h2>
        </div>

        <div id="courses-enrolled-container" class="hidden">
            <div id="courses-enrolled-list">
            </div>

            <div class="flex justify-center mt-[55px]" id="pagination-enrolled-courses">
                @include('partials.pagination')
            </div>
        </div>

        <h2 class="text-center hidden" id="no-courses-found">Aún no te has matriculado en ningún curso</h2>

    </div>


    <template id="course-template">
        <div
            class="flex items-center shadow-md rounded-[8px] course-block lg:flex-row flex-col gap-[11px] lg:gap-[28px] mb-[16px] flex-grow p-[12px]">

            <div class="w-full">
                <div class="flex w-full gap-[11px] items-center lg:flex-row flex-col">
                    <div class="h-auto w-full lg:h-[190px] lg:w-[190px] flex-none">
                        <a class="course-link" href="#">
                            <img src="{{ asset('images/articulo1.png') }}"
                                class="image w-full lg:w-[190px] h-full object-cover rounded-[8px]">
                        </a>
                    </div>

                    <div class="w-full flex-grow">
                        <a class="course-link" href="#">
                            <h2 class="title line-clamp line-clamp-2 mb-[12px]"></h2>
                        </a>

                        <div class="mb-[12px]">
                            <section class="enrolling-date-section hidden">
                                <p class="enrolling-date text-[14px] text-color_4">
                                    <span class="lg:hidden">Matriculación:</span>
                                    <span class="hidden lg:inline-block">Matriculación:</span>
                                    <span class="date"></span>
                                </p>
                            </section>

                            <section class="realization-date-section">
                                <p class="realization-date text-[14px] text-color_4">
                                    <span class="lg:hidden">Realización:</span>
                                    <span class="hidden lg:inline-block">Realización:</span>
                                    <span class="date"></span>
                                </p>
                            </section>
                        </div>

                        <div class="flex gap-[19px]">
                            <div class="block-status">
                                <div class="indicator"></div>
                                <div class="indicator-label text-[14px] text-color_4">En matriculación</div>
                            </div>

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

            <div class="border-t-[1px] lg:border-l-[1px] border-dashed w-full lg:w-auto lg:h-3/4"></div>

            <div
                class="h-full flex-none w-full lg:w-[180px] xl:w-[335px] flex flex-col lg:pr-[18px] lg:py-[8px] gap-[10px] justify-center">
                <div class="w-full">
                    <button type="button" class="btn btn-primary w-full btn-action-course" data-course_uid="">Ir al
                        curso</button>
                </div>
            </div>
        </div>
    </template>

    @include('profile.partials.redsys')
    @include('profile.partials.payment-terms')
@endsection

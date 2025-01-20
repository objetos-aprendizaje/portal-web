@extends('layouts.app')
@section('content')

    <div id="preloader">
        <div id="loader"></div>
    </div>

    <div id="main-slider">
        <div class="slideshow-container">

            <!-- SLIDER DE PREVISUALIZACIÓN -->
            @if ($sliderPrevisualization)
                @include('partials.slider', [
                    'image_path' => env('BACKEND_URL') . '/' . $sliderPrevisualization->image_path,
                    'title' => $sliderPrevisualization->title,
                    'description' => $sliderPrevisualization->description,
                    'registerButton' => true,
                    'colorFont' => $sliderPrevisualization->color ?? '#fff',
                ])
            @endif

            <!-- SLIDER PRINCIPAL -->
            @include('partials.slider', [
                'image_path' => $general_options['carrousel_image_path']
                    ? env('BACKEND_URL') . '/' . $general_options['carrousel_image_path']
                    : 'data/images/default_images/wallpaper-default-main-slider.jpg',
                'title' => $general_options['carrousel_title'] ?? 'Portal Objetos de Aprendizaje',
                'description' =>
                    $general_options['carrousel_description'] ??
                    'Esta es la descripción de prueba. Puedes cambiarla en la configuración del portal',
                'colorFont' => $general_options['main_slider_color_font'] ?? '#fff',
            ])

            <!-- SLIDERS DE CURSOS Y PROGRAMAS FORMATIVOS -->
            @foreach ($featuredCoursesSlider as $course)
                @include('partials.slider', [
                    'image_path' => env('BACKEND_URL') . '/' . $course['featured_big_carrousel_image_path'],
                    'title' => $course['featured_big_carrousel_title'],
                    'description' => $course['featured_big_carrousel_description'],
                    'registerButton' => true,
                    'registerLink' => '/course/' . $course->uid,
                    'colorFont' => $course['featured_slider_color_font'] ?? '#fff',
                ])
            @endforeach

            @foreach ($featuredEducationalProgramsSlider as $educationalProgram)
                @include('partials.slider', [
                    'image_path' => env('BACKEND_URL') . '/' . $educationalProgram['featured_slider_image_path'],
                    'title' => $educationalProgram['featured_slider_title'],
                    'description' => $educationalProgram['featured_slider_description'],
                    'registerButton' => true,
                    'registerLink' => '/educational_program/' . $educationalProgram->uid,
                    'colorFont' => $educationalProgram['featured_slider_color_font'] ?? '#fff',
                ])
            @endforeach

            <!-- Next and previous buttons -->
            <a aria-label="anterior" title="anterior" class="prev">
                {{ e_heroicon('chevron-left', 'outline', 'white', 20, 20) }}
            </a>
            <a aria-label="siguiente" title="siguiente" class="next">
                {{ e_heroicon('chevron-right', 'outline', 'white', 20, 20) }}
            </a>

        </div>
    </div>

    <!-- CARROUSEL FLOTANTE -->
    @if (!$featuredLearningObjectsCarrousel->isEmpty())
        <section class="floating-carrousel container mx-auto relative mb-[48px] lg:mb-[149px]">
            <x-carrousel-component :items="$featuredLearningObjectsCarrousel" type="courseOrEducationalProgram"></x-carrousel-component>
        </section>
    @endif


    @if (Auth::check())
        <div class="container mx-auto mt-[90px]">
            <div class="container-custom-lanes flex justify-center mb-[26px] gap-[13px] items-center">
                <div id="scroll-container" class="row-buttons-lanes overflow-x-auto scrollbar-hide">
                    <h2 data-lane="courses-actived" class="m-0 lane-tab row-button selected whitespace-nowrap ">Cursos
                        Activos</h2>
                    <h2 data-lane="courses-inscribed" class="m-0 lane-tab row-button whitespace-nowrap">Cursos Inscritos /
                        En Matriculación</h2>

                    @if (Auth::user()->hasAnyRole(['TEACHER']))
                        <h2 data-lane="courses-teacher" class="m-0 lane-tab row-button whitespace-nowrap">Cursos como
                            docente
                        </h2>
                    @endif

                    @if ($general_options['enabled_recommendation_module'])
                        <h2 data-lane="courses-recommended" class="m-0 lane-tab row-button whitespace-nowrap">Cursos
                            Recomendados</h2>

                        <h2 data-lane="educational-resources-recommended" class="m-0 lane-tab row-button whitespace-nowrap">
                            Recursos
                            Recomendados</h2>
                    @endif

                    <h2 data-lane="my-educational-resources" class="m-0 lane-tab row-button whitespace-nowrap">
                        Mis Recursos</h2>

                    @if ($general_options['lane_featured_itineraries'])
                        <h2 data-lane="recommended-itinerary" class="m-0 lane-tab row-button whitespace-nowrap">
                            Itinerario recomendado</h2>
                    @endif
                </div>

                <div id="scroll-buttons-custom-lanes" class="flex gap-[13px]">
                    <div>
                        <button id="scroll-lanes-left" type="button" class="lane-arrow">
                            {{ e_heroicon('chevron-left', 'outline', null, 16, 16) }}
                        </button>
                    </div>

                    <div>
                        <button id="scroll-lanes-right" type="button" class="lane-arrow lane-arrow-enabled">
                            {{ e_heroicon('chevron-right', 'outline', null, 16, 16) }}
                        </button>
                    </div>
                </div>
            </div>

            <div id="courses-lane-container" class="learning-objects-container horizontal mb-[65px]">

            </div>


            <div id="control-pagination-courses-lanes" class="flex justify-center sm:justify-between mb-[172px]">
                <div class="hidden sm:block" id="selector-num-pages-courses-lanes">
                    @include('partials.selector-num-pages')
                </div>

                <div>
                    <div id="pagination-lane-courses">
                        @include('partials.pagination')
                    </div>
                </div>

            </div>
        </div>
    @endif



    <!-- BOTONES DE CONTROL -->
    <section
        class="container mx-auto items-end  mb-[48px] lgx:mb-[110px] flex gap-[26px] flex-row flex-wrap justify-center ">
        @if ($general_options['lane_featured_courses'])
            <label id="cursoButton" class="btn-lane-home text-white" for="courses-lane-checkbox">
                <x-checkbox id="courses-lane-checkbox" label="Cursos" :classLabel="'text-white'" :checked="$lanes_preferences['courses']" />
            </label>
        @endif

        @if ($general_options['lane_featured_educationals_programs'])
            <label id="programaButton" class="btn-lane-home" for="programs-lane-checkbox">
                <x-checkbox id="programs-lane-checkbox" label="Programas" :classLabel="'text-white'" :checked="$lanes_preferences['programs']" />
            </label>
        @endif

        @if ($general_options['lane_recents_educational_resources'])
            <label id="recursoButton" class="btn-lane-home" for="resources-lane-checkbox">
                <x-checkbox id="resources-lane-checkbox" label="Recursos" :classLabel="'text-white'" :checked="$lanes_preferences['resources']" />
            </label>
        @endif
    </section>


    <!-- CURSOS MÁS DESTACADOS -->
    @if ($general_options['lane_featured_courses'])
        <section
            class="container mx-auto mb-[48px] lg:mb-[149px] courses-lane {{ $lanes_preferences['courses'] ? 'block' : 'hidden' }}">
            <div class="flex text-center flex-wrap justify-between mx-auto items-center mb-[40px] gap-[20px]">
                <h1 class="font-bold font-roboto-bold text-[36px] text-color_1 leading-[120%]">Cursos destacados</h1>
                <div
                    class="flex gap-[10px] text-color_1 arrow-see-more items-center hover:scale-110 cursor-pointer mx-auto md:mx-0">
                    <p class="leading-[150%] text-center text-color_1"><a aria-label="enlace"
                            title="ver todos los objeto de aprendizaje" href="/searcher?resources=courses">Ver todos los
                            cursos</a></p>
                    {{ e_heroicon('chevron-right', 'outline', 'black') }}
                </div>
            </div>

            @if (!$featured_courses->isEmpty())
                <x-carrousel-component :items="$featured_courses" type="courseOrEducationalProgram"></x-carrousel-component>
            @else
                <div class="flex justify-center">
                    <h2>No hay cursos destacados</h2>
                </div>
            @endif
        </section>
    @endif


    <!-- PROGRAMAS FORMATIVOS DESTACADOS -->
    @if ($general_options['lane_featured_educationals_programs'])
        <section
            class="container mx-auto mb-[48px] lg:mb-[149px] programs-lane {{ $lanes_preferences['programs'] ? 'block' : 'hidden' }}">
            <div class="flex text-center flex-wrap justify-between mx-auto items-center mb-[40px] gap-[20px]">
                <h1 class="font-bold font-roboto-bold text-[36px] text-color_1 leading-[120%]">Programas formativos
                    destacados</h1>
                <div
                    class="flex gap-[10px] text-color_1 arrow-see-more items-center hover:scale-110 cursor-pointer mx-auto md:mx-0">
                    <p class="leading-[150%] text-center text-color_1"><a aria-label="enlace"
                            title="ver todos los programas" href="/searcher?resources=programs">Ver todos los
                            programas</a></p>
                    {{ e_heroicon('chevron-right', 'outline', 'black') }}
                </div>
            </div>

            @if (!$featuredEducationalPrograms->isEmpty())
                <x-carrousel-component :items="$featuredEducationalPrograms" type="courseOrEducationalProgram"></x-carrousel-component>
            @else
                <div class="flex justify-center">
                    <h2>No hay programas formativos destacados</h2>
                </div>
            @endif
        </section>
    @endif


    <!-- RECURSOS EDUCATIVOS DESTACADOS -->
    @if ($general_options['lane_recents_educational_resources'])
        <section
            class="container mx-auto mb-[48px] lg:mb-[149px] resources-lane {{ $lanes_preferences['resources'] ? 'block' : 'hidden' }}">
            <div class="flex text-center flex-wrap justify-between mx-auto items-center mb-[40px] gap-[20px]">
                <h1 class="font-bold font-roboto-bold text-[36px] text-color_1 leading-[120%]">Recursos educativos
                    destacados
                </h1>
                <div
                    class="flex gap-[10px] text-color_1 arrow-see-more items-center hover:scale-110 cursor-pointer mx-auto md:mx-0">
                    <p class="leading-[150%] text-center text-color_1"><a aria-label="enlace" title="ver todos los recursos"
                            href="/searcher?resources=resources">Ver todos los
                            recursos</a></p>
                    {{ e_heroicon('chevron-right', 'outline', 'black') }}
                </div>
            </div>

            @if (!$educational_resources->isEmpty())
                <x-carrousel-component :items="$educational_resources" type="educational_resources"></x-carrousel-component>
            @else
                <div class="flex justify-center">
                    <h2>No hay recursos educativos destacados</h2>
                </div>
            @endif
        </section>
    @endif


    <div class="bg-color_2 py-[66px] lg:py-[110px]">
        <div class="container mx-auto px-[20px]">
            <h1 class="text-white text-center mb-[34px] lg:mb-[77px] hidden lg:block">¿Qué quieres aprender hoy?</h1>

            <h2 class="text-white text-center mb-[34px] lg:mb-[77px] lg:hidden">¿Qué quieres aprender hoy?</h2>

            <div class="grid lg:grid-cols-3 lg:grid-flow-row gap-[14px] lg:gap-[29px]">
                @foreach ($categories as $category)
                    <a aria-label="enlace" title="categoría" class="no-effect-hover"
                        href="/searcher?category_uid={{ $category['uid'] }}">
                        <div style="background-color: {{ $category['color'] }}"
                            class="flex p-[17px] gap-[17px] rounded-[8px] cursor-pointer h-[135px] items-center">
                            <img alt="{{ $category['name'] }}" class="w-[88px] h-[88px] rounded-[4px]"
                                src="{{ env('BACKEND_URL') . '/' . $category['image_path'] }}" alt="">
                            <div class="flex flex-col">
                                <h2 class="text-black mb-[8px] line-clamp line-clamp-2">{{ $category['name'] }}</h2>

                                <p class="text-[18px]">{{ $category['courses_count'] }}
                                    {{ trans_choice('Curso|Cursos', $category['courses_count']) }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

        </div>
    </div>

    <template id="learning-object-template">
        <div class="learning-object-block shadow-lg">
            <div class="learning-object-img-container">
                <a aria-label="enlace" title="objeto de aprendizaje" class="no-effect-hover block-url"
                    href="#"><img alt="objeto de aprendizaje" src="" class="learning-object-image"></a>
            </div>
            <div class="block-container">
                <div class="block-container-title">
                    <a aria-label="enlace" title="objeto de aprendizaje" class="no-effect-hover block-url"
                        href="#">
                        <h2 class="block-title">Objeto de aprendizaje</h2>
                    </a>
                </div>
                <p class="block-description block-description-small"></p>

                <hr class="separator-status-user-course border-t border-dashed">
                <small class="status-user-course"></small>

                <hr class="separator-dates border-t border-dashed">

                <div class="learning-objects-dates-container">
                    <div class="learning-object-dates-block">
                        <div class="learning-object-dates">
                            <div class="dates-container">
                                <p class="text-color_4">Fecha de inicio: </p>
                                <p class="dates-container-date learning-object-start-date text-color_4"></p>
                            </div>

                            <div class="dates-container">
                                <p class="text-color_4">Fecha de fin: </p>
                                <p class="learning-object-finish-date text-color_4"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

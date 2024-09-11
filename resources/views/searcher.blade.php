@extends('layouts.app')
@section('content')
    <div class="container mx-auto my-[65px]">
        <h1 class="font-bold text-[36px] text-color_1 leading-[120%] mb-[36px]">Buscador</h1>

        <!-- FILTROS INICIALES -->
        <section class="flex gap-4 lg:items-center flex-col lg:flex-row mb-[28px]">
            <div class="flex gap-4 justify-between text-black">
                <div class="flex-none">
                    <x-checkbox id="courses" label="Cursos" class="primary" gap="8" />
                </div>

                <div class="flex-none">
                    <x-checkbox id="programs" label="Programa" class="primary" gap="8" />
                </div>

                <div class="flex-none">
                    <x-checkbox id="resources" label="Recurso" class="primary" gap="8" />
                </div>

            </div>

            <div class="relative flex grow justify-end">
                <input type="text"
                    class="w-full rounded-lg py-[8px] border-none bg-color_background_elements pl-[46px] focus:ring-0 focus:border-color_1 focus:outline-none searcher"
                    placeholder="Buscar..." id="search">
                <button title="buscar" type="button"
                    class="absolute h-[32px] w-[32px] p-1.5 top-0 bottom-0 m-auto rounded-lg bg-white left-[8px] flex items-center justify-center"
                    id="search-btn">
                    {{ e_heroicon('magnifying-glass', 'solid', 16, 16) }}
                </button>
            </div>

            <div class="border-l border-2 h-[16px] flex-none hidden lg:block"></div>

            <div class="flex gap-4 justify-between lg:items-center relative">

                <div id="order-by-btn" class="flex gap-4 items-center relative cursor-pointer">
                    <div class="flex-none">
                        <button title="ordenar"
                            class="w-[32px] h-[32px] bg-color_background_elements p-2 rounded-lg flex justify-center items-center"
                            type="button">
                            {{ e_heroicon('bars-3-bottom-left', 'solid', 16, 16) }}
                        </button>
                    </div>

                    <p class="flex-none">Ordenado por: <span id="order-by-label">relevancia</span></p>

                </div>

                <div id="order-by-container"
                    class="hidden absolute top-[54px] bg-white p-[10px] w-[218px] z-20 border rounded-[8px] border-[#E9ECEF] shadow-sm">
                    <div data-order_by="relevance"
                        class="order-option-btn p-[10px] cursor-pointer hover:bg-color_1 rounded-[8px] hover:text-white text-[14px]">
                        Relevancia</div>
                    <div data-order_by="closer"
                        class="order-option-btn p-[10px] cursor-pointer hover:bg-color_1 rounded-[8px] hover:text-white text-[14px]">
                        Más próximos</div>
                    <div data-order_by="puntuation"
                        class="order-option-btn p-[10px] cursor-pointer hover:bg-color_1 rounded-[8px] hover:text-white text-[14px]">
                        Mejor puntuación</div>
                </div>

                <div class="border-l border-2 h-[16px] flex-none hidden lg:block"></div>

                <div class="control-view-searcher">
                    <button title="ver en vertical" class="control" id="view-vertical-btn">
                        {{ e_heroicon('squares-2x2', 'solid', null, false, false, 'text-color_1') }}
                    </button>

                    <button title="ver en horizontal" class="control" id="view-horizontal-btn">
                        {{ e_heroicon('bars-4', 'solid') }}
                    </button>
                </div>

            </div>
        </section>

        <section id="filters"
            class="p-[24px] bg-color_background_elements grid grid-cols-1 lg:grid-cols-4 gap-[8px] rounded-[8px] mb-[16px] filters-search">
            <div class="custom-treeselect" id="treeselect-categories"></div>

            <div class="custom-treeselect" id="treeselect-competences"></div>

            <div class="h-[50px]">
                <select aria-label="estado de los objetos de aprendizaje" class="rounded-[8px] w-full border-none" id="learning-object-status">
                    <option value="">Estado</option>
                    <option value="INSCRIPTION">En inscripción</option>
                    <option value="DEVELOPMENT">En desarrollo</option>
                    <option value="ENROLLING">En matriculación</option>
                    <option value="FINISHED">Finalizado</option>
                </select>
            </div>

            <div>
                <input type="datetime-local" class="w-full border-none rounded-[8px] custom-flatpickr"
                    id="filter_inscription_date" placeholder="Fecha de inscripción" name="filter_inscription_date" />
            </div>

            <div>
                <input type="datetime-local" class="w-full rounded-[8px] custom-flatpickr" id="filter_realization_date"
                    placeholder="Fecha de realización" name="filter_realization_date" />
            </div>

            <div>
                <select aria-label="modalidad de pago" class="rounded-[8px] w-full border-none" id="modality-payment">
                    <option value="">Objeto gratuito o pago</option>
                    <option value="PAID">De pago</option>
                    <option value="FREE">Gratuitos</option>
                </select>
            </div>

            <div>
                <select aria-label="valoraciones" class="rounded-[8px] w-full border-none" id="assessments">
                    <option value="">Valoraciones</option>
                    <option value="5">5 estrellas</option>
                    <option value="4">4 estrellas</option>
                    <option value="3">3 estrellas</option>
                    <option value="2">2 estrellas</option>
                    <option value="1">1 estrellas</option>
                </select>
            </div>
        </section>

        <section class="flex justify-between mb-[54px]">
            <div id="filters-container" class="flex flex-wrap gap-[8px]">
                <div id="filters-results" class="text-[14px] gap-[16px] text-center items-center hidden">
                    <div>Mostrando <span id="filter-results-showing"></span> de <span id="filter-results-total"></span>
                        resultados</div>
                    <div class="border-l border h-[16px] flex-none hidden lg:block"></div>
                    <div class="text-[#FF2954]" id="delete-all-filters-btn"><a title="limpiar" href="javascript:void(0)">Limpiar</a></div>
                </div>
            </div>
        </section>

        <template id="filter-template">
            <div
                class="filter-selector flex py-[8px] pl-[16px] pr-[8px] border gap-[8px] rounded-[8px] border-color_background_elements">
                <div class="filter-name text-[14px]"></div>
                <div class="delete-filter cursor-pointer w-[24px] h-[24px] flex justify-center items-center bg-color_background_elements rounded-[8px]"
                    data-filter_key="">
                    {{ e_heroicon('x-mark', 'outline', null, 16, 16) }}
                </div>
            </div>
        </template>

        <section id="learning-objects-section" class="hidden">
            <div class="learning-objects-container vertical mb-[80px]" id="learning-objects-container">
            </div>

            <div class="flex justify-center" id="searcher-pagination">
                @include('partials.pagination')
            </div>
        </section>

        <h2 id="no-learning-objects-found" class="text-center hidden">No se encontraron objetos de aprendizaje</h2>


        <template id="learning-object-template">
            <div class="learning-object-block shadow-lg">
                <div class="learning-object-img-container">
                    <a title="objeto de aprendizaje" class="no-effect-hover" class="learning-object-url" href="javascript:void(0)">
                        <img alt="objeto de aprendizaje" src="" class="learning-object-image">
                    </a>
                    <div class="learning-object-img-container-title learning-object-type"></div>

                </div>
                <div class="block-container">

                    <div class="block-container-title">
                        <div>
                            <a title="objeto de aprendizaje" class="learning-object-url" href="javascript:void(0)">
                                <h2 class="block-title"></h2>
                            </a>
                        </div>

                        <div>
                            <p class="learning-object-ects"><span class="learning-object-ects-count">0</span> ECT</p>
                        </div>

                    </div>

                    <a title="objeto de aprendizaje" class="learning-object-url" href="javascript:void(0)">
                        <div class="block-description"></div>
                    </a>

                    <div class="learning-objects-dates-container">
                        <div class="learning-object-dates-block">

                            <div class="learning-object-dates">
                                <div class="dates-container">
                                    <div class="dates-container-label text-color_4">
                                        F. inscripción
                                    </div>
                                    <div class="dates-container-date learning-object-inscription-date"></div>
                                </div>

                                <div class="dates-container">
                                    <div class="dates-container-label">
                                        F. realización
                                    </div>
                                    <div class="dates-container-date learning-object-realization-date"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-t border-dashed">

                    <div class="learning-object-info-block">
                        @if ($general_options['learning_objects_appraisals'])
                            <div class="flex justify-between">
                                <div class="cards-stars flex learning-object-stars">

                                </div>
                            </div>
                            <hr class="border-t border-dashed">
                        @endif

                        <div class="block-status">
                            <div class="indicator learning-object-status-indicator"></div>
                            <p class="block-status-text learning-object-status-text"></p>
                        </div>

                    </div>
                </div>

            </div>
        </template>
    </div>

    @include('partials.footer')
@endsection

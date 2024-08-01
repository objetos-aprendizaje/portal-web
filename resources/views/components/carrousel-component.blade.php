<div class="containerCarrousel mx-auto z-10 overflow-hidden z-1">

    <div class="swiper-container mySwiper flex lg:overflow-hidden ">
        <div class="swiper-button-prev hidden md:flex my-auto"></div>

        <div class="swiper-wrapper ">

            @if ($type == 'courses')

                @foreach ($items as $item)
                    <div class="swiper-slide">
                        <div>
                            <a class="no-effect-hover" href="/course/{{ $item['uid'] }}">
                                <div class="h-[224px] relative">
                                    @if ($item['image_path'])
                                        <img src="{{ env('BACKEND_URL') . '/' . $item['image_path'] }}"
                                            class="h-[100%] w-[100%]">
                                    @else
                                        <img src="/images/articulo0.png" class="h-[100%] w-[100%]">
                                    @endif

                                    <div class="absolute  bottom-[20px] bg-[white] text-color_1 font-bold px-[20px]">
                                        Curso
                                    </div>

                                </div>
                            </a>

                            <div class="p-[20px] flex flex-col gap-[19px]">
                                <div class="flex flex-col gap-[14px]">
                                    <div class="min-h-[110px]">
                                        <h4 class="text-[24px] font-bold line-clamp line-clamp-3">
                                            <a class="no-effect-hover" href="/course/{{ $item['uid'] }}">
                                                {{ $item['title'] }}
                                            </a>
                                        </h4>
                                    </div>

                                    <p>{{ $item['ects_workload'] }} ECT</p>
                                </div>

                                <p
                                    class="text-color_1 text-[20px font-bold leading-[22px] overflow-hidden line-clamp line-clamp-3 min-h-[66px]">
                                    <a class="no-effect-hover" href="/course/{{ $item['uid'] }}">
                                        {{ $item['description'] }}
                                    </a>
                                </p>

                                <div>
                                    <div class="flex text-[10px] justify-between text-color_4">
                                        <div>
                                            Período de inscripción
                                        </div>
                                        <div>
                                            {{ (new DateTime($item['inscription_start_date']))->format('d/m/y') }} -
                                            {{ (new DateTime($item['inscription_finish_date']))->format('d/m/y') }}
                                        </div>
                                    </div>

                                    <div class="flex text-[10px] justify-between text-color_4">
                                        <div>
                                            Período de realización
                                        </div>
                                        <div>
                                            {{ (new DateTime($item['realization_start_date']))->format('d/m/y') }} -
                                            {{ (new DateTime($item['realization_finish_date']))->format('d/m/y') }}
                                        </div>
                                    </div>
                                </div>

                                @if ($general_options['learning_objects_appraisals'])
                                    <hr class="border-t border-dashed">

                                    <div class="flex justify-between">
                                        <div class="cards-stars flex">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= round($item['average_calification']))
                                                    <div class="star-filled">
                                                        {{ e_heroicon('star', 'solid') }}
                                                    </div>
                                                @else
                                                    <div class="star-no-filled">
                                                        {{ e_heroicon('star', 'solid') }}
                                                    </div>
                                                @endif
                                            @endfor

                                        </div>
                                    </div>
                                @endif

                                <hr class="border-t border-dashed">

                                <div class="flex items-center gap-[5px]">
                                    @if ($item['status']['code'] == 'INSCRIPTION')
                                        <div class="w-[7px] h-[7px] bg-[#76F28A] rounded-[50%]"></div>
                                        <p class="text-[16px]">Inscripción</p>
                                    @elseif ($item['status']['code'] == 'ACCEPTED_PUBLICATION')
                                        <div class="w-[7px] h-[7px] bg-[#F27676] rounded-[50%]"></div>
                                        <p class="text-[16px]">Próximamente</p>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @elseif($type == 'educational_programs')
                @foreach ($items as $item)
                    <div class="swiper-slide">
                        <div>
                            <a class="no-effect-hover" href="/educational_program/{{ $item['uid'] }}">
                                <div class="h-[224px] relative">
                                    @if ($item['image_path'])
                                        <img src="{{ env('BACKEND_URL') . '/' . $item['image_path'] }}"
                                            class="h-[100%] w-[100%]">
                                    @else
                                        <img src="/images/articulo0.png" class="h-[100%] w-[100%]">
                                    @endif

                                    <div class="absolute  bottom-[20px] bg-[white] text-color_1 font-bold px-[20px]">
                                        Programa
                                    </div>
                                </div>
                            </a>
                            <div class="p-[20px] flex flex-col gap-[19px]">
                                <div class="flex flex-col gap-[14px]">
                                    <div class="min-h-[110px]">

                                        <h4 class="text-[24px] font-bold line-clamp line-clamp-3">
                                            <a class="no-effect-hover" href="/educational_program/{{ $item['uid'] }}">
                                                {{ $item['name'] }}
                                            </a>
                                        </h4>
                                    </div>

                                    <p>{{ $item['total_ects_workload'] ?? 0 }} ECT</p>
                                </div>

                                <h3
                                    class="text-color_1 text-[20px font-bold leading-[22px] overflow-hidden line-clamp line-clamp-3 min-h-[66px]">
                                    <a class="no-effect-hover" href="/educational_program/{{ $item['uid'] }}">
                                        {{ $item['description'] }}
                                    </a>
                                </h3>

                                <div>
                                    <div class="flex text-[10px] justify-between text-color_4">
                                        <div>
                                            Período de inscripción
                                        </div>
                                        <div>
                                            {{ (new DateTime($item['inscription_start_date']))->format('d/m/y') }}
                                            -
                                            {{ (new DateTime($item['inscription_finish_date']))->format('d/m/y') }}
                                        </div>
                                    </div>
                                </div>


                                @if ($general_options['learning_objects_appraisals'])
                                    <hr class="border-t border-dashed">

                                    <div class="flex justify-between">
                                        <div class="cards-stars flex">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= round($item['total_ects_workload']))
                                                    <div class="star-filled">
                                                        {{ e_heroicon('star', 'solid') }}
                                                    </div>
                                                @else
                                                    <div class="star-no-filled">
                                                        {{ e_heroicon('star', 'solid') }}
                                                    </div>
                                                @endif
                                            @endfor

                                        </div>
                                    </div>
                                @endif

                                <hr class="border-t border-dashed">

                                <div class="flex items-center gap-[5px]">
                                    <div class="flex items-center gap-[5px]">
                                        @if ($item['inscription_start_date'] < date('Y-m-d') && $item['inscription_finish_date'] > date('Y-m-d'))
                                            <div class="w-[7px] h-[7px] bg-[#76F28A] rounded-[50%]"></div>
                                            <p class="text-[16px]">Inscripción</p>
                                        @else
                                            <div class="w-[7px] h-[7px] bg-[#F27676] rounded-[50%]"></div>
                                            <p class="text-[16px]">Próximamente</p>
                                        @endif

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @elseif($type == 'educational_resources')
                @foreach ($items as $item)
                    <div class="swiper-slide min-h-[520px]">
                        <div>
                            <a class="no-effect-hover" href="/resource/{{ $item['uid'] }}">
                                <div class="h-[224px] relative">

                                    @if ($item['image_path'])
                                        <img src="{{ env('BACKEND_URL') . '/' . $item['image_path'] }}"
                                            class="h-[100%] w-[100%]">
                                    @else
                                        <img src="/images/articulo0.png" class="h-[100%] w-[100%]">
                                    @endif


                                    <div class="absolute  bottom-[20px] bg-[white] text-color_1 font-bold px-[20px]">
                                        Recurso educativo
                                    </div>

                                </div>
                            </a>

                            <div class="p-[20px] flex flex-col gap-[19px]">
                                <div class="flex flex-col gap-[14px]">
                                    <h4 class="text-[24px] font-bold line-clamp line-clamp-3 min-h-[84px]">
                                        <a class="no-effect-hover" href="/resource/{{ $item['uid'] }}">

                                            {{ $item['title'] }}
                                        </a>
                                    </h4>
                                </div>
                                <div
                                    class="text-color_1 text-[20px font-bold leading-[22px] overflow-hidden line-clamp line-clamp-3 min-h-[66px]">
                                    <a class="no-effect-hover" href="/resource/{{ $item['uid'] }}">
                                        <p class="text-color_1">{{ $item['description'] }}</p>
                                    </a>
                                </div>

                                @if ($general_options['learning_objects_appraisals'])
                                    <hr class="border-t border-dashed">
                                    <div class="flex justify-between">
                                        <div class="cards-stars flex">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= round($item['average_calification']))
                                                    <div class="star-filled">
                                                        {{ e_heroicon('star', 'solid') }}
                                                    </div>
                                                @else
                                                    <div class="star-no-filled">
                                                        {{ e_heroicon('star', 'solid') }}
                                                    </div>
                                                @endif
                                            @endfor

                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif


        </div>
        <div class="swiper-button-next hidden md:flex my-auto"></div>
        <div class="swiper-pagination flex md:hidden"></div>

    </div>

</div>

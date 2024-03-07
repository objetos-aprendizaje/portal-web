<div class="containerCarrousel mx-auto z-10 overflow-hidden z-1">

    <div class="swiper-container mySwiper flex lg:overflow-hidden ">
        <div class="swiper-button-prev hidden md:flex my-auto"></div>

        <div class="swiper-wrapper">

            @if (in_array($type, ['courses', 'educational_programs']))

                @foreach ($items as $item)
                    <div class="swiper-slide min-h-[750px]">
                        <div class="h-[224px] relative">
                            @if ($item['image_path'])
                                <img src="{{ env('BACKEND_URL') . '/' . $item['image_path'] }}" class="h-[100%] w-[100%]">
                            @else
                                <img src="/images/articulo0.png" class="h-[100%] w-[100%]">
                            @endif

                            <div class="absolute  bottom-[20px] bg-[white] text-primary font-roboto-bold px-[20px]">
                                Curso
                            </div>

                        </div>
                        <div class="p-[20px] flex flex-col gap-[19px]">
                            <div class="flex flex-col gap-[14px]">
                                <h4 class="text-[24px] font-bold line-clamp-3">{{ $item['title'] }}</h4>

                                <p>{{ $item['ects_workload'] }} ECT</p>
                            </div>
                            <div class=" text-primary text-[20px font-bold leading-[22px] overflow-hidden line-clamp-3">
                                {{ $item['description'] }}
                            </div>
                            <div class="flex cards-icons gap-[8px]">
                                {{ e_heroicon('academic-cap', 'solid', 'white') }}
                                {{ e_heroicon('bell', 'outline', '#D9D9D9') }}
                                {{ e_heroicon('briefcase', 'solid', 'white') }}
                            </div>

                            <div>
                                <div class="flex text-[10px] justify-between text-[#585859]">
                                    <div>
                                        Período de inscripción
                                    </div>
                                    <div>
                                        {{ (new DateTime($item['inscription_start_date']))->format('d/m/y') }} -
                                        {{ (new DateTime($item['inscription_finish_date']))->format('d/m/y') }}
                                    </div>
                                </div>

                                <div class="flex text-[10px] justify-between text-[#585859]">
                                    <div>
                                        Período de realización
                                    </div>
                                    <div>
                                        {{ (new DateTime($item['realization_start_date']))->format('d/m/y') }} -
                                        {{ (new DateTime($item['realization_finish_date']))->format('d/m/y') }}
                                    </div>
                                </div>
                            </div>

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

                            <hr class="border-t border-dashed">

                            <div class="flex items-center gap-[5px]">
                                @if ($item['status']['code'] == 'INSCRIPTION')
                                    <div class="w-[10px] h-[10px] bg-[#76F28A] rounded-[50%]"></div>
                                    <div>Inscripción</div>
                                @elseif ($item['status']['code'] == 'ACCEPTED_PUBLICATION')
                                    <div class="w-[10px] h-[10px] bg-[#F27676] rounded-[50%]"></div>
                                    <div>Próximamente</div>
                                @endif

                            </div>

                        </div>
                    </div>
                @endforeach
            @elseif($type == 'educational_resources')
                @foreach ($items as $item)
                    <div class="swiper-slide min-h-[520px]">
                        <div class="h-[224px] relative">
                            @if ($item['image_path'])
                                <img src="{{ env('BACKEND_URL') . '/' . $item['image_path'] }}"
                                    class="h-[100%] w-[100%]">
                            @else
                                <img src="/images/articulo0.png" class="h-[100%] w-[100%]">
                            @endif

                            <div class="absolute  bottom-[20px] bg-[white] text-primary font-roboto-bold px-[20px]">
                                Recurso educativo
                            </div>

                        </div>
                        <div class="p-[20px] flex flex-col gap-[19px]">
                            <div class="flex flex-col gap-[14px]">
                                <h4 class="text-[24px] font-bold line-clamp-3">{{ $item['name'] }}</h4>
                            </div>
                            <div class=" text-primary text-[20px font-bold leading-[22px] overflow-hidden line-clamp-3">
                                {{ $item['description'] }}
                            </div>
                            <div class="flex cards-icons gap-[8px]">
                                {{ e_heroicon('academic-cap', 'solid', 'white') }}
                                {{ e_heroicon('bell', 'outline', '#D9D9D9') }}
                                {{ e_heroicon('briefcase', 'solid', 'white') }}
                            </div>

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

                        </div>
                    </div>
                @endforeach
            @endif


        </div>
        <div class="swiper-button-next hidden md:flex my-auto"></div>
        <div class="swiper-pagination flex md:hidden"></div>

    </div>

</div>

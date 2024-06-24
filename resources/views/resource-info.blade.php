@extends('layouts.app')
@section('content')
    <div class="container mx-auto">

        <div class="flex mt-[60px] gap-[74px] lg:flex-row flex-col mb-[156px]">

            <div class="lg:flex-grow">
                <h2 class="lg:mb-[69px]">
                    {{ $educational_resource->title }}
                </h2>

                <p class="mb-[40px]">
                    {{ $educational_resource->description }}
                </p>
            </div>

            <div class="lg:min-w-[475px] lg:max-w-[475px] mb-[31px] lg:mb-0 rounded-[12px]">

                <div class="shadow-xl pb-[20px] mb-[50px]">
                    <img class="mb-[30px]" src="{{ env('BACKEND_URL') . '/' . $educational_resource->image_path }}">

                    <div class="text-center mb-[30px]">

                        @if ($educational_resource->resource_way == 'FILE')
                            <a class="no-effect-hover"
                                href="{{ env('BACKEND_URL') . '/' . $educational_resource->resource_path }}"
                                target="_blank"><button type="button" class="btn btn-primary">Descargar ahora
                                    {{ e_heroicon('arrow-down-tray', 'outline') }}</button></a>
                        @elseif ($educational_resource->resource_way == 'URL')
                            <a href="{{ $educational_resource->resource_url }}" target="_blank"><button type="button"
                                    class="btn btn-primary">Ir al recurso
                                    {{ e_heroicon('arrow-top-right-on-square', 'outline') }}</button></a>
                        @endif
                    </div>

                    <div class="px-[20px]">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-black m-0">{{ $educational_resource->title }}</h2>
                            </div>

                            @if ($general_options['learning_objects_appraisals'])
                                <div id="resource-avg-calification-block"
                                    class="flex gap-[10px] items-center {{ $educational_resource->average_calification ? 'block' : 'hidden' }}">
                                    <h2 id="average-calification" class="text-black m-0">
                                        {{ $educational_resource->average_calification }}</h2>
                                    <div>{{ e_heroicon('star', 'solid', '#EABA0F', 20, 20) }}</div>
                                </div>
                            @endif
                        </div>

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('document-check', 'outline', null, 20, 20) }}
                                <h5>Tipo de licencia</h5>
                            </div>
                            <div>
                                <p>{{ $educational_resource->license_type ?? 'Ninguna' }}</p>
                            </div>
                        </div>

                        @if (Auth::check() && $general_options['learning_objects_appraisals'])
                            <hr class="border-dashed border-[#ACACAC] my-[12px]">
                            <div class="grid grid-cols-2">
                                <div class="flex gap-[10px] font-roboto-bold">
                                    {{ e_heroicon('hand-thumb-up', 'outline', null, 20, 20) }}
                                    <h5>Calificar</h5>
                                </div>
                                <div class="flex stars-califications">
                                    <input type="hidden" id="avg-calification"
                                        value="{{ floor($educational_resource->average_calification) }}">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @php
                                            $color =
                                                $i <= floor($educational_resource->average_calification)
                                                    ? '#EABA0F'
                                                    : '#E4E4E4';
                                        @endphp
                                        {{ e_heroicon('star', 'solid', $color, 20, 20) }}
                                    @endfor
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <div class="flex justify-center items-center">
                    <a class="no-effect-hover" href="/doubts/educational_resource/{{ $educational_resource->uid }}"><button
                            type="button" class="btn btn-primary min-w-[304px]">¿Dudas?
                            {{ e_heroicon('chat-bubble-bottom-center-text', 'outline') }}</button></a>
                </div>
            </div>

        </div>

    </div>

    <input type="hidden" id="educational_resource_uid" value="{{ $educational_resource->uid }}">
    @include('partials.footer')
@endsection

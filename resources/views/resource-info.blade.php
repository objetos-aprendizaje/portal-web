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

                @if (!empty($educational_resource->learningResults))
                    <h2 class="text-black">
                        Resultados de aprendizaje
                    </h2>
                    <div class="mb-[40px]">
                        <ul class="list-disc pl-5">
                            @foreach ($educational_resource->learningResults as $learningResult)
                                <li>{{ $learningResult->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @switch($educational_resource->resource_way)
                    @case('IMAGE')
                        <img alt="{{ $educational_resource->title }}" class="mb-[30px]"
                            src="{{ env('BACKEND_URL') . '/' . $educational_resource->resource_path }}">
                    @break

                    @case('VIDEO')
                        <video class="mb-[30px]" controls>
                            <source src="{{ env('BACKEND_URL') . '/' . $educational_resource->resource_path }}" type="video/mp4">
                            Your browser does not support the video tag.
                        @break

                        @case('AUDIO')
                            <audio class="mb-[30px]" controls>
                                <source src="{{ env('BACKEND_URL') . '/' . $educational_resource->resource_path }}"
                                    type="audio/mpeg">
                                Your browser does not support the audio element.
                            @break;
                            @case('PDF')
                                <iframe class="mb-[30px]" title="recurso educativo"
                                    src="{{ env('BACKEND_URL') . '/' . $educational_resource->resource_path }}" width="100%"
                                    height="500px"></iframe>

                                @default
                            @endswitch
                </div>

                <div class="lg:min-w-[475px] lg:max-w-[475px] mb-[31px] lg:mb-0 rounded-[12px]">

                    <div class="shadow-xl pb-[20px] mb-[50px]">
                        <img alt="{{ $educational_resource->title }}" class="mb-[30px]"
                            src="{{ env('BACKEND_URL') . '/' . $educational_resource->image_path }}">

                        <div class="text-center mb-[30px]">

                            @if ($educational_resource->resource_way == 'FILE')
                                <button id="download-button"
                                    data-url="{{ env('BACKEND_URL') . '/' . $educational_resource->resource_path }}"
                                    type="button" class="btn btn-primary">Descargar ahora
                                    {{ e_heroicon('arrow-down-tray', 'outline') }}
                                </button>
                            @elseif ($educational_resource->resource_way == 'URL')
                                <a aria-label="enlace" data-destination="{{ $educational_resource->resource_url }}"
                                    href="{{ $educational_resource->resource_url }}" target="_blank"><button type="button"
                                        class="btn btn-primary">Ir
                                        al recurso
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
                                    <p>{{ $educational_resource->licenseType->name ?? 'Ninguna' }}</p>
                                </div>
                            </div>

                            <hr class="border-dashed border-[#ACACAC] my-[12px]">
                            <div class="grid grid-cols-2">
                                <div class="flex gap-[10px] font-roboto-bold">
                                    {{ e_heroicon('numbered-list', 'outline', null, 20, 20) }}
                                    <h5>Categorías</h5>
                                </div>
                                <div>
                                    @if ($educational_resource->categories->isEmpty())
                                        <p>Sin categorías</p>
                                    @else
                                        @foreach ($educational_resource->categories as $category)
                                            <p>{{ $category['name'] }}</p>
                                        @endforeach
                                    @endif

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

                    @if ($showDoubtsButton)
                        <div class="flex justify-center items-center">
                            <a aria-label="enlace" class="no-effect-hover"
                                href="/doubts/educational_resource/{{ $educational_resource->uid }}"><button type="button"
                                    class="btn btn-primary min-w-[304px]">¿Dudas?
                                    {{ e_heroicon('chat-bubble-bottom-center-text', 'outline') }}</button></a>
                        </div>
                    @endif
                </div>

            </div>

        </div>

        <input type="hidden" id="educational_resource_uid" value="{{ $educational_resource->uid }}">
    @endsection

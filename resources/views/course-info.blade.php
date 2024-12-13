@extends('layouts.app')
@section('content')
    <div class="container mx-auto">

        <div class="flex mt-[60px] gap-[74px] lg:flex-row flex-col mb-[156px]">

            <div class="lg:flex-grow">
                <h2 class="text-color_1 lg:mb-[69px]">
                    {{ $course->title }}
                </h2>

                <p class="mb-[40px]">
                    {{ $course->description }}
                </p>

                @if (!empty($course->blocks))
                    <div class="mb-4">
                        <h2 class="text-black">
                            Bloques
                        </h2>
                        <div>
                            <ul class="list-disc pl-5">
                                @foreach ($course->blocks as $block)
                                    <li>{{ $block->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if (!empty($learningResults))
                    <h2 class="text-black">
                        Resultados de aprendizaje
                    </h2>
                    <div>
                        <ul class="list-disc pl-5">
                            @foreach ($learningResults as $learningResult)
                                <li>{{ $learningResult->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>

            <div class="lg:min-w-[475px] lg:max-w-[475px] mb-[31px] lg:mb-0">

                <div class="shadow-xl pb-[20px] mb-[50px]">
                    <img aria-label="enlace" alt="{{ $course->title }}" class="mb-[30px] w-full h-[425px]"
                        src="{{ $course->image_path ? env('BACKEND_URL') . '/' . $course->image_path : '/images/articulo0.png' }}">

                    @if ($course->status->code == 'INSCRIPTION')
                        <div class="text-center mb-[30px]">
                            <a class="no-effect-hover" href="/cart/course/{{ $course->uid }}">
                                <button type="button" class="btn btn-primary">Inscribirme ahora
                                    {{ e_heroicon('chevron-right', 'outline') }}</button>
                            </a>
                        </div>
                    @endif

                    <div class="px-[20px]">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-black m-0">{{ $course->title }}</h2>
                            </div>

                            @if ($general_options['learning_objects_appraisals'])
                                <div class="gap-[10px] items-center {{ $course->average_calification ? 'flex' : 'hidden' }}"
                                    id="average-calification-container">
                                    <h2 id="average-calification" class="text-black m-0">
                                        {{ $course->average_calification }}
                                    </h2>
                                    <div>{{ e_heroicon('star', 'solid', '#EABA0F', 20, 20) }}</div>
                                </div>
                            @endif

                        </div>

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('academic-cap', 'outline', null, 20, 20) }}
                                <h5>Estado del curso</h5>
                            </div>
                            <p>{{ $course->status->name }}</p>
                        </div>

                        @if ($studentCourseInfo)
                            <hr class="border-dashed border-[#ACACAC] my-[12px]">
                            <div class="grid grid-cols-2">
                                <div class="flex gap-[10px] font-roboto-bold">
                                    {{ e_heroicon('document-check', 'outline', null, 20, 20) }}
                                    <h5>Tu estado</h5>
                                </div>
                                <p>{{ $studentCourseInfo->status == 'INSCRIBED' ? 'Inscrito' : 'Matriculado' }}</p>
                            </div>
                        @endif

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('computer-desktop', 'outline', null, 20, 20) }}
                                <h5>Tipo de curso</h5>
                            </div>
                            <p>{{ $course->course_type['name'] }}</p>
                        </div>

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('check-badge', 'outline', null, 20, 20) }}
                                <h5>Créditos (ECTS)</h5>
                            </div>
                            <p>{{ $course->ects_workload }}</p>
                        </div>

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('currency-dollar', 'outline', null, 20, 20) }}
                                <h5>Matrícula</h5>
                            </div>
                            @if ($course->payment_mode == 'SINGLE_PAYMENT')
                                <p>{{ number_format($course->cost ?? 0, 2) }} €</p>
                            @elseif ($course->payment_mode == 'INSTALLMENT_PAYMENT')
                                @php
                                    $totalPayments = $course->paymentTerms->sum('cost');
                                @endphp
                                <p>{{ number_format($totalPayments, 2) }} €</p>
                                @foreach ($course->paymentTerms as $paymentTerm)
                                    <div class="ml-[30px]">
                                        <h5>{{ $paymentTerm->name }}</h5>
                                    </div>
                                    <p>{{ number_format($paymentTerm->cost, 2) }} €</p>
                                @endforeach
                            @endif
                        </div>

                        @php
                            $tags = $course->tags->toArray();
                        @endphp

                        @if (!empty($tags))
                            <hr class="border-dashed border-[#ACACAC] my-[12px]">
                            <div class="grid grid-cols-2">
                                <div class="flex gap-[10px] font-roboto-bold">
                                    {{ e_heroicon('tag', 'outline', null, 20, 20) }}
                                    <h5>Etiquetas</h5>
                                </div>
                                <div>
                                    <p>{{ implode(', ',array_map(function ($tag) {return $tag['tag'];}, $tags)) }}</p>
                                </div>
                            </div>
                        @endif

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('calendar', 'outline', null, 20, 20) }}
                                <h5>Fecha de inscripción</h5>
                            </div>
                            <div>
                                <p>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $course->inscription_start_date)->format('d/m/y') }}
                                    -
                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $course->inscription_finish_date)->format('d/m/y') }}
                                </p>
                            </div>
                        </div>


                        @if ($course->validate_student_registrations || ($course->payment_mode == 'SINGLE_PAYMENT' && $course->cost > 0))
                            <hr class="border-dashed border-[#ACACAC] my-[12px]">
                            <div class="grid grid-cols-2">
                                <div class="flex gap-[10px] font-roboto-bold">
                                    {{ e_heroicon('calendar', 'outline', null, 20, 20) }}
                                    <h5>Fecha de matriculación</h5>
                                </div>
                                <div>
                                    <p>
                                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $course->enrolling_start_date)->format('d/m/y') }}
                                        -
                                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $course->enrolling_finish_date)->format('d/m/y') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('calendar', 'outline', null, 20, 20) }}
                                <h5>Fecha de realización</h5>
                            </div>
                            <div>
                                <p>
                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $course->realization_start_date)->format('d/m/y') }}
                                    -
                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $course->realization_finish_date)->format('d/m/y') }}
                                </p>
                            </div>
                        </div>

                        @if ($course->teachers->count())
                            <hr class="border-dashed border-[#ACACAC] my-[12px]">
                            <div class="grid grid-cols-2">
                                <div class="flex gap-[10px] font-roboto-bold">
                                    {{ e_heroicon('user-circle', 'outline', null, 20, 20) }}
                                    <h5>Profesorado</h5>
                                </div>
                                <div>
                                    @foreach ($course->teachers as $teacher)
                                        <p>{{ $teacher['first_name'] }} {{ $teacher['last_name'] }}</p>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">
                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('numbered-list', 'outline', null, 20, 20) }}
                                <h5>Categorías</h5>
                            </div>
                            <div>
                                @foreach ($course->categories as $category)
                                    <p>{{ $category['name'] }}</p>
                                @endforeach
                            </div>
                        </div>

                        @if ($course->center)
                            <hr class="border-dashed border-[#ACACAC] my-[12px]">
                            <div class="grid grid-cols-2">
                                <div class="flex gap-[10px] font-roboto-bold">
                                    {{ e_heroicon('map-pin', 'outline', null, 20, 20) }}
                                    <h5>Centro</h5>
                                </div>
                                <div>
                                    <p>{{ $course->center->name }}</p>
                                </div>
                            </div>
                        @endif

                        @if ($course->presentation_video_url)
                            <hr class="border-dashed border-[#ACACAC] my-[12px]">
                            <div class="grid grid-cols-2">
                                <div class="flex gap-[10px] font-roboto-bold">
                                    {{ e_heroicon('video-camera', 'outline', null, 20, 20) }}
                                    <h5>Vídeo de presentación</h5>
                                </div>
                                <div>
                                    <p><a target="_blank"
                                            href="{{ $course->presentation_video_url }}">{{ $course->presentation_video_url }}</a>
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if (Auth::check() && $general_options['learning_objects_appraisals'])
                            <hr class="border-dashed border-[#ACACAC] my-[12px]">

                            <div class="grid grid-cols-2">
                                <div class="flex gap-[10px] font-roboto-bold">
                                    {{ e_heroicon('hand-thumb-up', 'outline', null, 20, 20) }}
                                    <h5>Calificar</h5>
                                </div>
                                <div class="flex stars-califications">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @php
                                            $color = $i <= floor($course->average_calification) ? '#EABA0F' : '#E4E4E4';
                                        @endphp

                                        {{ e_heroicon('star', 'solid', $color, 20, 20) }}
                                    @endfor
                                    <input type="hidden" id="avg-calification"
                                        value="{{ floor($course->average_calification) }}">
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                @if ($showDoubtsButton)
                    <div class="flex justify-center items-center">
                        <a aria-label="enlace" class="no-effect-hover" href="/doubts/course/{{ $course->uid }}"><button
                                type="button" class="btn btn-primary min-w-[304px]">¿Dudas?
                                {{ e_heroicon('chat-bubble-bottom-center-text', 'outline') }}</button></a>
                    </div>
                @endif

            </div>
        </div>

    </div>

    <input type="hidden" id="course_uid" value="{{ $course->uid }}">
@endsection

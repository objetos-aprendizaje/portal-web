@extends('layouts.app')
@section('content')
    <div class="container mx-auto">

        <div class="flex mt-[60px] gap-[74px] lg:flex-row flex-col mb-[156px]">

            <div class="lg:flex-grow">
                <h2 class="text-color_1 lg:mb-[41px]">
                    {{ $educational_program->name }}
                </h2>

                <p class="mb-[40px]">
                    {{ $educational_program->description }}
                </p>

                <div>
                    @foreach ($educational_program->courses as $course)
                        <div class="flex mb-[30px] gap-[20px] shadow-lg">
                            <div class="w-[130px] flex-none flex justify-center">
                                <img class="w-[130px] h-[130px]"
                                    src="{{ $course->image_path ? env('BACKEND_URL') . '/' . $course->image_path : env('DEFAULT_IMAGE_ROUTE') }}"
                                    alt="">
                            </div>

                            <div class="flex flex-col justify-between py-[20px] flex-grow">
                                <h3 class="m-0">{{ $course->title }}</h3>
                                <p>{{ $course->description }}</p>

                                <hr class="border-dashed border-[#ACACAC] my-[14px]">
                                <div class="flex gap-[12px] text-color_4">
                                    <p>{{ $course->ects_workload }} ECTS</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lg:w-[475px] lg:flex-shrink-0 mb-[31px] lg:mb-0">

                <div class="shadow-xl pb-[20px] mb-[50px]">
                    <img class="mb-[30px]  h-[425px]"
                        src="{{ env('BACKEND_URL') . '/' . $educational_program->image_path }}">

                    @if ($educational_program->status_code == 'INSCRIPTION')
                        <div class="text-center mb-[30px]">
                            <a href="/cart/educational_program/{{ $educational_program->uid }}">
                                <button type="button" class="btn btn-primary">Inscribirme ahora
                                    {{ e_heroicon('chevron-right', 'outline') }}</button>
                            </a>
                        </div>
                    @endif

                    <div class="px-[20px]">
                        <div class="flex justify-between items-center gap-2">
                            <div>
                                <h2 class="text-black m-0">{{ $educational_program->name }}</h2>
                            </div>

                            @if ($general_options['learning_objects_appraisals'])
                                <div id="program-avg-calification-block"
                                    class="flex gap-[10px] items-center {{ $educational_program->average_calification ? 'block' : 'hidden' }}">
                                    <h2 id="average-calification" class="text-black m-0">
                                        {{ $educational_program->average_calification }}</h2>
                                    <div>{{ e_heroicon('star', 'solid', '#EABA0F', 20, 20) }}</div>
                                </div>
                            @endif
                        </div>

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div>
                                <h5 class="flex gap-[10px] font-roboto-bold">
                                    {{ e_heroicon('computer-desktop', 'outline', null, 20, 20) }}
                                    Tipo de curso</h5>
                            </div>
                            <div>
                                <p>{{ $educational_program->educational_program_type->name }}</p>
                            </div>
                        </div>

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('check-badge', 'outline', null, 20, 20) }}
                                <h5>Créditos (ECTS)</h5>
                            </div>
                            <div>
                                <p>{{ $educational_program->total_ects_workload }}</p>
                            </div>
                        </div>

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('currency-dollar', 'outline', null, 20, 20) }}
                                <h5>Matrícula</h5>
                            </div>
                            @if ($educational_program->payment_mode == 'SINGLE_PAYMENT')
                                <p>{{ number_format($educational_program->cost ?? 0, 2) }} €</p>
                            @elseif ($educational_program->payment_mode == 'INSTALLMENT_PAYMENT')
                                @php
                                    $totalPayments = $educational_program->paymentTerms->sum('cost');
                                @endphp
                                <p>{{ number_format($totalPayments, 2) }} €</p>
                                @foreach ($educational_program->paymentTerms as $paymentTerm)
                                    <div class="ml-[30px]">
                                        <h5>{{ $paymentTerm->name }}</h5>
                                    </div>
                                    <p>{{ number_format($paymentTerm->cost, 2) }} €</p>
                                @endforeach
                            @endif
                        </div>


                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('calendar', 'outline', null, 20, 20) }}
                                <h5>Fecha de inscripción</h5>
                            </div>
                            <div>
                                <p>
                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $educational_program->inscription_start_date)->format('d/m/y') }}
                                    -
                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $educational_program->inscription_finish_date)->format('d/m/y') }}
                                </p>
                            </div>
                        </div>

                        <hr class="border-dashed border-[#ACACAC] my-[12px]">

                        <div class="grid grid-cols-2">
                            <div class="flex gap-[10px] font-roboto-bold">
                                {{ e_heroicon('user-circle', 'outline', null, 20, 20) }}
                                <h5>Profesorado</h5>
                            </div>
                            <div>
                                @foreach ($teachers as $teacher)
                                    <p>{{ $teacher->first_name }} {{ $teacher->last_name }}</p>
                                @endforeach
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
                                    @for ($i = 1; $i <= 5; $i++)
                                        @php
                                            $color =
                                                $i <= floor($educational_program->average_calification)
                                                    ? '#EABA0F'
                                                    : '#E4E4E4';
                                        @endphp
                                        {{ e_heroicon('star', 'solid', $color, 20, 20) }}
                                    @endfor
                                    <input type="hidden" id="avg-calification"
                                        value="{{ floor($educational_program->average_calification) }}">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-center items-center">
                    <a class="no-effect-hover" href="/doubts/educational_program/{{ $educational_program->uid }}"><button
                            type="button" class="btn btn-primary min-w-[304px]">¿Dudas?
                            {{ e_heroicon('chat-bubble-bottom-center-text', 'outline') }}</button></a>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="educational_program_uid" value="{{ $educational_program->uid }}" />

    @include('partials.footer')
@endsection

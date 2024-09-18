@extends('layouts.app')
@section('content')
    <div class="dudas-img ">
        <div class="flex flex-col gap-[22px] m-[40px] lg:mx-[293px] lg:mt-[80px] justify-center">
            <div class="dudas-icon flex justify-center">
                {{ e_heroicon('chat-bubble-left-right', 'outline', 'white', 90, 90) }}
            </div>

            <h1 class="text-[32px] text-center tracking-[-0.64px] font-bold text-white">¿Dudas de nuestros cursos?</h1>

            <div class="text-center text-white max-w-[508px]">
                Haznos saber tus inquietudes acerca de nuestros cursos y te daremos respuesta lo antes posible
            </div>
        </div>

        <form id="form-doubt" prevent-default>

            <div class="dudas-container">
                @csrf
                <div class="flex gap-[20px] justify-stretch flex-col lg:flex-row">
                    <div class="flex flex-col w-full">
                        <label for="name" class=" ml-[24px] leading-6">Nombre</label>
                        <input type="text" class="poa-input input-text h-[60px]" id="name" name="name">

                    </div>
                    <div class="flex flex-col w-full">
                        <label class="ml-[24px] leading-6" for="email">Email</label>
                        <input type="text" class="poa-input input-text h-[60px] px-[40px]" name="email" id="email">
                    </div>
                </div>
                <div class="flex flex-col gap-[8px]">
                    <label class=" ml-[24px] leading-6 w-full" for="message">¿Que deseas saber?</label>

                    <textarea class="input-basic p-[25px] h-[180px] rounded-[40px]" name="message" id="message"></textarea>
                </div>

                <div class="flex">
                    <button class="btn btn-primary btn-big ml-auto">
                        Enviar
                        {{ e_heroicon('arrow-up-right', 'outline', 'white') }}
                    </button>
                </div>

            </div>

            <input type="hidden" name="uid" value="{{ $uid }}"/>
            <input type="hidden" name="learning_object_type" value="{{ $learning_object_type }}"/>

        </form>


    </div>
@endsection

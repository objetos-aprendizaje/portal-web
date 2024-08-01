@extends('layouts.app')
@section('content')
    <div class="sugerencias-img ">
        <div class="flex flex-col gap-[22px] m-[40px] lg:mx-[293px] lg:mt-[80px] justify-center">
            <div class="sugerencias-icon flex justify-center">
                {{ e_heroicon('inbox-arrow-down', 'outline', 'white', 90, 90) }}
            </div>
            <h1 class="text-[32px] text-center tracking-[-0.64px] font-bold text-white">Buzon de sugerencias</h1>
            <div class="text-center text-white max-w-[508px]">
                ¿Crees que se puede mejorar algo? Háznoslo saber.
            </div>
        </div>

        <form id="form-suggestions" prevent-default>
            @csrf
            <div
                class="lg:w-[1075px] mx-[10px] lg:mx-[182px] p-[30px] h-auto rounded-[20px] flex flex-col gap-[25px] bg-white mt-[60px] mb-12 shadow-xl">
                <div class="flex gap-[20px] justify-stretch flex-col lg:flex-row">
                    <div class="flex flex-col w-full">
                        <label for="name" class="ml-[24px] leading-6">Nombre</label>
                        <input type="text" class="poa-input poa-input-height-amplified input-text h-[60px]"
                            id="name" name="name">

                    </div>
                    <div class="flex flex-col w-full">
                        <label class="ml-[24px] leading-6" for="email">Email</label>
                        <input type="text" class="poa-input poa-input-height-amplified input-text h-[60px] px-[40px]"
                            name="email" id="email">
                    </div>
                </div>
                <div class="flex flex-col gap-[8px]">
                    <label class="mx-[24px] leading-6 w-full">Comentarios o sugerencias</label>
                    <textarea class="poa-input poa-input-amplified-textarea" name="message" id="message"></textarea>
                </div>
                <div class="flex">
                    <button type="submit" class="btn btn-primary btn-big ml-auto">
                        Enviar
                        {{ e_heroicon('arrow-up-right', 'outline', 'white') }}
                    </button>
                </div>
            </div>

        </form>
    </div>


    @include('partials.footer')
@endsection

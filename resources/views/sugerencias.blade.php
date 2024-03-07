@extends('layouts.app')
@section('content')
    <div class="sugerencias-img ">
        <div class="flex flex-col gap-[22px] m-[40px] lg:mx-[293px] lg:mt-[80px] justify-center">
            <div class="sugerencias-icon flex justify-center">
                {{ e_heroicon('inbox-arrow-down', 'outline', 'white') }}
            </div>
            <h1 class="text-[32px] text-center tracking-[-0.64px] font-bold text-white">Buzon de sugerencias</h1>
            <div class="text-center text-white max-w-[508px]">
                Lorem ipsum dolor sit amet consectetur. At integer et parturient commodo. Egestas dolor suscipit fringilla
                senectus.
            </div>
        </div>
        <div class="sugerencias-container">
            <div class="flex gap-[20px] justify-stretch flex-col lg:flex-row">
                <div class="flex flex-col w-full">
                    <h1 class="mx-[24px] leading-6	">Nombre</h1>
                    <input type="text" class="input-text h-[60px]">
                </div>

            </div>
            <div class="flex flex-col gap-[8px]">
                <h1 class="mx-[24px] leading-6 w-full">Comentarios o sugerencias</h1>

                <textarea class="border-[1.5px] border-solid border-[#2B4C7E] w-full h-[180px] resize-none rounded-[20px] p-[15px]"></textarea>
            </div>
            <div class="flex">
                <button class="sugerencias-send-button">
                    Enviar
                    {{ e_heroicon('arrow-up-right', 'outline', 'white') }}
                </button>
            </div>
        </div>


    </div>
@endsection

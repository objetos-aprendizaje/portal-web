@extends('layouts.app')
@section('content')
    <div class="flex flex-wrap gap-[50px] 2xl:gap-[158px] px-[20px] lg:px-[120px] py-[56px] justify-around">
        <div class="flex flex-col gap-[25px] max-w-[610px]">
            <h1 class="text-color_1 text-[24px] font-bold">Lorem ipsum dolor sit amet consectetur. Ultricies viverra id nisl
                elit interdum.</h1>
            <p>Lorem ipsum dolor sit amet consectetur. Platea aliquet pretium enim cursus. Pretium ut etiam id et eu dui
                nulla. Non gravida faucibus vitae integer sem egestas metus aliquet laoreet. In pellentesque maecenas donec
                volutpat quam malesuada. At nisl enim mi metus egestas phasellus.</p>
            <p>Pharetra blandit auctor nisi platea vel proin quis neque. Ullamcorper cursus diam id risus arcu. Elementum
                nullam lobortis adipiscing porttitor dui vulputate ac eu. Ac varius iaculis in ipsum facilisi justo purus
                in. Vitae blandit augue nullam nunc tempus turpis maecenas nunc. Vitae non malesuada ac faucibus. Id
                tincidunt cursus auctor ornare neque vitae congue.</p>
        </div>
        <div class="flex flex-col gap-[50px]">
            <div class="max-w-[475px] bg-white xl:border xl:border-gray-200 xl:rounded-lg xl:shadow flex flex-col gap-[30px] pb-4">
                <div class="max-h-[424px]">
                    <img class="xl:rounded-t-lg" src="/images/resource.png" alt="" />
                </div>
                <div class="flex">
                    <button class="resource-send-button">
                        Descargar Ahora
                        {{ e_heroicon('arrow-down-tray', 'outline', 'white') }}
                    </button>
                </div>
                <div class="px-0 xl:px-[20px] flex flex-col gap-[12px] mt-14 xl:mt-0">
                    <div class="flex justify-between text-[--black-title-color] text-[24px] font-bold">
                        <h1>Lorem Ipsum dolor</h1>
                        <div class="resource-card-star flex items-center gap-[10px]">
                            <span class="text-[--black-title-color] leading-[22px]">4.9</span>
                            {{ e_heroicon('star', 'solid', '#EABA0F') }}
                        </div>
                    </div>
                    <hr class="border-t border-gray-500 border-dashed">
                    <div class="flex justify-between text-[--black-title-color]">
                        <div class="info-resource flex items-center gap-[10px]">
                            {{ e_heroicon('academic-cap', 'solid', 'black') }}
                            <span class="text-[--black-title-color] font-bold leading-[22px]">Curso</span>
                        </div>
                        <p>Lorem ipsum dolor sit</p>
                    </div>
                    <hr class="border-t border-gray-500 border-dashed">
                    <div class="flex justify-between text-[--black-title-color]">
                        <div class="info-resource flex items-center gap-[10px]">
                            {{ e_heroicon('document-check', 'outline', 'black') }}
                            <span class="text-[--black-title-color] font-bold leading-[22px]">Tipo de licencia</span>
                        </div>
                        <p>GPL3</p>
                    </div>
                    <hr class="border-t border-gray-500 border-dashed">
                    <div class="flex justify-between text-[--black-title-color]">
                        <div class="info-resource flex items-center gap-[10px]">
                            {{ e_heroicon('hand-thumb-up', 'outline', 'black') }}
                            <span class="text-[--black-title-color] font-bold leading-[22px]">Calificar</span>
                        </div>
                        <div class="cards-stars flex">
                            {{ e_heroicon('star', 'solid', '#EABA0F') }}
                            {{ e_heroicon('star', 'solid', '#EABA0F') }}
                            {{ e_heroicon('star', 'solid', '#EABA0F') }}
                            {{ e_heroicon('star', 'solid', '#EABA0F') }}
                            {{ e_heroicon('star', 'solid', '#E4E4E4') }}
                        </div>
                    </div>
                    <hr class="border-t border-gray-500 border-dashed">

                </div>
            </div>

            <div class="flex ">
                <button class="resource-send-button w-[304px]">
                    ¿Dudas?
                    {{ e_heroicon('chat-bubble-bottom-center-text', 'outline', 'white') }}
                </button>
            </div>
        </div>



    </div>
@endsection

<div class="mySlides fade relative">
    <div class="bg-image" style="background-image:url({{ $image_path }})"></div>
    <div
        class="absolute top-[0px] left-1/2 transform -translate-x-1/2 w-[300px] lg:w-[855px] mx-auto flex flex-col text-center text-white ">
        <h1 style="color: {{ $colorFont ?? '#fff' }}" class="text-[28px] lg:text-[54px] font-bold leading-[120%] mb-[39px] mt-[80px] line-clamp line-clamp-3">
            {{ $title }}
        </h1>

        <p style="color: {{ $colorFont ?? '#fff' }}" class="w-[258px] lg:w-[508px] mx-auto font-[400] mb-[39px] line-clamp line-clamp-2 text-white">
            {{ $description }}
        </p>

        @if (isset($registerButton) && $registerButton)
            <a href="#" class="border mx-auto rounded-[6px] justify-center text-white button-register-home">
                <div class="flex gap-[10px] px-[20px] py-[10px] m-auto">
                    <span>Registrarme</span>
                    {{ e_heroicon('chevron-right', 'outline', 'white') }}
                </div>
            </a>
        @endif

    </div>
</div>

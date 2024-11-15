@extends('non_authenticated.app')
@section('content')
    <section class="md:flex hidden">

        <div class="w-1/2">
            <img alt="reset" id="image-background" src="{{ asset('data/images/background_login.png') }}" class="object-cover w-full h-screen">
        </div>

        <div class="w-1/2 justify-center flex items-center">
            <div class="w-[530px] mb-[25px]">
                <div class="rounded-[20px] border py-[20px] px-[40px]">
                    <img alt="reset" class="mx-auto block max-w-[211px] max-h-[80px] mb-[15px]"
                        src="{{ $general_options['poa_logo_1'] ? env('BACKEND_URL') . '/' .  $general_options['poa_logo_1'] : asset('data/images/logo_login.jpg') }}" />

                        <div class="text-[28px] font-bold text-center mb-[15px]">Introduce tu email</div>

                        <p class="text-center mb-[20px]">Introduce tu dirección de correo electrónico para poder enviarte notificaciones por email</p>

                        <form id="getEmailDesktop" action="/get-email/add" method="POST">
                            @csrf
                            <div class="mb-[25px]">
                                <div class="flex flex-col mb-[20px]">
                                    <label class="px-3 mb-[8px]">Correo</label>
                                    <input
                                        class="border-[1.5px] border-solid border-primary rounded-full p-3 focus:border-primary h-[60px]"
                                        type="text" name="email" value="" />
                                </div>

                                <div class="flex flex-col mb-[20px]">
                                    <label class="px-3 mb-[8px]">Vuelva a escribir el Correo</label>
                                    <input
                                        class="border-[1.5px] border-solid border-primary rounded-full p-3 focus:border-primary h-[60px]"
                                        type="text" name="email_verification" value="" />
                                </div>

                                @if ($errors->any())
                                    <div class="bg-[#ff605814] py-[12px] px-[27px] rounded-[8px] mb-[15px]">
                                        <p>Los siguientes campos son erróneos:</p>
                                        <ul class="list-disc">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>

                                    </div>
                                @endif

                                <button title="acceder" type="submit"
                                class="btn bg-color_1 text-white hover:bg-color_2 w-full h-[60px]">Acceder
                                {{ e_heroicon('arrow-up-right', 'outline') }}</button>

                            </div>

                        </form>

                </div>
            </div>

        </div>

    </section>

    <section class="md:hidden p-[20px]">
        <img alt="reset" class="mx-auto block max-w-[146px] h-[51px] mb-[15px]"
            src="{{ $general_options['poa_logo_1'] ? env('BACKEND_URL') . '/' . $general_options['poa_logo_1'] : asset('data/images/logo_login.jpg') }}" />

        <div class="text-[28px] font-bold text-center mb-[15px]">Introduce tu email</div>

        <div class="mb-[25px]">
            <form id="getEmailMobile" action="/get-email/add" method="POST">
                @csrf

                <div class="mb-[25px]">

                    <div class="flex flex-col mb-[20px]">

                        <div class="flex flex-col mb-[20px]">
                            <label class="px-3 mb-[8px]">Correo</label>
                            <input
                                class="border-[1.5px] border-solid border-primary rounded-full p-3 focus:border-primary h-[60px]"
                                type="text" name="email" value="" />
                        </div>

                        <div class="flex flex-col mb-[20px]">
                            <label class="px-3 mb-[8px]">Vuelva a escribir el Correo</label>
                            <input
                                class="border-[1.5px] border-solid border-primary rounded-full p-3 focus:border-primary h-[60px]"
                                type="text" name="email_verification" value="" />
                        </div>

                        <button title="acceder" type="submit"
                                class="btn bg-color_1 text-white hover:bg-color_2 w-full h-[60px]">Acceder
                                {{ e_heroicon('arrow-up-right', 'outline') }}</button>

                    </div>

            </form>
        </div>

    </section>
@endsection

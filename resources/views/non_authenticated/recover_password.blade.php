@extends('non_authenticated.app')
@section('content')
    <section class="md:flex hidden">

        <div class="w-1/2">
            <img alt="recuperar" id="image-background" src="{{ asset('data/images/background_login.png') }}"
                class="object-cover w-full h-screen">
        </div>

        <div class="w-1/2 justify-center flex items-center">
            <div class="w-[530px] mb-[25px]">
                <div class="rounded-[20px] border py-[20px] px-[40px]">
                    <img alt="recuperar" class="mx-auto block max-w-[211px] max-h-[80px] mb-[15px]"
                        src="{{ $general_options['poa_logo_1'] ? env('BACKEND_URL') . '/' . $general_options['poa_logo_1'] : asset('images/logo_login.jpg') }}" />

                    <div class="text-[28px] font-bold text-center mb-[15px]">¿Olvidaste la contraseña?</div>
                    <div class="mb-[30px]">Introduce la dirección de correo electrónico y te enviaremos un enlace para
                        restablecer tu contraseña.</div>

                    <form id="recoverPasswordFormDesktop" action="/recover_password/send" method="POST" prevent-default>
                        @csrf
                        <div class="mb-[25px]">
                            <div class="flex flex-col mb-[20px]">
                                <label class="px-3 mb-[8px]">Correo</label>
                                <input aria-label="email"
                                    class="border-[1.5px] border-solid border-color_1 rounded-full p-3 focus:border-color_1 h-[60px]"
                                    type="text" name="email" />
                            </div>


                            <button type="submit"
                                class="btn bg-color_1 text-white hover:bg-color_2 w-full h-[60px]">Restablecer
                                contraseña
                                {{ e_heroicon('arrow-up-right', 'outline') }}</button>

                        </div>

                        <p class="text-center">Volver a <a aria-label="enlace" href="{{ route('login') }}">Iniciar sesión</a></p>

                    </form>

                </div>
            </div>

        </div>

    </section>

    <section class="md:hidden p-[20px]">
        <img alt="recuperar" class="mx-auto block max-w-[146px] h-[51px] mb-[15px]"
            src="{{ $general_options['poa_logo_1'] ? env('BACKEND_URL') . '/' . $general_options['poa_logo_1'] : asset('images/logo_login.jpg') }}" />

        <div class="text-[28px] font-bold text-center mb-[15px]">¿Olvidaste la contraseña?</div>

        <div class="mb-[25px]">
            <form id="recoverPasswordFormMobile" action="/recover_password/send" method="POST" prevent-default>
                @csrf

                <div class="mb-[25px]">

                    <div class="flex flex-col mb-[20px]">
                        <label class="px-3 mb-[8px]">Correo</label>
                        <input aria-label="email"
                            class="border-[1.5px] border-solid border-color_1 rounded-full p-3 focus:border-color_1 h-[60px]"
                            type="text" name="email" />
                    </div>

                    <button type="submit" class="btn bg-color_1 text-white hover:bg-color_2 w-full h-[60px]">Restablecer
                        contraseña
                        {{ e_heroicon('arrow-up-right', 'outline') }}</button>

                </div>
                <p class="text-center">Volver a <a aria-label="enlace" href="{{ route('login') }}">Iniciar sesión</a></p>

            </form>
        </div>

    </section>
@endsection

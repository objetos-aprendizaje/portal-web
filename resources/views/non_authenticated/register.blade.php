@extends('non_authenticated.app')
@section('content')
    <section class="md:flex hidden">

        <div class="w-1/2">
            <img alt="registro" id="image-background" src="{{ asset('data/images/background_login.png') }}" class="object-cover w-full">
        </div>

        <div class="w-1/2 justify-center flex items-center">
            <div class="w-[530px] mb-[25px]">
                <div class="rounded-[20px] border py-[20px] px-[40px]">
                    @if (app('general_options')['poa_logo_1'])
                        <img alt="registro" class="mx-auto block max-w-[211px] max-h-[80px] mb-[15px]"
                            src="{{ env('BACKEND_URL') . '/' . app('general_options')['poa_logo_1'] }}" />
                    @endif

                    <h1 class="text-[32px] text-center mb-[15px]">Registrarme</h1>

                    <p class="text-center mb-[20px]">¿Ya tienes una cuenta? <a aria-label="enlace" class="text-color_1" href="/login">Inicia
                            Sesión</a></p>

                    <form id="registerFormDesktop" action="/register/submit" method="POST">
                        @csrf
                        <div class="mb-[25px]">

                            <div class="flex gap-[20px] mb-[20px]">
                                <div class="flex flex-col w-1/2">
                                    <label class="px-3 mb-[8px]">Nombre</label>
                                    <input aria-label="Nombre"
                                        class="border-[1.5px] border-solid border-color_1 rounded-full p-3 focus:border-color_1 h-[50px]"
                                        type="text" name="first_name" value="{{ old('first_name') }}" />
                                </div>

                                <div class="flex flex-col w-1/2">
                                    <label class="px-3 mb-[8px]">Apellidos</label>
                                    <input aria-label="apellidos"
                                        class="border-[1.5px] border-solid border-color_1 rounded-full p-3 focus:border-color_1 h-[50px]"
                                        type="text" name="last_name" value="{{ old('last_name') }}" />
                                </div>
                            </div>

                            <div class="flex flex-col mb-[20px]">
                                <label class="px-3 mb-[8px]">Correo</label>
                                <input aria-label="email"
                                    class="border-[1.5px] border-solid border-color_1 rounded-full p-3 focus:border-color_1 h-[50px]"
                                    type="text" name="email" value="{{ old('email') }}" />
                            </div>

                            <div class="flex flex-col mb-[20px]">
                                <label class="px-3 mb-[8px]">Contraseña</label>
                                <input aria-label="contraseña" class="border-[1.5px] border-solid border-color_1 rounded-full h-[50px] p-3"
                                    name="password" type="password" />
                            </div>

                            <div class="flex flex-col mb-[20px]">
                                <label class="px-3 mb-[8px]">Confirma la contraseña</label>
                                <input aria-label="confirmar contraseña" class="border-[1.5px] border-solid border-color_1 rounded-full h-[50px] p-3"
                                    name="password_confirmation" type="password" />
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

                            <button title="registrar" type="submit"
                                class="btn bg-color_1 text-white hover:bg-color_2 w-full h-[60px]">Registrarme
                                {{ e_heroicon('arrow-up-right', 'outline') }}</button>

                        </div>

                        @if (
                            $urlCas ||
                                $urlRediris ||
                                $parameters_login_systems['facebook_login_active'] ||
                                $parameters_login_systems['twitter_login_active'] ||
                                $parameters_login_systems['linkedin_login_active'] ||
                                $parameters_login_systems['google_login_active']
                        )
                            <div class="flex items-center justify-center space-x-2 mb-[25px]">
                                <div class="border-t w-full"></div>
                                <div>O</div>
                                <div class="border-t w-full"></div>
                            </div>
                        @endif

                        @if ($urlCas)
                            <a aria-label="enlace" class="no-effect-hover" href="{{ $urlCas }}">
                                <div class="flex justify-center mb-[25px]">
                                    <div
                                        class="inline-flex border rounded-full items-center justify-center pl-[6px] pr-[14px] py-[6px] gap-2 cursor-pointer hover:border-color_1">
                                        <div>
                                            <img alt="registro" src="{{ asset('/data/images/logo_min_boton_login.png') }}"
                                                class="w-[40px] h-[40px] mx-auto rounded-full  block" />
                                        </div>

                                        <div class="border-l h-10"></div>

                                        <div>
                                            <p class="font-bold">ACCESO UNIVERSIDAD</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endif

                        <div class="flex justify-center gap-[32px]">
                            @if ($parameters_login_systems['facebook_login_active'])
                                <a aria-label="enlace" class="no-effect-hover" href="/auth/facebook">
                                    <button title="facebook login" type="button"
                                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                                        <img alt="registro" class="w-[45px] h-[45px]" src="data/images/login_icons/facebook.png" />
                                    </button>
                                </a>
                            @endif

                            @if ($parameters_login_systems['twitter_login_active'])
                                <a aria-label="enlace" class="no-effect-hover" href="/auth/twitter">
                                    <button title="twitter login" type="button"
                                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                                        <img alt="registro" class="w-[32px] h-[32px]" src="data/images/login_icons/x_icon.png" />
                                    </button>
                                </a>
                            @endif

                            @if ($parameters_login_systems['linkedin_login_active'])
                                <a aria-label="enlace" class="no-effect-hover" href="/auth/linkedin">
                                    <button title="linkedin login" type="button"
                                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                                        <img alt="registro" class="w-[32px] h-[32px]" src="data/images/login_icons/linkedin_icon.png" />
                                    </button>
                                </a>
                            @endif

                            @if ($parameters_login_systems['google_login_active'])
                                <a aria-label="enlace" class="no-effect-hover" href="/auth/google">
                                    <button title="google login" type="button"
                                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                                        <img alt="registro" class="w-[32px] h-[32px]" src="data/images/login_icons/google_icon.png" />
                                    </button>
                                </a>
                            @endif

                            @if ($urlRediris)
                                <a aria-label="enlace" class="no-effect-hover" href="{{ $urlRediris }}">
                                    <button title="rediris login" type="button"
                                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                                        <img alt="registro" class="w-[32px] h-[32px]" src="data/images/login_icons/rediris.png" />
                                    </button>
                                </a>
                            @endif

                        </div>

                    </form>

                    @if ($cert_login != '')
                        <div class="text-center p-4"><a aria-label="enlace"
                                href="https://{{ env('DOMINIO_CERTIFICADO') }}/certificate-access">Acceso mediante
                                Certificado Digital</a></div>
                    @endif

                </div>
            </div>

        </div>

    </section>

    <section class="md:hidden p-[20px]">
        @if (app('general_options')['poa_logo_1'])
            <img alt="registro" class="mx-auto block max-w-[146px] h-[51px] mb-[15px]"
                src="{{ env('BACKEND_URL') . '/' . app('general_options')['poa_logo_1'] }}" />
        @endif

        <div class="text-[28px] font-bold text-center mb-[15px]">Registrarme</div>

        <div class="mb-[25px]">
            <form id="registerFormMobile" action="/register/submit" method="POST">
                @csrf

                <div class="flex flex-col mb-[20px]">
                    <label class="px-3 mb-[8px]">Nombre</label>
                    <input aria-label="nombre"
                        class="border-[1.5px] border-solid border-color_1 rounded-full h-[50px] p-3 focus:border-color_1 "
                        type="text" name="first_name" value="{{ old('first_name') }}" />
                </div>

                <div class="flex flex-col mb-[20px]">
                    <label class="px-3 mb-[8px]">Apellidos</label>
                    <input aria-label="apellidos"
                        class="border-[1.5px] border-solid border-color_1 rounded-full h-[50px] p-3 focus:border-color_1 "
                        type="text" name="last_name" value="{{ old('last_name') }}" />
                </div>

                <div class="flex flex-col mb-[20px]">
                    <label class="px-3 mb-[8px]">Correo</label>
                    <input aria-label="email"
                        class="border-[1.5px] border-solid border-color_1 rounded-full h-[50px] p-3 focus:border-color_1 "
                        type="text" name="email" value="{{ old('email') }}" />
                </div>

                <div class="flex flex-col mb-[20px]">
                    <label class="px-3 mb-[8px]">Contraseña</label>
                    <input aria-label="contraseña" class="border-[1.5px] border-solid border-color_1 rounded-full h-[50px] p-3" name="password"
                        type="password" />
                </div>

                <div class="flex flex-col mb-[20px]">
                    <label class="px-3 mb-[8px]">Confirmar contraseña</label>
                    <input aria-label="confirmar contraseña" class="border-[1.5px] border-solid border-color_1 rounded-full h-[50px] p-3"
                        name="password_confirmation" type="password" />
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

                <button title="registro" type="submit" class="btn bg-color_1 text-white hover:bg-color_2 w-full h-[60px]">Registrarme
                    {{ e_heroicon('arrow-up-right', 'outline') }}</button>
            </form>
        </div>

        @if (
            $urlCas ||
                $urlRediris ||
                $parameters_login_systems['facebook_login_active'] ||
                $parameters_login_systems['twitter_login_active'] ||
                $parameters_login_systems['linkedin_login_active'] ||
                $parameters_login_systems['google_login_active']
        )
            <div class="flex items-center justify-center space-x-2 mb-[25px]">
                <div class="border-t w-full"></div>
                <div>O</div>
                <div class="border-t w-full"></div>
            </div>
        @endif

        @if ($urlCas)
            <a aria-label="enlace" class="no-effect-hover" href="{{ $urlCas }}">
                <div class="flex justify-center mb-[25px] cursor-pointer ">
                    <div
                        class="hover:border-color_1 inline-flex border rounded-full items-center justify-center pl-[6px] pr-[14px] py-[6px] gap-2">
                        <div>
                            <img alt="registro" src="{{ asset('data/images/logo_min_boton_login.png') }}"
                                class="w-[40px] h-[40px] mx-auto rounded-full  block" />
                        </div>

                        <div class="border-l h-10"></div>

                        <div>
                            <p class="font-bold">ACCESO UNIVERSIDAD</p>
                        </div>
                    </div>
                </div>
            </a>
        @endif

        <div class="flex justify-center gap-[32px] flex-wrap">
            @if ($parameters_login_systems['facebook_login_active'])
                <a aria-label="enlace" class="no-effect-hover" href="/auth/facebook">
                    <button title="facebook login" type="button"
                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                        <img alt="registro" class="w-[45px] h-[45px]" src="data/images/login_icons/facebook.png" />
                    </button>
                </a>
            @endif

            @if ($parameters_login_systems['twitter_login_active'])
                <a aria-label="enlace" class="no-effect-hover" href="/auth/twitter">
                    <button title="twitter login"type="button"
                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                        <img alt="registro" class="w-[32px] h-[32px]" src="data/images/login_icons/x_icon.png" />
                    </button>
                </a>
            @endif

            @if ($parameters_login_systems['linkedin_login_active'])
                <a aria-label="enlace" class="no-effect-hover" href="/auth/linkedin">
                    <button title="linkedin login" type="button"
                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                        <img alt="registro" class="w-[32px] h-[32px]" src="data/images/login_icons/linkedin_icon.png" />
                    </button>
                </a>
            @endif

            @if ($parameters_login_systems['google_login_active'])
                <a aria-label="enlace" class="no-effect-hover" href="/auth/google">
                    <button title="google login" type="button"
                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                        <img alt="registro" class="w-[32px] h-[32px]" src="data/images/login_icons/google_icon.png" />
                    </button>
                </a>
            @endif

            @if ($urlRediris)
                <a aria-label="enlace" class="no-effect-hover" href="{{ $urlRediris }}">
                    <button title="rediris login" type="button"
                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                        <img alt="registro" class="w-[32px] h-[32px]" src="data/images/login_icons/rediris.png" />
                    </button>
                </a>
            @endif

        </div>

    </section>
@endsection

@extends('non_authenticated.app')
@section('content')
    <section class="md:flex hidden gap-2">

        <div class="w-1/2">
            <img alt="login" id="image-background" src="{{ asset('data/images/background_login.png') }}"
                class="object-cover w-full h-screen">
        </div>

        <div class="w-1/2 justify-center flex items-center">
            <div class="max-w-[530px] w-full mb-[25px]">
                <div class="rounded-[20px] border py-[20px] px-[40px]">
                    @if (app('general_options')['poa_logo_1'])
                        <img alt="login" class="mx-auto block max-w-[211px] max-h-[80px] mb-[15px]"
                            src="{{ env('BACKEND_URL') . '/' . app('general_options')['poa_logo_1'] }}" />
                    @endif

                    <h1 class="text-[32px] text-center mb-[15px]">Inicia sesión</h1>

                    @if ($general_options['registration_active'])
                        <p class="text-center mb-[20px]">¿No tienes cuenta? <a class="text-color_1"
                                href="/register">Regístrate</a></p>
                    @endif


                    @if (session('sent_email_recover_password'))
                        <div class="bg-[#E7ECF3] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
                            <p>Se ha enviado un link para reestablecer la contraseña</p>
                            <p>
                                ¿No has recibido nada? <a href="javascript:void(0)"
                                    data-email-account="{{ session('email') }}"
                                    class="text-color_1 resend-email-confirmation">Reenviar email</a>
                            </p>
                        </div>
                    @endif

                    @if (session('account_created'))
                        <div class="bg-[#E7ECF3] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
                            <p>Su cuenta ha sido creada correctamente. Por favor, verifíquela con el email de confirmación
                                que acaba de recibir. Si no lo encuentra, revise la carpeta de spam. </p>
                            <p>¿No has recibido el email?
                                <a aria-label="enlace" href="javascript:void(0)" data-email-account="{{ session('email') }}"
                                    class="text-color_1 resend-email-confirmation">Reenviar
                                    email de
                                    confirmación</a>
                            </p>
                        </div>
                    @endif

                    @if (session('verify_link_expired'))
                        <div class="bg-[#ff605814] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
                            <p>El link de confirmación ha expirado</p>
                            <p>
                                <a aria-label="enlace" href="javascript:void(0)" data-email-account="{{ session('email') }}"
                                    class="text-color_1 resend-email-confirmation">Reenviar email de
                                    confirmación</a>
                            </p>
                        </div>
                    @endif

                    @if (session('email_verified'))
                        <div class="bg-[#E7ECF3] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
                            <p>Has verificado correctamente tu cuenta. Ahora puedes iniciar sesión.</p>
                        </div>
                    @endif

                    @if (session('user_not_found'))
                        <div class="bg-[#ff605814] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
                            <p>No existe ninguna cuenta con esas credenciales</p>
                        </div>
                    @endif

                    @if (session('user_not_verified'))
                        <div class="bg-[#ff605814] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
                            <p>Su cuenta no está verificada</p>
                            <p>¿No has recibido el email?
                                <a aria-label="enlace" href="javascript:void(0)" data-email-account="{{ session('email') }}"
                                    class="text-color_1 resend-email-confirmation">Reenviar
                                    email de
                                    confirmación</a>
                            </p>
                        </div>
                    @endif

                    <form id="loginFormDesktop" action="/login/authenticate" method="POST">
                        @csrf
                        <div class="mb-[25px]">
                            <div class="flex flex-col mb-[20px]">
                                <label class="px-3 mb-[8px]" for="email">Correo</label>
                                <input aria-label="email"
                                    class="border-[1.5px] border-solid border-color_1 rounded-full p-3 focus:border-color_1 h-[60px]"
                                    type="text" name="email" id="email" />
                            </div>

                            <div class="flex flex-col mb-[8px]">
                                <label class="px-3 mb-[8px]" for="password">Contraseña</label>
                                <input aria-label="contraseña"
                                    class="border-[1.5px] border-solid border-color_1 rounded-full h-[60px] p-3"
                                    name="password" type="password" id="password" />
                            </div>

                            <div class="block px-3 mb-[20px]">
                                <a aria-label="enlace" href="{{ route('recover-password') }}" id="recover-password"
                                    class="text-color_1 text-[16px]">¿Olvidaste la
                                    contraseña?</a>
                            </div>

                            <button title="acceder" type="submit"
                                class="btn bg-color_1 text-white hover:bg-color_2 w-full h-[60px]">Iniciar sesión
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
                                            <img alt="login" src="{{ asset('/data/images/logo_min_boton_login.png') }}"
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
                                        <img alt="login" class="w-[45px] h-[45px]"
                                            src="data/images/login_icons/facebook.png" />
                                    </button>
                                </a>
                            @endif

                            @if ($parameters_login_systems['twitter_login_active'])
                                <a aria-label="enlace" class="no-effect-hover" href="/auth/twitter">
                                    <button title="twitter login" type="button"
                                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                                        <img alt="login" class="w-[32px] h-[32px]"
                                            src="data/images/login_icons/x_icon.png" />
                                    </button>
                                </a>
                            @endif

                            @if ($parameters_login_systems['linkedin_login_active'])
                                <a aria-label="enlace" class="no-effect-hover" href="/auth/linkedin-openid">
                                    <button title="linkedin login" type="button"
                                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                                        <img alt="login" class="w-[32px] h-[32px]"
                                            src="data/images/login_icons/linkedin_icon.png" />
                                    </button>
                                </a>
                            @endif

                            @if ($parameters_login_systems['google_login_active'])
                                <a aria-label="enlace" class="no-effect-hover" href="/auth/google">
                                    <button title="google login" type="button"
                                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                                        <img alt="login" class="w-[32px] h-[32px]"
                                            src="data/images/login_icons/google_icon.png" />
                                    </button>
                                </a>
                            @endif

                            @if ($urlRediris)
                                <a aria-label="enlace" class="no-effect-hover" href="{{ $urlRediris }}">
                                    <button title="rediris login" type="button"
                                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                                        <img alt="login" class="w-[32px] h-[32px]"
                                            src="data/images/login_icons/rediris.png" />
                                    </button>
                                </a>
                            @endif

                            @if (env('URL_LOGIN_CERT'))
                                <a aria-label="enlace" class="no-effect-hover"
                                    href='{{ env('URL_LOGIN_CERT') }}?origin="portal_web"'>
                                    <button title="certificate login" type="button"
                                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                                        <img alt="login" class="w-[32px] h-[32px]"
                                            src="data/images/login_icons/certificate_icon.svg" />
                                    </button>
                                </a>
                            @endif

                        </div>
                    </form>

                    @if ($cert_login != '')
                        <div class="text-center p-4"><a aria-label="enlace" href="/certificate-access">Acceso mediante
                                Certificado Digital</a></div>
                    @endif

                </div>
            </div>

        </div>

    </section>

    <section class="md:hidden p-[20px]">
        @if (app('general_options')['poa_logo_1'])
            <img alt="login" class="mx-auto block max-w-[146px] h-[51px] mb-[15px]"
                src="{{ env('BACKEND_URL') . '/' . app('general_options')['poa_logo_1'] }}" />
        @endif

        <div class="text-[28px] font-bold text-center mb-[15px]">Inicia sesión</div>

        @if ($general_options['registration_active'])
            <p class="text-center mb-[20px]">¿No tienes cuenta? <a aria-label="enlace" class="text-color_1"
                    href="/register">Regístrate</a></p>
        @endif

        @if (session('account_created'))
            <div class="bg-[#E7ECF3] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
                <p>Su cuenta ha sido creada correctamente. Por favor, verifíquela con el email de confirmación
                    que acaba de recibir. Si no lo encuentra, revise la carpeta de spam. </p>
                <p>¿No has recibido el email?
                    <a aria-label="enlace" href="javascript:void(0)" data-email-account="{{ session('email') }}"
                        class="text-color_1 resend-email-confirmation">Reenviar
                        email de
                        confirmación</a>
                </p>
            </div>
        @endif

        @if (session('verify_link_expired'))
            <div class="bg-[#ff605814] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
                <p>El link de confirmación ha expirado</p>
                <p>
                    <a aria-label="enlace" href="javascript:void(0)" data-email-account="{{ session('email') }}"
                        class="text-color_1 resend-email-confirmation">Reenviar email de
                        confirmación</a>
                </p>
            </div>
        @endif

        @if (session('email_verified'))
            <div class="bg-[#E7ECF3] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
                <p>Has verificado correctamente tu cuenta. Ahora puedes iniciar sesión.</p>
            </div>
        @endif

        @if (session('user_not_found'))
            <div class="bg-[#ff605814] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
                <p>No existe ninguna cuenta con esas credenciales</p>
            </div>
        @endif

        @if (session('user_not_verified'))
            <div class="bg-[#ff605814] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
                <p>Su cuenta no está verificada</p>
                <p>¿No has recibido el email?
                    <a aria-label="enlace" href="javascript:void(0)" data-email-account="{{ session('email') }}"
                        class="text-color_1 resend-email-confirmation">Reenviar
                        email de
                        confirmación</a>
                </p>
            </div>
        @endif

        <div class="mb-[25px]">
            <form id="loginFormMobile" action="/login/authenticate" method="POST">
                @csrf
                <div class="flex flex-col mb-[20px]">
                    <label class="px-3 mb-[8px]">Correo</label>
                    <input aria-label="email"
                        class="border-[1.5px] border-solid border-color_1 rounded-full h-[60px] p-3 focus:border-color_1 "
                        type="text" name="email" />
                </div>

                <div class="flex flex-col mb-[8px]">
                    <label class="px-3 mb-[8px]">Contraseña</label>
                    <input aria-label="contraseña"
                        class="border-[1.5px] border-solid border-color_1 rounded-full h-[60px] p-3" name="password"
                        type="password" />
                </div>

                <div class="block px-3 mb-[20px]">
                    <a aria-label="enlace" href="{{ route('recover-password') }}"
                        class="text-color_1 text-[16px]">¿Olvidaste
                        la
                        contraseña?</a>
                </div>

                <button title="iniciar sesión" class="btn bg-color_1 text-white hover:bg-color_2 w-full h-[60px]"
                    type="submit">Iniciar
                    sesión
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
                            <img alt="login" src="{{ asset('data/images/logo_min_boton_login.png') }}"
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
                        <img alt="login" class="w-[45px] h-[45px]" src="data/images/login_icons/facebook.png" />
                    </button>
                </a>
            @endif

            @if ($parameters_login_systems['twitter_login_active'])
                <a aria-label="enlace" class="no-effect-hover" href="/auth/twitter">
                    <button title="twitter login" type="button"
                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                        <img alt="login" class="w-[32px] h-[32px]" src="data/images/login_icons/x_icon.png" />
                    </button>
                </a>
            @endif

            @if ($parameters_login_systems['linkedin_login_active'])
                <a aria-label="enlace" class="no-effect-hover" href="/auth/linkedin-openid">
                    <button title="linkedin login" type="button"
                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                        <img alt="login" class="w-[32px] h-[32px]" src="data/images/login_icons/linkedin_icon.png" />
                    </button>
                </a>
            @endif

            @if ($parameters_login_systems['google_login_active'])
                <a aria-label="enlace" class="no-effect-hover" href="/auth/google">
                    <button title="google login" type="button"
                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                        <img alt="login" class="w-[32px] h-[32px]" src="data/images/login_icons/google_icon.png" />
                    </button>
                </a>
            @endif

            @if ($urlRediris)
                <a aria-label="enlace" class="no-effect-hover" href="{{ $urlRediris }}">
                    <button title="rediris login" type="button"
                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                        <img alt="login" class="w-[32px] h-[32px]" src="data/images/login_icons/rediris.png" />
                    </button>
                </a>
            @endif

            @if (env('URL_LOGIN_CERT'))
                <a aria-label="enlace" class="no-effect-hover" href='{{ env('URL_LOGIN_CERT') }}?origin="portal_web"'>
                    <button title="certificado login" type="button"
                        class="border hover:border-color_1 flex items-center justify-center rounded-full w-[64px] h-[64px]">
                        <img alt="login" class="w-[32px] h-[32px]"
                            src="data/images/login_icons/certificate_icon.svg" />
                    </button>
                </a>
            @endif
        </div>

    </section>
@endsection

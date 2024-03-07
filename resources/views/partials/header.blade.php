{{-- HEADER USUARIO NO LOGUEADO --}}
{{-- <nav class="h-[109px] px-[30px] py-[18px] bg-white items-center justify-between hidden lg:flex border-b">

    <div class="flex items-center gap-[25px]">
        <a href="/" class="">
            <img src="{{ asset('images/logo.png') }}" class="mr-3 h-[75px]" alt="Logo header">
        </a>
        <div
            class="bg-white items-center h-3/4 justify-between p-1 border border-[--secondary-color] rounded-xl w-2/4 my-auto ml-[25px]  lg:flex hidden min-w-[383px]">

            <input
                class=" w-full px-3 border-none bg-transparent text-black py-1 rounded-none ring-0 focus:ring-0 focus:outline-none focus:ring-opacity-0"
                type="text" placeholder="¿Qué quieres aprender hoy?">

            <div
                class="bg-[--primary-color] p-2 cursor-pointer mx-1 rounded-[10px] input-search hover:bg-[#2B4C7E33] transition duration-300">
                {{ e_heroicon('magnifying-glass', 'outline', 'white') }}
            </div>
        </div>
    </div>


    <div class="flex gap-[20px] items-center">
        <div class="flex items-center p-[10px] gap-[10px]">
            <a href="#" class="block p-[10px]" >Inicio</a>

            <a href="#" class="block p-[10px] ">Buscador</a>

        </div>

        <div class="flex gap-[10px]">
            <a href="#"
                class="w-[128px] m-auto border border-[--primary-color] justify-center rounded-[6px] bg-white text-[--primary-color] px-[10px] py-[10px] text-center hover:bg-[--primary-color] hover:text-white transition duration-300">Iniciar
                sesión</a>

            <a href="#"
                class=" w-[128px] m-auto border rounded-[6px] bg-[--primary-color] text-center justify-center text-white px-[10px] py-[10px] button-register">Registrarme</a>
        </div>
    </div>


</nav> --}}

{{-- HEADER USUARIO LOGUEADO --}}
<nav class="h-[109px] px-[30px] py-[18px] bg-white items-center justify-between hidden lg:flex border-b">

    <div class="flex items-center gap-[25px]">
        <a href="/" class="">
            <img src="{{env('BACKEND_URL') . "/" . $general_options['poa_logo']}}" class="mr-3 h-[75px]" alt="Logo header">
        </a>
        <div
            class="bg-white items-center h-3/4 justify-between p-1 border border-[--secondary-color] rounded-xl w-2/4 my-auto ml-[25px]  lg:flex hidden min-w-[383px]">

            <input
                class=" w-full px-3 border-none bg-transparent text-black py-1 rounded-none ring-0 focus:ring-0 focus:outline-none focus:ring-opacity-0"
                type="text" placeholder="¿Qué quieres aprender hoy?">

            <div
                class="bg-[--primary-color] p-2 cursor-pointer mx-1 rounded-[10px] input-search hover:bg-[#2B4C7E33] transition duration-300">
                {{ e_heroicon('magnifying-glass', 'outline', 'white') }}
            </div>
        </div>
    </div>


    <div class="flex gap-[20px] items-center">
        <div class="flex items-center p-[10px] gap-[10px]">
            <a href="#" class="block p-[10px]">Inicio</a>

            <a href="#" class="block p-[10px] ">Buscador</a>

            <div class="relative inline-block text-left">
                <div>
                    <button type="button" class=" flex items-center z-1000 py-[7px] cursor-pointer notify-icon"
                        id="menu-notify-button" aria-expanded="true" aria-haspopup="true">
                        {{ e_heroicon('bell', 'outline', 'black') }}
                    </button>
                </div>

                <div class="absolute hidden p-3 z-10 mt-[35px] h-[469px]  right-[0px] origin-top-left px-[24px] rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden"
                    role="menu-notify" aria-orientation="vertical" aria-labelledby="menu-notify-button" tabindex="-1">
                    <div class="h-[400px] overflow-x-hidden overflow-y-auto pr-[10px] pt-4">
                        <h1 class="text-[--primary-color] text-[20px] font-bold">Notificaciones</h1>

                        @for ($i = 1; $i <= 5; $i++)
                            <div class="py-5 flex flex-col gap-[8px]" role="none">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-[8px]">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="4" height="4"
                                            viewBox="0 0 4 4" fill="none">
                                            <circle cx="2" cy="2" r="2" fill="#FF0000" />
                                        </svg>
                                        <span class="font-bold leading-6">Lorem ipsum dolor sit</span>
                                    </span>

                                    <span class="text-[10px] text-[--arrow-color] justify-end"> Hace 45 min</span>
                                </div>
                                <div class="ml-[12px] w-[270px] h-[45px] text-[14px] font-normal">
                                    Lorem ipsum dolor sit amet consectetur. Amet bibendum mattis ac elementum.
                                </div>
                            </div>
                            @if ($i < 5)
                                <hr>
                            @endif
                        @endfor
                    </div>

                    <!-- Footer -->
                    <div class="py-3 text-center" role="none">
                        <a href="#" class="text-primary w-full px-4 py-2 text-sm font-semibold text-center"
                            role="menuitem-notify" tabindex="-1" id="menu-notify-item-2">
                            Configuración de notificaciones
                        </a>
                    </div>
                </div>

            </div>


        </div>

        <div class="flex gap-[10px]">
            <img src="{{ asset('images/user.png') }}" class="w-[36px] shrink-0 rounded-full hidden xl:block"
                alt="Logo header">

            <div class="relative inline-block text-left">
                <div>
                    <button type="button"
                        class="justify-center flex items-center border-l-2 border-solid border-gray-300 px-[10px] py-[7px] cursor-pointer"
                        id="menu-button" aria-expanded="true" aria-haspopup="true">
                        Mi cuenta
                    </button>
                </div>

                <div class="absolute hidden p-3 z-10 mt-[35px] w-52 right-[-30px] origin-top-left divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                    role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                    <div class="py-1 flex flex-col gap-[24px]" role="none">
                        <a href="#" class="text-gray-700 block px-4 py-2 text-sm" role="menuitem" tabindex="-1"
                            id="menu-item-0">Perfil</a>
                        <a href="#" class="text-gray-700 block px-4 py-2 text-sm" role="menuitem" tabindex="-1"
                            id="menu-item-1">Administrar Portal</a>
                    </div>

                    <div class="py-1 flex flex-col pt-[18px]" role="none">
                        <a href="#" class="text-gray-700 px-4 py-2 text-sm flex gap-[8px] items-center"
                            role="menuitem" tabindex="-1" id="menu-item-3">
                            <span
                                class="rounded-full bg-gray-100 p-[10px] close-sesion">{{ e_heroicon('lock-closed', 'outline', '#2B4C7E') }}</span>
                            <span></span>Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>



</nav>

<nav class="block lg:hidden px-[30px] py-[17px] bg-white justify-between mobile-navbar border-b">
    <div class="flex justify-between items-center">
        <button data-collapse-toggle="mobile-menu" type="button" id="mobile-menu"
            class="inline-flex items-center p-1 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg lg:hidden focus:outline-none">
            <span class="sr-only">Open main menu</span>
            <span id="menu-icon1" class="icon-bars">{{ e_heroicon('bars-3', 'outline', 'grey') }}</span>
            <span id="menu-icon2" class="icon-bars"
                style="display: none">{{ e_heroicon('x-mark', 'outline', 'grey') }}</span>
        </button>

        <div class="flex justify-around items-center">
            <a href="/" class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" class="mr-3 h-[75px] sm:h-[75px]" alt="Logo header">
            </a>
            <div
                class="bg-white items-center h-3/4 justify-between p-1 border border-[--secondary-color] rounded-xl w-2/4 my-auto ml-[25px]  lg:flex hidden">

                <input
                    class=" w-full px-3 border-none bg-transparent text-black py-1 rounded-none ring-0 focus:ring-0 focus:outline-none focus:ring-opacity-0"
                    type="text" placeholder="¿Qué quieres aprender hoy?">

                <div
                    class="bg-[--primary-color] p-2 cursor-pointer mx-1 rounded-[10px] input-search hover:bg-[#2B4C7E33] transition duration-300">
                    {{ e_heroicon('magnifying-glass', 'outline', 'white') }}
                </div>

            </div>

        </div>
        <div class="p-2 cursor-pointer mx-1 transition input-search-mobile ">
            {{ e_heroicon('magnifying-glass', 'outline', 'grey') }}
        </div>
    </div>
</nav>

<!-- Menú desplegable para dispositivos móviles NO LOGUEADO-->

{{-- <div class="hidden lg:hidden bg-white border-y border-grey absolute w-full rounded-md" id="menu">
    <div class="flex flex-col gap-[24px] p-[24px]">
        <a href="#" class=" text-black block rounded-md px-3 py-2 text-base font-medium" aria-current="page">
            Inicio
        </a>
        <a href="#"
            class="text-gray-300 hover:bg-gray-700 hover:text-black block rounded-md px-3 py-2 text-base font-medium">
            Buscador
        </a>
        <hr>

        <div class="flex flex-col gap-[10px]">
            <a href="#"
                class="w-full m-auto border border-[--primary-color] justify-center rounded-[6px] bg-white text-[--primary-color] px-[41px] py-[10px] text-center hover:bg-[--primary-color] hover:text-white transition duration-300">Iniciar
                sesión</a>

            <a href="#"
                class="w-full m-auto border rounded-[6px] bg-[--primary-color] text-center justify-center text-white px-[41px] py-[10px] button-register">Registrarme</a>
        </div>
    </div>
</div> --}}

<!-- Menú desplegable para dispositivos móviles LOGUEADO-->

<div class="hidden lg:hidden bg-white border-y border-grey absolute w-full rounded-md mobile-logueado" id="menu">
    <div class="flex flex-col gap-[24px] p-[24px] menu-general">
        <div class="flex items-center gap-[30px] p-3">
            <img src="{{ asset('images/user.png') }}" class="w-[36px] shrink-0 rounded-full" alt="Logo desplegable">
            <p class="leading-[150%]">María Paula Fernández López</p>
        </div>
        <hr>
        <a href="#" class=" text-black block rounded-md p-3 text-base font-medium" aria-current="page">
            Inicio
        </a>
        <a href="#" class="text-black block rounded-md p-3 text-base font-medium">
            Buscador
        </a>
        <div class="text-black rounded-md p-3 text-base font-medium flex justify-between cuenta-icon cursor-pointer">
            <p>Tu cuenta</p>
            {{ e_heroicon('chevron-right', 'outline', 'black') }}

        </div>
    </div>

    <div class="flex flex-col gap-[24px] p-[24px] menu-cuenta hidden">
        <div class="flex items-center gap-[8px] p-[7px] menu-back-icon h-[60px]">
            {{ e_heroicon('chevron-left', 'outline', 'black') }}
            <p class="leading-[150%]">Menú principal</p>
        </div>
        <hr>
        <a href="#" class=" text-black block rounded-md p-[7px] text-base font-medium" aria-current="page">
            Perfil
        </a>
        <a href="#" class="text-black block rounded-md p-[7px] text-base font-medium">
            Administrar Portal
        </a>
        <hr>
        <a href="#" class="text-gray-700 flex gap-[8px] items-center" role="menuitem" tabindex="-1"
            id="menu-item-3">
            <span
                class="rounded-full bg-gray-100 p-[10px] close-sesion">{{ e_heroicon('lock-closed', 'outline', '#2B4C7E') }}</span>
            <span>Cerrar sesión</span></a>
    </div>
</div>

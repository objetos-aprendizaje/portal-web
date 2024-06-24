@if (Auth::check())
    {{-- HEADER USUARIO LOGUEADO --}}
    <header
        class="h-[110px] px-[30px] py-[18px] bg-white items-center justify-between hidden lg:flex border-b top-0 fixed w-full z-50">
        <div class="flex items-center gap-[25px]">
            <a class="no-effect-hover" href="/" class="no-effect-hover">
                <img src="{{ $general_options['poa_logo'] ? env('BACKEND_URL') . '/' . $general_options['poa_logo'] : '/data/images/default_images/logo-default.png' }}" class="mr-3 h-[75px]"
                    alt="Logo header">
            </a>
            <div
                class="bg-white items-center h-3/4 justify-between p-1 border rounded-xl w-2/4 my-auto ml-[25px]  lg:flex hidden min-w-[383px]">

                <input
                    class=" w-full px-3 border-none bg-transparent text-black py-1 rounded-none ring-0 focus:ring-0 focus:outline-none focus:ring-opacity-0"
                    type="text" placeholder="¿Qué quieres aprender hoy?">

                <div
                    class="bg-color_1 p-2 cursor-pointer mx-1 rounded-[10px] input-search transition duration-300 hover:bg-color_2">
                    {{ e_heroicon('magnifying-glass', 'outline', 'white') }}
                </div>
            </div>
        </div>


        <div class="flex gap-[20px] items-center">
            <div class="flex items-center p-[10px] gap-[10px]">
                <a href="{{ route('index') }}" class="block p-[10px]">Inicio</a>

                <a href="{{ route('searcher') }}" class="p-[10px]">Buscador</a>

                @foreach ($header_pages as $page)
                    <a href="" class="block p-[10px] ">{{ $page['name'] }}</a>
                @endforeach

                <div class="relative inline-block text-left">
                    <div>
                        <button type="button" class="flex items-center z-1000 py-[7px] cursor-pointer notify-icon"
                            id="bell-btn" aria-expanded="true" aria-haspopup="true">
                            {{ e_heroicon('bell', 'outline', 'black') }}

                            <div id="notification-dot"
                                class="notification-dot {{ $unread_general_notifications ? 'block' : 'hidden' }}"></div>
                        </button>

                        @include('partials.notifications')
                    </div>

                </div>
            </div>

            <div id="my-account-btn">
                <div class="flex gap-[10px] cursor-pointer">
                    <img src="{{ Auth::user()->photo_path ? env('BACKEND_URL') . '/' . Auth::user()->photo_path : asset('images/no-user.svg') }}"
                        class="w-[36px] shrink-0 rounded-full hidden xl:block" alt="Logo header">

                    <div class="relative inline-block text-left">
                        <div>
                            <button type="button"
                                class="justify-center flex items-center border-l-2 border-solid border-gray-300 px-[10px] py-[7px] cursor-pointer"
                                id="menu-button" aria-expanded="true" aria-haspopup="true">
                                Mi cuenta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div id="my-account-menu" class="hidden">
        <div class="w-[282px] p-[24px] z-50 fixed right-[6px] origin-top-left divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
            role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
            <div class="py-1 flex flex-col gap-[24px]" role="none">
                <a href="/profile/update_account" class="hover:bg-color_hover_2 block p-[10px] text-sm no-effect-hover" role="menuitem" tabindex="-1"
                    id="menu-item-0">Perfil</a>
                <a href="{{env('BACKEND_URL')}}" class=" hover:bg-color_hover_2 block p-[10px] text-sm no-effect-hover" role="menuitem" tabindex="-1"
                    id="menu-item-1">Administrar
                    Portal</a>
                <hr>

                <a href="{{ env('APP_URL') }}/logout" class="hover:bg-color_hover_2 text-sm flex gap-[8px] items-center no-effect-hover" role="menuitem"
                    tabindex="-1" id="menu-item-3">
                    <span
                        class="rounded-full  bg-gray-100 close-sesion p-[10px]">{{ e_heroicon('lock-closed', 'outline', '#2B4C7E', 20, 20) }}</span>
                    Cerrar sesión</a>
            </div>
        </div>
    </div>
@else
    {{-- HEADER USUARIO NO LOGUEADO --}}
    <header
        class="h-[119px] px-[30px] py-[18px] bg-white items-center justify-between hidden lg:flex border-b top-0 fixed w-full z-50">

        <div class="flex items-center gap-[25px]">
            <a href="/" class="no-effect-hover">
                <img src="{{ $general_options['poa_logo'] ? env('BACKEND_URL') . '/' . $general_options['poa_logo'] : '/data/images/default_images/logo-default.png' }}" class="mr-3 h-[75px]"
                    alt="Logo header">
            </a>
            <div
                class="bg-white items-center h-3/4 justify-between p-1 border rounded-xl w-2/4 my-auto ml-[25px]  lg:flex hidden min-w-[383px]">

                <input
                    class=" w-full px-3 border-none bg-transparent text-black py-1 rounded-none ring-0 focus:ring-0 focus:outline-none focus:ring-opacity-0"
                    type="text" placeholder="¿Qué quieres aprender hoy?">

                <div
                    class="bg-color_1 p-2 cursor-pointer mx-1 rounded-[10px] input-search transition duration-30 hover:bg-color_2">
                    {{ e_heroicon('magnifying-glass', 'outline', 'white') }}
                </div>
            </div>
        </div>


        <div class="flex gap-[20px] items-center">
            <div class="flex items-center p-[10px] gap-[10px]">
                <a href="{{ route('index') }}" class="block p-[10px]">Inicio</a>

                <a href="{{ route('searcher') }}" class="block p-[10px] ">Buscador</a>

                @foreach ($header_pages as $page)
                    <a href="" class="block p-[10px] ">{{ $page['name'] }}</a>
                @endforeach

            </div>

            <div class="flex gap-[10px]">
                <a href="/login"
                    class="w-[128px] m-auto border border-color_1 justify-center rounded-[6px] bg-white text-color_1 px-[10px] py-[10px] text-center hover:bg-color_1 hover:text-white transition duration-300">Iniciar
                    sesión</a>

                <a href="#"
                    class=" w-[128px] m-auto border rounded-[6px] bg-color_1 text-center justify-center text-white px-[10px] py-[10px] button-register hover:bg-color_2">Registrarme</a>
            </div>
        </div>
    </header>
@endif


<!-- Menú móvil-->
<header
    class="block lg:hidden px-[30px] py-[17px] bg-white justify-between mobile-navbar border-b fixed w-full z-50 top-0">
    <div class="flex justify-between items-center">
        <button data-collapse-toggle="mobile-menu" type="button" id="mobile-menu-btn"
            class="inline-flex items-center p-1 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg lg:hidden focus:outline-none">
            <span class="sr-only">Open main menu</span>
            <span id="menu-icon1" class="icon-bars">{{ e_heroicon('bars-3', 'outline', 'grey') }}</span>
            <span id="menu-icon2" class="icon-bars"
                style="display: none">{{ e_heroicon('x-mark', 'outline', 'grey') }}</span>
        </button>

        <div class="flex justify-around items-center">
            <a href="/" class="flex items-center no-effect-hover">
                <img src="{{ $general_options['poa_logo'] ? env('BACKEND_URL') . '/' . $general_options['poa_logo'] : '/data/images/default_images/logo-default.png' }}"
                    class="mr-3 h-[75px] sm:h-[75px]" alt="Logo header">
            </a>
            <div
                class="bg-white items-center h-3/4 justify-between p-1 border rounded-xl w-2/4 my-auto ml-[25px]  lg:flex hidden">

                <input
                    class=" w-full px-3 border-none bg-transparent text-black py-1 rounded-none ring-0 focus:ring-0 focus:outline-none focus:ring-opacity-0"
                    type="text" placeholder="¿Qué quieres aprender hoy?">

                <div
                    class="bg-color_1 p-2 cursor-pointer mx-1 rounded-[10px] input-search transition duration-300 hover:bg-color_2">
                    {{ e_heroicon('magnifying-glass', 'outline', 'white') }}
                </div>

            </div>

        </div>
        <div class="p-2 cursor-pointer mx-1 transition input-search-mobile ">
            {{ e_heroicon('magnifying-glass', 'outline', 'grey') }}
        </div>
    </div>
</header>

<!-- Menú desplegable para dispositivos móviles-->
<div id="overlay-layer-menu" class="overlay-layer-menu hidden"></div>
<header class="menu-mobile hidden" id="mobile-menu">
    <div class="menu-mobile-wrapper">

        <div id="menu-mobile-options" class="menu-mobile-container">

            @if (Auth::check())
                <a href="#">
                    <div class="option">

                        <div class="flex gap-[20px] items-center">
                            <img class="w-[32px] h-[32px] rounded-full"
                                src="{{ Auth::user()->photo_path ? env('BACKEND_URL') . '/' . Auth::user()->photo_path : asset('images/no-user.svg') }}"
                                alt="Foto de perfil">

                            <div>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                        </div>
                    </div>
                </a>
            @endif

            <a href="{{ route('index') }}" class="option" aria-current="page">
                Inicio
            </a>

            <a href="{{ route('searcher') }}" class="option">
                Buscador
            </a>

            @foreach ($header_pages as $page)
                <a href="" class="option">{{ $page['name'] }}</a>
            @endforeach

            @if (Auth::check())
                <a href="javascript:void(0)" id="your-account-option-btn" class="option">
                    <div class="flex justify-between items-center">
                        <div>Tu cuenta</div>
                        <div>
                            {{ e_heroicon('chevron-right', 'outline', null, 16, 16) }}
                        </div>
                    </div>
                </a>
            @else
                <hr>
                <div class="flex flex-col gap-[10px]">
                    <a href="/login" class="btn btn-secondary">Iniciar
                        sesión</a>

                    <a href="#" class="btn btn-primary">Registrarme</a>
                </div>
            @endif
        </div>

        <div id="menu-mobile-account" class="menu-mobile-container not-show">
            <a href="javascript:void(0)" id="main-menu-btn" class="option bg-color_hover_2">
                <div class="flex items-center gap-[8px] ">
                    <div>
                        {{ e_heroicon('chevron-left', 'outline', null, 16, 16) }}
                    </div>
                    <div>Menú principal</div>
                </div>
            </a>
            <hr>

            <a href="/profile/update_account" class="option">
                Perfil
            </a>

            <a href="{{env('BACKEND_URL')}}" class="option">
                Administrar Portal
            </a>

            <hr>

            <a href="javascript:void(0)" id="your-account-option-btn" class="option">
                <div class="flex items-center gap-[8px] ">
                    <div class="icon-close-session">
                        {{ e_heroicon('lock-closed', 'outline', null, 20, 20) }}
                    </div>

                    <div>Cerrar sesión</div>
                </div>
            </a>
        </div>

    </div>
</header>

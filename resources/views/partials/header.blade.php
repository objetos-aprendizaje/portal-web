<!-- Header desktop -->
<header
    class="px-[30px] h-[110px] bg-white items-center justify-between hidden lgx:flex border-b top-0 fixed w-full z-50">
    <div class="flex items-center gap-[25px]">
        @if (!$general_options['poa_logo_1'] && !$general_options['poa_logo_2'] && !$general_options['poa_logo_3'])
            <a aria-label="enlace" class="no-effect-hover" href="/" class="no-effect-hover">
                <img src="/data/images/default_images/logo-default.png" class="mr-3 h-[75px]" alt="Logo header">
            </a>
        @else
            @if ($general_options['poa_logo_1'])
                <a aria-label="enlace" class="no-effect-hover" href="/" class="no-effect-hover">
                    <img src="{{ env('BACKEND_URL') . '/' . $general_options['poa_logo_1'] }}"
                        class="mr-3 w-[215px] h-[75px]" alt="Logo header">
                </a>
            @endif
            @if ($general_options['poa_logo_2'])
                <a aria-label="enlace" class="no-effect-hover" href="/" class="no-effect-hover">
                    <img src="{{ env('BACKEND_URL') . '/' . $general_options['poa_logo_2'] }}"
                        class="mr-3 w-[215px] h-[75px]" alt="Logo header">
                </a>
            @endif
            @if ($general_options['poa_logo_3'])
                <a aria-label="enlace" class="no-effect-hover" href="/" class="no-effect-hover">
                    <img src="{{ env('BACKEND_URL') . '/' . $general_options['poa_logo_3'] }}"
                        class="mr-3 w-[215px] h-[75px]" alt="Logo header">
                </a>
            @endif
        @endif
        <div class="w-[280px] relative searcher">
            <label for="searcher_button" class="hidden">Introduce texto para buscar</label>
            <input
                class="border border-[#D9D9D9] rounded-[10px] w-full pr-[40px] py-[10px] pl-[10px] text-black focus:outline-none focus:ring-1 focus:ring-color_1 focus:border-color_1 focus:shadow-none placeholder:text-gray-400 h-[40px]"
                type="text" placeholder="¿Qué quieres aprender hoy?" id="searcher_button">
            <button title="buscar" aria-label="Buscar" type="button"
                class="bg-color_1 absolute w-[32px] h-[32px] flex items-center justify-center top-1/2 transform -translate-y-1/2 rounded-[8px] right-[4px] ">
                {{ e_heroicon('magnifying-glass', 'outline', 'white', 20, 20, null, '3') }}
            </button>
        </div>
    </div>
    <div class="flex gap-[30px] items-center h-full">
        <div class="flex items-center gap-[10px] h-full">
            <a aria-label="enlace" href="{{ route('index') }}" class="block p-[10px]">Inicio</a>
            <a aria-label="enlace" href="{{ route('searcher') }}" class="p-[10px]">Buscador</a>
            @foreach ($header_pages as $page)
                <div class="flex items-center h-full">
                    <a aria-label="enlace" href="/page/{{ $page->slug }}"
                        class="flex items-center gap-[4px] px-[10px] rounded-[8px] h-full {{ $page->headerPagesChildren->count() ? 'has-submenu-header' : '' }}">{{ $page['name'] }}
                        @if ($page->headerPagesChildren->count())
                            {{ e_heroicon('chevron-down', 'outline', 'black', 20, 20) }}
                        @endif
                    </a>
                    @if ($page->headerPagesChildren->count())
                        <div
                            class="submenu-header hidden absolute shadow top-full bg-white p-[12px] rounded-[8px] w-[260px]">
                            @foreach ($page->headerPagesChildren as $pageChildren)
                                <a aria-label="enlace" href="/page/{{ $pageChildren->slug }}"
                                    class="flex gap-2 p-[10px] hover:bg-color_hover_2 rounded-[8px] mb-[12px]">{{ $pageChildren['name'] }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
            @if (Auth::check() && Auth::user()->general_notifications_allowed)
                <div class="relative inline-block text-left">
                    <div>
                        <button type="button" class="flex items-center z-1000 py-[7px] cursor-pointer notify-icon"
                            id="notifications-desktop-btn" aria-expanded="true" aria-haspopup="true">
                            {{ e_heroicon('bell', 'outline', 'black') }}
                            <div
                                class="notification-dot top-1.5 right-0 {{ $unread_general_notifications ? 'block' : 'hidden' }}">
                            </div>
                        </button>
                        <div id="notification-box"
                            class="hidden  top-[calc(100%+35px)] right-0 w-[600px] absolute max-h-[300px]">
                            <div
                                class="notification-box bg-white rounded-lg overflow-y-scroll py-[24px] px-[24px] max-h-[300px] shadow-lg">
                                <div class="font-bold text-[22px] text-color_1 leading-[22px]">Notificaciones</div>
                                <hr class="mt-[18px] border-gray-300" />
                                @include('partials.notifications')
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if (Auth::check())
            <div id="my-account-btn">
                <div class="flex gap-[10px] cursor-pointer items-center">
                    <img src="{{ Auth::user()->photo_path ? env('BACKEND_URL') . '/' . Auth::user()->photo_path : asset('images/no-user.svg') }}"
                        class="w-[36px] h-[36px] shrink-0 rounded-full hidden xl:block" alt="Logo header">

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
        @else
            <div class="flex gap-[10px]">
                <a aria-label="enlace" href="/login"
                    class="w-[128px] m-auto border border-color_1 justify-center rounded-[6px] bg-white text-color_1 px-[10px] py-[10px] text-center hover:bg-color_1 hover:text-white transition duration-300">Iniciar
                    sesión</a>

                @if ($general_options['registration_active'])
                    <a aria-label="enlace" href="/register"
                        class=" w-[128px] m-auto border rounded-[6px] bg-color_1 text-center justify-center text-white px-[10px] py-[10px] button-register hover:bg-color_2">Registrarme</a>
                @endif
            </div>
        @endif
    </div>
</header>

<!-- Menú desplegable de mi cuenta en desktop-->
<section id="my-account-menu" class="hidden">
    <div class="w-[282px] p-[24px] z-50 fixed right-[6px] origin-top-left divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
        <div class="py-1 flex flex-col gap-[24px]" role="none">
            <a aria-label="enlace" href="/profile/update_account"
                class="hover:bg-color_hover_2 block p-[10px] text-sm no-effect-hover" role="menuitem" tabindex="-1"
                id="menu-item-0">Perfil</a>

            @if (Auth::check() && Auth::user()->hasAnyRole(['ADMINISTRATOR', 'MANAGEMENT', 'TEACHER']))
                <a aria-label="enlace" href="{{ env('BACKEND_URL') }}"
                    class=" hover:bg-color_hover_2 p-[10px] text-sm no-effect-hover" role="menuitem" tabindex="-1"
                    id="menu-item-1" target="_blank">Portal de administración </a>
            @endif
            <hr>

            <a aria-label="enlace" href="{{ env('APP_URL') }}/logout"
                class="hover:bg-color_hover_2 text-sm flex gap-[8px] items-center no-effect-hover" role="menuitem"
                tabindex="-1" id="menu-item-3">
                <span
                    class="rounded-full  bg-gray-100 close-sesion p-[10px]">{{ e_heroicon('lock-closed', 'outline', $general_options['color_1'], 20, 20) }}</span>
                Cerrar sesión</a>
        </div>
    </div>
</section>

<!-- Menú móvil-->
<header class="mobile-navbar">
    <div>
        <button data-collapse-toggle="mobile-menu" type="button" id="mobile-menu-btn"
            class="inline-flex items-center p-1 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg lgx:hidden focus:outline-none">
            <span class="sr-only">Open main menu</span>
            <span id="menu-icon1" class="icon-bars">{{ e_heroicon('bars-3', 'outline', 'grey') }}</span>
            <span id="menu-icon2" class="icon-bars"
                style="display: none">{{ e_heroicon('x-mark', 'outline', 'grey') }}</span>
        </button>
    </div>

    <div class="flex justify-around items-center">
        <a aria-label="enlace" href="/" class="flex items-center no-effect-hover">
            <img src="{{ $general_options['poa_logo_1'] ? env('BACKEND_URL') . '/' . $general_options['poa_logo_1'] : '/data/images/default_images/logo-default.png' }}"
                class="h-[50px]" alt="Logo header">
        </a>
        <div
            class="bg-white items-center h-3/4 justify-between p-1 border rounded-xl w-2/4 my-auto ml-[25px]  lgx:flex hidden searcher">

            <input
                class=" w-full px-3 border-none bg-transparent text-black py-1 rounded-none ring-0 focus:ring-0 focus:outline-none focus:ring-opacity-0"
                type="text" placeholder="¿Qué quieres aprender hoy?">

            <div
                class="bg-color_1 p-2 cursor-pointer mx-1 rounded-[10px] input-search transition duration-300 hover:bg-color_2">
                {{ e_heroicon('magnifying-glass', 'outline', 'white') }}
            </div>

        </div>
    </div>

    <div class="p-2 cursor-pointer mx-1 transition input-search-mobile flex">
        @if (Auth::check())
            @if (Auth::user()->general_notifications_allowed)
                <div class="relative">
                    <button id="notifications-btn">{{ e_heroicon('bell', 'outline', 'grey') }}</button>
                    <div
                        class="notification-dot top-0 right-0 {{ $unread_general_notifications ? 'block' : 'hidden' }}">
                    </div>
                </div>
            @endif

            <div>
                <button id="user-menu-mobile-btn">{{ e_heroicon('user-circle', 'solid', 'grey') }}</button>
            </div>
        @else
            <a aria-label="enlace"
                href="/login">{{ e_heroicon('arrow-right-end-on-rectangle', 'outline', 'grey') }}</a>
        @endif
    </div>
</header>

<!-- Overlay de fondo al desplegar menús en móviles-->
<div id="overlay-layer-menu" class="overlay-layer-menu hidden"></div>

<!-- Menú desplegable en móvil-->
<section class="menu-mobile-container hidden" id="mobile-menu">
    <ul class="menu-mobile">

        @if (Auth::check())
            <li class="option-label option">
                <div class="flex gap-[20px] items-center">
                    <img class="w-[32px] h-[32px] rounded-full"
                        src="{{ Auth::user()->photo_path ? env('BACKEND_URL') . '/' . Auth::user()->photo_path : asset('images/no-user.svg') }}"
                        alt="Foto de perfil">

                    <div>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>

                </div>
            </li>
        @endif

        <a aria-label="enlace" href="/">
            <li class="option">
                <div class="option-label">Inicio</div>
            </li>
        </a>

        <a aria-label="enlace" href="{{ route('searcher') }}">
            <li class="option-label option">
                <div>Buscador</div>
            </li>
        </a>

        @foreach ($header_pages as $page)
            @if (!$page->headerPagesChildren->count())
                <a aria-label="enlace" href="{{ '/page/' . $page->slug }}">
                    <li class="option">
                        <div class="option-label">{{ $page->name }}</div>
                    </li>
                </a>
            @else
                <li class="option-submenu ">
                    <div class="option-label flex justify-between items-center hover:opacity-75">
                        <div>{{ $page->name }}</div>
                        <div class="icon-closed">
                            {{ e_heroicon('chevron-right', 'outline', null, 16, 16) }}
                        </div>
                        <div class="icon-openned hidden">
                            {{ e_heroicon('chevron-down', 'outline', null, 16, 16) }}
                        </div>
                    </div>

                    <ul class="submenu hidden overflow-y-scroll max-h-[200px]">
                        @foreach ($page->headerPagesChildren as $page)
                            <a aria-label="enlace" href="{{ '/page/' . $page->slug }}">
                                <li class="option">
                                    <div class="option-label">{{ $page->name }}</div>
                                </li>
                            </a>
                        @endforeach

                    </ul>
                </li>
            @endif
        @endforeach

        <hr class="my-[24px]">

        @if (Auth::user())
            <a aria-label="enlace" href="{{ route('logout') }}"
                class="hover:bg-color_hover_2 text-sm flex gap-[8px] items-center no-effect-hover" role="menuitem"
                tabindex="-1" id="menu-item-3">
                <span class="rounded-full  bg-gray-100 close-sesion p-[10px]">
                    {{ e_heroicon('lock-closed', 'outline', $general_options['color_1'], 20, 20) }}
                </span>
                Cerrar sesión</a>
        @else
            <a aria-label="enlace" href="{{ route('login') }}"
                class="w-full mb-[12px] m-auto border border-color_1 justify-center rounded-[6px] bg-white text-color_1 px-[10px] py-[10px] text-center hover:bg-color_1 hover:text-white transition duration-300">Iniciar
                sesión</a>

            @if ($general_options['registration_active'])
                <a aria-label="enlace" href="{{ route('register') }}"
                    class=" w-full m-auto border rounded-[6px] bg-color_1 text-center justify-center text-white px-[10px] py-[10px] button-register hover:bg-color_2">Registrarme</a>
            @endif
        @endif
    </ul>
</section>

<!-- Menú desplegable de notificaciones en móvil-->
<section class="menu-mobile-container hidden" id="notifications-mobile-menu">
    <ul class="menu-mobile">
        <li>
            <div class="option-label flex justify-between items-center hover:opacity-75">
                <div>Notificaciones</div>
            </div>

            <ul class="submenu overflow-y-scroll max-h-[200px]">
                @include('partials.notifications')
            </ul>
        </li>
    </ul>
</section>

<!-- Menú desplegable de mi cuenta en móvil-->
<section class="menu-mobile-container hidden" id="user-mobile-menu">
    <ul class="menu-mobile">
        <a target="_blank" aria-label="enlace" href="{{ env('BACKEND_URL') }}">
            <li class="option-label option">
                <div>Portal de administración</div>
            </li>
        </a>

        <a aria-label="enlace" href="{{ route('my-profile') }}">
            <li class="option-label option">
                <div>Mi perfil</div>
            </li>
        </a>

        <li class="option-submenu">
            <div class="option-label flex justify-between items-center hover:opacity-75">
                <div>Mis cursos</div>
                <div class="icon-closed">
                    {{ e_heroicon('chevron-right', 'outline', null, 16, 16) }}
                </div>
                <div class="icon-openned hidden">
                    {{ e_heroicon('chevron-down', 'outline', null, 16, 16) }}
                </div>
            </div>

            <ul class="submenu hidden">
                <a aria-label="enlace" href="{{ route('my-courses-inscribed') }}">
                    <li>
                        <div class="option-label">Inscritos</div>
                    </li>
                </a>

                <a aria-label="enlace" href="{{ route('my-courses-enrolled') }}">
                    <li>
                        <div class="option-label">Matriculados</div>
                    </li>
                </a>

                <a aria-label="enlace" ref="{{ route('my-courses-historic') }}">
                    <li>
                        <div class="option-label">Histórico</div>
                    </li>
                </a>
            </ul>
        </li>

        <li class="option-submenu">
            <div class="option-label flex justify-between items-center hover:opacity-75">
                <div>Mis programas formativos</div>
                <div class="icon-closed">
                    {{ e_heroicon('chevron-right', 'outline', null, 16, 16) }}
                </div>
                <div class="icon-openned hidden">
                    {{ e_heroicon('chevron-down', 'outline', null, 16, 16) }}
                </div>
            </div>

            <ul class="submenu hidden">
                <a aria-label="enlace" href="{{ route('my-educational-programs-inscribed') }}">
                    <li>
                        <div class="option-label">Inscritos</div>
                    </li>
                </a>

                <a aria-label="enlace" href="{{ route('my-educational-programs-enrolled') }}">
                    <li>
                        <div class="option-label">Matriculados</div>
                    </li>
                </a>

                <a aria-label="enlace" href="{{ route('my-educational-programs-historic') }}">
                    <li>
                        <div class="option-label">Histórico</div>
                    </li>
                </a>
            </ul>
        </li>

        <a aria-label="enlace" href="{{ route('competences-learning-results') }}">
            <li class="option-label option">
                Competencias y resultados de aprendizaje
            </li>
        </a>

        <li class="option-submenu">
            <div class="option-label flex justify-between items-center hover:opacity-75">
                <div>Configuración de notificaciones</div>
                <div class="icon-closed">
                    {{ e_heroicon('chevron-right', 'outline', null, 16, 16) }}
                </div>
                <div class="icon-openned hidden">
                    {{ e_heroicon('chevron-down', 'outline', null, 16, 16) }}
                </div>
            </div>

            <ul class="submenu hidden">
                <a aria-label="enlace" href="{{ route('profile-general-notifications') }}">
                    <li>
                        <div class="option-label">Notificaciones generales</div>
                    </li>
                </a>

                <a aria-label="enlace" href="{{ route('profile-email-notifications') }}">
                    <li>
                        <div class="option-label">Notificaciones por email</div>
                    </li>
                </a>
            </ul>
        </li>

        <a aria-label="enlace" href="{{ route('categories') }}">
            <li class="option-label option">
                Configuración de Categorías
            </li>
        </a>
    </ul>
</section>

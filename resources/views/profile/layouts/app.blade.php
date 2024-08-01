<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @if (isset($page_title))
            {{ $page_title }} - POA
        @else
            POA
        @endif
    </title>

    @vite(['resources/css/app.css', 'resources/scss/app.scss', 'resources/js/app.js', 'resources/css/toastify.css', 'resources/js/header.js', 'resources/js/notifications_handler.js', 'resources/js/modal_handler.js', 'resources/js/profile/menu.js'])

    <style>
        :root {
            --color_hover: #507ab9;
            --color_hover_2: #F5F6F9;
            --color_background_elements: #F4F4F6;
            --color_1: {{ $general_options['color_1'] }};
            --color_2: {{ $general_options['color_2'] }};
            --color_3: {{ $general_options['color_3'] }};
            --color_4: {{ $general_options['color_4'] }};
        }
    </style>

    @if (isset($resources))
        @vite($resources)
    @endif

    @if (isset($infiniteTree) && $infiniteTree)
        @vite(['node_modules/infinite-tree/dist/infinite-tree.css'])
    @endif

    <script>
        window.userUid = @json(Auth::check() ? Auth::user()->uid : null);
        window.backendUrl = @json(env('BACKEND_URL'));
    </script>

    @if (isset($variables_js))
        <script>
            @foreach ($variables_js as $name => $value)
                window['{{ $name }}'] = @json($value);
            @endforeach
        </script>
    @endif
</head>

<body>
    @include('partials.header')

    <div class="bg-[#EEEEEE] p-8 lg:ml-[270px] min-h-[calc(100vh-110px)] mt-[110px]" id="main-content">
        @include('profile.partials.menu')

        @yield('content')
    </div>

    @include('partials.loading')
    @include('partials.notification-info-modal')

</body>

</html>

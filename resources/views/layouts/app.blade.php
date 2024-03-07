<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <style>
        :root {
            --primary-color: {{ $general_options['color_1'] ?? env('COLOR_PRIMARY') }};
            --secondary-color: {{$general_options['color_2'] ?? env('COLOR_SECONDARY') }};
            --title-color: {{ env('COLOR_TITLE') }};
            --footer-title-color: {{ env('COLOR_FOOTER_TITLE') }};
            --arrow-color: {{ env('COLOR_ARROW') }};
            --black-title-color: {{ env('COLOR_BLACK_TITLE') }};

        }
    </style>

    <title>
        @if (isset($page_title))
            {{ $page_title }}
        @else
            POA
        @endif
    </title>

    @vite(['resources/css/app.css', 'resources/scss/app.scss'])

    @if (isset($resources) && is_array($resources))
        @vite($resources)
    @endif

    @if ($general_options['scripts'])
        {!!$general_options['scripts']!!}
    @endif

</head>

<body>
    @include('partials.header')

    <div class="">
        @yield('content')
    </div>

    @include('partials.footer')

    @vite(['resources/js/header.js', 'resources/js/carrousel.js'])

</body>

</html>

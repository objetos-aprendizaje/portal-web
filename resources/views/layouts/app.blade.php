<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @if (isset($page_title))
            {{ $page_title }}
        @else
            POA
        @endif
    </title>

    @vite(['resources/css/app.css', 'resources/scss/app.scss', 'resources/js/app.js', 'resources/js/carrousel.js', 'resources/css/toastify.css', 'resources/js/header.js', 'resources/js/modal_handler.js'])

    @if (Auth::check())
        @vite(['resources/js/notifications_handler.js'])
    @endif

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

        @font-face {
            font-family: 'font-regular';

            src: @if ($fonts['truetype_regular_file_path'])
                url({{ asset($fonts['truetype_regular_file_path']) }}) format('truetype');
            @endif
            @if ($fonts['woff_regular_file_path'])
                url({{ asset($fonts['woff_regular_file_path']) }}) format('woff');
            @endif
            @if ($fonts['woff2_regular_file_path'])
                url({{ asset($fonts['woff2_regular_file_path']) }}) format('woff2');
            @endif
            @if ($fonts['embedded_opentype_regular_file_path'])
                url({{ asset($fonts['embedded_opentype_regular_file_path']) }}) format('embedded-opentype');
            @endif
            @if ($fonts['opentype_regular_input_file'])
                url({{ asset($fonts['opentype_regular_input_file']) }}) format('opentype');
            @endif
            @if ($fonts['svg_regular_file_path'])
                url({{ asset($fonts['svg_regular_file_path']) }}) format('svg');
            @endif
        }

        @font-face {
            font-family: 'font-medium';

            src: @if ($fonts['truetype_medium_file_path'])
                url({{ asset($fonts['truetype_medium_file_path']) }}) format('truetype');
            @endif
            @if ($fonts['woff_medium_file_path'])
                url({{ asset($fonts['woff_medium_file_path']) }}) format('woff');
            @endif
            @if ($fonts['woff2_medium_file_path'])
                url({{ asset($fonts['woff2_medium_file_path']) }}) format('woff2');
            @endif
            @if ($fonts['embedded_opentype_medium_file_path'])
                url({{ asset($fonts['embedded_opentype_medium_file_path']) }}) format('embedded-opentype');
            @endif
            @if ($fonts['opentype_medium_file_path'])
                url({{ asset($fonts['opentype_medium_file_path']) }}) format('opentype');
            @endif
            @if ($fonts['svg_medium_file_path'])
                url({{ asset($fonts['svg_medium_file_path']) }}) format('svg');
            @endif

        }

        @font-face {
            font-family: 'font-bold';

            src: @if ($fonts['truetype_bold_file_path'])
                url({{ asset($fonts['truetype_bold_file_path']) }}) format('truetype');
            @endif
            @if ($fonts['woff_bold_file_path'])
                url({{ asset($fonts['woff_bold_file_path']) }}) format('woff');
            @endif
            @if ($fonts['woff2_bold_file_path'])
                url({{ asset($fonts['woff2_bold_file_path']) }}) format('woff2');
            @endif
            @if ($fonts['embedded_opentype_bold_file_path'])
                url({{ asset($fonts['embedded_opentype_bold_file_path']) }}) format('embedded-opentype');
            @endif
            @if ($fonts['opentype_bold_file_path'])
                url({{ asset($fonts['opentype_bold_file_path']) }}) format('opentype');
            @endif
            @if ($fonts['svg_bold_file_path'])
                url({{ asset($fonts['svg_bold_file_path']) }}) format('svg');
            @endif

        }
    </style>

    @if (isset($resources))
        @vite($resources)
    @endif

    @if (isset($flatpickr) && $flatpickr)
        @vite(['node_modules/flatpickr/dist/flatpickr.css'])
    @endif

    @if (isset($treeselect) && $treeselect)
        @vite(['node_modules/treeselectjs/dist/treeselectjs.css'])
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

    @php
        echo $general_options['scripts'];
    @endphp

</head>

<body>
    @include('partials.header')

    <div class="lg:p-0 mt-[110px] overflow-auto">
        @yield('content')
    </div>

    @include('partials.loading')

    @include('partials.notification-info-modal')

</body>

</html>

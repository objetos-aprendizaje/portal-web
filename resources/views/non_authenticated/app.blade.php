<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --color_hover: #507ab9;
            --color_secondary_text: #585859;
            --color_hover_2: #F5F6F9;
            --color_1: {{ $general_options['color_1'] }};
            --color_2: {{ $general_options['color_2'] }};
        }
    </style>

    <title>
        @if (isset($page_title))
            {{ $page_title }}
        @else
            POA
        @endif
    </title>

    @vite(['resources/css/app.css', 'resources/scss/app.scss', 'resources/css/toastify.css'])

    @if (isset($resources) && is_array($resources))
        @vite($resources)
    @endif

    @if ($errors->any())
        <script>
            window.errors = [];
            @foreach ($errors->all() as $error)
                window.errors.push('{{ $error }}');
            @endforeach
        </script>
    @endif

</head>

<body>
    @yield('content')

    @include('partials.loading')

</body>

</html>

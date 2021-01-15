<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="font-sans antialiased"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ mix('css/main.css') }}">

        <!-- Scripts -->
        @routes
        <script src="{{ mix('js/main.js') }}" defer></script>
    </head>
    <body class="min-h-screen bg-white dark:bg-gray-900">
        <div id="app" class="flex flex-col flex-none w-full" data-page="{{ json_encode($page) }}"></div>
        <div id="modals"></div>
    </body>
</html>

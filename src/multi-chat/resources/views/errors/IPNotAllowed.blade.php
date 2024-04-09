<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased scrollbar">
    <div
        class="min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white flex items-center justify-center">
        <div class="max-w-7xl mx-auto px-6 pt-6 lg:px-8 lg:pt-8 pb-3">
            <x-logo />
            <div class="mt-4 flex flex-col items-center">
                @env(['nuk'])
                <p style="font-size:30px;" class="my-auto text-center text-blue-600 dark:text-blue-400">
                    {{ __('welcome.service_campus_only') }}</p>
                @else
                <p style="font-size:30px;" class="my-auto text-center text-blue-600 dark:text-blue-400">
                    {{ __('welcome.service_internal_only') }}</p>
                @endenv
                <a href="/"
                    class="p-2 mt-2 bg-blue-500 hover:bg-blue-600 rounded-lg text-white">{{ __('welcome.button.return_home') }}</a>
            </div>
        </div>
    </div>
</body>

</html>

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

@php
    try {
        if (Cookie::get('locale')) {
            App::setLocale(explode('|', Crypt::decrypt(Cookie::get('locale'), false))[1]);
        }
    } catch (\Exception $e) {
    }
@endphp

<body class="antialiased scrollbar">
    <div
        class="min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white flex items-center justify-center">
        <div class="max-w-7xl mx-auto px-6 pt-6 lg:px-8 lg:pt-8 pb-3">
            <x-logo />
            <div class="mt-4">
                <p style="font-size:50px;" class="text-center text-orange-500 dark:text-orange-300">
                    {{ __('welcome.under_maintenance.header') }}</p>
                <p style="font-size:30px;" class="my-auto text-center text-blue-600 dark:text-blue-400">
                    {!! __('welcome.under_maintenance.label') !!}</p>
            </div>
        </div>
    </div>
</body>

</html>

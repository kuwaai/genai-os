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
    <div class="min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white flex items-center justify-center">
        <div class="max-w-7xl mx-auto px-6 pt-6 lg:px-8 lg:pt-8 pb-3">
            <div class="flex items-center flex-col">
                <h3 class="text-5xl font-bold mb-2 text-blue-600 dark:text-cyan-200"><a class="flex items-center"
                        href="https://taide.tw/" target="_blank"><img class="mr-3 hidden dark:block"
                            src="{{ asset('images/TAIDE2.png') }}"><img class="mr-3 dark:hidden block"
                            src="{{ asset('images/TAIDE2_dark.png') }}"><span class="pt-4"
                            style="font-size:75px;">Chat</span></a></h3>
            </div>
            <div class="mt-4">
                <p style="font-size:50px;" class="text-center text-orange-500 dark:text-orange-300">暫停服務</p>
                <p style="font-size:50px;" class="text-center text-orange-500 dark:text-orange-300">Under Maintenance</p>

                <p style="font-size:30px;" class="my-auto text-center text-blue-600 dark:text-blue-400">我們正在進行網站維護，<br>請稍後再回來看看！</p>
                <p style="font-size:30px;" class="my-auto text-center text-blue-600 dark:text-blue-400">We're under maintenance, <br>Please come back later.</p>
            </div>
        </div>
    </div>
</body>

</html>

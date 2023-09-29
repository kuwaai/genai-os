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
        class="relative z-9999 min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        @if (Route::has('login'))
            <div class="p-6 text-right">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        @if (Auth::user()->hasPerm('tab_Dashboard'))
                            <a href="{{ url('/dashboard') }}"
                                class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
                        @endif
                        @if (Auth::user()->hasPerm('tab_Duel'))
                            <a href="{{ route('duel.home') }}"
                                class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Duel</a>
                        @endif
                        @if (Auth::user()->hasPerm('tab_Chat'))
                            <a href="{{ route('chat.home') }}"
                                class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Chat</a>
                        @endif
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                            class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('Log Out') }}</a>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log
                        in</a>

                    @if (Route::has('register') &&
                            \App\Models\SystemSetting::where('key', 'allowRegister')->where('value', 'true')->exists())
                        <a href="{{ route('register') }}"
                            class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                    @endif
                @endauth
            </div>
        @endif

        <div class="max-w-7xl mx-auto px-6 pt-6 lg:px-8 lg:pt-8 pb-3">
            <div class="flex items-center flex-col">
                <h3 class="text-5xl font-bold mb-2 text-blue-600 dark:text-cyan-200"><a class="flex items-center"
                        href="https://taide.tw/"><img class="mr-3 hidden dark:block"
                            src="{{ asset('images/TAIDE2.png') }}"><img class="mr-3 dark:hidden block"
                            src="{{ asset('images/TAIDE2_dark.png') }}"><span class="pt-4"
                            style="font-size:75px;">Chat</span></a></h3>

            </div>


            <div class="mt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    <div
                        class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div class="flex flex-col w-full">
                            <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">文本翻譯
                            </h2>
                            <div id="chatroom" class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                    <div>
                                        <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                            <p class="text-sm">請翻譯成繁體中文：The International Federation of the Phonographic Industry has announced it's latest Global Artist Chart, which features a Taiwanese artist in the top 10. ...</p>
                                        </div>
                                    </div>
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        User
                                    </div>
                                </div>
                                <div class="flex w-full mt-2 space-x-3 ">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        <img src="{{ asset('images/TAIDE.png') }}">
                                    </div>
                                    <div>
                                        <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm">
                                                國際唱片業協會公佈了最新的全球藝術家排行榜，其中一位臺灣藝術家上升至前十名。...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div class="flex flex-col w-full">
                            <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">寫文章</h2>
                            <div id="chatroom" class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                    <div>
                                        <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                            <p class="text-sm">
                                                寫一篇文章關於你曾收到的一份特別的禮物。描述你在何種情況下收到這份禮物，以及禮物的特別之處。題目：《一份特別的禮物》</p>
                                        </div>
                                    </div>
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        User
                                    </div>
                                </div>
                                <div class="flex w-full mt-2 space-x-3 ">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        <img src="{{ asset('images/TAIDE.png') }}">
                                    </div>
                                    <div>
                                        <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm">
                                                我曾經收到一份特別珍貴的禮物，那是我的朋友送我的。當時我們正在一個朋友的生日派對上，他突然走到我身旁，告訴我他有一個禮物想給我。我很驚訝，因為...
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    <div
                        class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div class="flex flex-col w-full">
                            <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">寫信
                            </h2>
                            <div id="chatroom" class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                    <div>
                                        <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                            <p class="text-sm">
                                                給一位朋友寫一封信，推薦他們去臺灣旅遊的五個必去景點以及各自的特色，並說明為什麼這些地方值得一遊。</p>
                                        </div>
                                    </div>
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        User
                                    </div>
                                </div>
                                <div class="flex w-full mt-2 space-x-3 ">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        <img src="{{ asset('images/TAIDE.png') }}">
                                    </div>
                                    <div>
                                        <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm">親愛的朋友，<br>
                                                在這封信中，我將向您推薦五個台灣必去的景點，以及每個地方的特色。這些景點都值得您一遊，以下是我對它們的詳細介紹：<br>
                                                1.台北101：台北101曾經是世界上最高的摩天大樓，...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div class="flex flex-col w-full">
                            <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">自動摘要</h2>
                            <div id="chatroom" class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                    <div>
                                        <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                            <p class="text-sm">
                                                請提供這篇文章的要點概述。DEEPMIND近日又開發出新一代AI棋類高手ALPHAZERO，只靠增強式學習演算法和遊戲規則，從隨機下棋開始歷經數百萬回自我博弈，來自學圍棋，西洋棋和日本象棋，並打敗這些棋賽的電腦程式冠軍，甚至在圍棋對弈中贏過ALPHAGO。...
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        User
                                    </div>
                                </div>
                                <div class="flex w-full mt-2 space-x-3 ">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        <img src="{{ asset('images/TAIDE.png') }}">
                                    </div>
                                    <div>
                                        <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm">本文概述如下：<br>
                                                1.開發出一代新的AI棋高手AlphaZero<br>
                                                ...
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-center mt-4 px-0 sm:items-center sm:justify-between">
                <div class="text-center text-sm text-gray-500 dark:text-gray-400 sm:text-left">
                    <div class="flex items-center gap-4">
                        <a href="https://www.nuk.edu.tw/"
                            class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                            Developed by NUK
                        </a>
                    </div>
                </div>

                <div class="ml-4 text-center text-sm text-gray-500 dark:text-gray-400 sm:text-right sm:ml-0">
                    <a class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                        href="https://www.twcc.ai/">Powered by TWCC</a>
                </div>
            </div>
            <span class="text-black dark:text-white flex justify-end text-sm">Version 0.0.6.3</span>
        </div>
    </div>
</body>

</html>

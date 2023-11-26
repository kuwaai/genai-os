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
                                class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('Dashboard') }}</a>
                        @endif
                        @if (Auth::user()->hasPerm('tab_Duel'))
                            <a href="{{ route('duel.home') }}"
                                class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('Duel') }}</a>
                        @endif
                        @if (Auth::user()->hasPerm('tab_Chat'))
                            <a href="{{ route('chat.home') }}"
                                class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('Chat') }}</a>
                        @endif
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                            class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('Sign out') }}</a>
                        <a class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                            href="{{ route('lang') }}">{{ __('Language') }}</a>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('Sign in') }}</a>

                    @if (Route::has('register') &&
                            \App\Models\SystemSetting::where('key', 'allowRegister')->where('value', 'true')->exists())
                        <a href="{{ route('register') }}"
                            class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('Sign up') }}</a>
                    @endif <a
                        class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                        href="{{ route('lang') }}">{{ __('Language') }}</a>

                @endauth
            </div>
        @endif

        <div class="max-w-7xl mx-auto px-6 pt-6 lg:px-8 lg:pt-8 pb-3">
            <div class="flex items-center flex-col">
                @env('nuk')
                <h3 class="text-5xl font-bold mb-2 text-blue-600 dark:text-cyan-200">
                    <div class="flex items-center justify-center overflow-hidden">
                        <a class="rounded-full overflow-hidden" href="https://www.csie.nuk.edu.tw/" target="_blank">
                            <img class="w-[150px]" src="{{ asset('images/csie_logo.svg') }}">
                        </a>
                        <div class="flex flex-col ml-4 text-[50px]">
                            <span>LLM</span>
                            <span class="pt-4">Workspace</span>
                        </div>
                    </div>
                </h3>
            @else
                <h3 class="text-5xl font-bold mb-2 text-blue-600 dark:text-cyan-200">
                    <a class="flex items-center overflow-hidden" href="https://taide.tw/" target="_blank">
                        <div>
                            <img class="mr-3 hidden dark:block" src="{{ asset('images/TAIDE2.png') }}">
                            <img class="mr-3 dark:hidden block" src="{{ asset('images/TAIDE2_dark.png') }}">
                        </div>
                        <span class="pt-4 text-[75px]">Chat</span>
                    </a>
                </h3>
                @endenv
            </div>


            <div class="mt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    <div
                        class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div class="flex flex-col w-full">
                            <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">
                                @env('nuk')
                                {{ __('Comparative') }}
                            @else
                                {{ __('Translation') }}
                                @endenv
                            </h2>
                            <div id="chatroom" class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">
                                @env('nuk')
                                <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                    <div>
                                        <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                            <p class="text-sm">你好</p>
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
                                        <img src="{{ asset('images/dolly.png') }}">
                                    </div>
                                    <div>
                                        <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm">你好，請問我能如何幫助你？</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex w-full mt-2 space-x-3 ">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        <img src="{{ asset('images/MetaAI.png') }}">
                                    </div>
                                    <div>
                                        <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm">嗨！請問需要甚麼幫助？</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex w-full mt-2 space-x-3 ">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        <img src="{{ asset('images/bloom.png') }}">
                                    </div>
                                    <div>
                                        <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm">您好！我是一個熱心助人的AI。</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                    <div>
                                        <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                            <p class="text-sm">請翻譯成繁體中文：The International Federation of the Phonographic
                                                Industry has announced it's latest Global Artist Chart, which features a
                                                Taiwanese artist in the top 10. ...</p>
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
                                @endenv
                            </div>
                        </div>
                    </div>
                    <div
                        class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div class="flex flex-col w-full">
                            <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">
                                @env('nuk')
                                {{ __('Feedbacks') }}
                            @else
                                {{ __('Composition') }}
                                @endenv
                            </h2>
                            <div id="chatroom" class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">
                                @env('nuk')
                                <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                    <div>
                                        <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                            <p class="text-sm">請介紹國立高雄大學大學部</p>
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
                                        <img src="{{ asset('images/csie.png') }}">
                                    </div>
                                    <div>
                                        <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm">國立高雄大學大學部的資訊工程學系的課程設計重點主要在於培養學生資訊領域的基礎知識及未來就業或研究所繼續深造的技術能力。課程設計以實用性及前瞻性為原則，以符合業界當前的技術需求，並為學生未來就業做準備。<br>
課程設計重點包括：<br>
1. 奠定學生資訊領域的基礎知識。...</p>
                                            <div class="flex space-x-1" style="">
                                                <div class="flex text-black hover:bg-gray-400 p-2 rounded-lg">
                                                    <svg stroke="currentColor" fill="none" stroke-width="2"
                                                        viewBox="0 0 24 24" stroke-linecap="round"
                                                        stroke-linejoin="round" class="icon-sm" height="1em"
                                                        width="1em" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2">
                                                        </path>
                                                        <rect x="8" y="2" width="8" height="4"
                                                            rx="1" ry="1">
                                                        </rect>
                                                    </svg>
                                                    <svg stroke="currentColor" fill="none" stroke-width="2"
                                                        viewBox="0 0 24 24" stroke-linecap="round"
                                                        stroke-linejoin="round" class="icon-sm" style="display:none;"
                                                        height="1em" width="1em"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                </div>
                                                <div class="flex hover:bg-gray-400 p-2 rounded-lg text-green-600"
                                                    data-modal-target="feedback" data-modal-toggle="feedback">
                                                    <svg stroke="currentColor" fill="none" stroke-width="2"
                                                        viewBox="0 0 24 24" stroke-linecap="round"
                                                        stroke-linejoin="round" class="icon-sm" height="1em"
                                                        width="1em" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div class="flex text-black hover:bg-gray-400 p-2 rounded-lg text-black"
                                                    data-modal-target="feedback" data-modal-toggle="feedback">
                                                    <svg stroke="currentColor" fill="none" stroke-width="2"
                                                        viewBox="0 0 24 24" stroke-linecap="round"
                                                        stroke-linejoin="round" class="icon-sm" height="1em"
                                                        width="1em" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17">
                                                        </path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
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
                                @endenv
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
                            <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">
                                @env('nuk')
                                {{ __('Developer API') }}
                            @else
                                {{ __('Communication') }}
                                @endenv
                            </h2>
                            <div id="chatroom" class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                @env('nuk')
                                <div class="bg-gray-600 dark:bg-black text-white rounded-lg overflow-hidden break-all">
                                    {{-- blade-formatter-disable --}}<span class="text-green-300">$</span> curl -X POST -H "Content-Type:
                                    application/json" -H "Authorization: Bearer YOUR_TOKEN_HERE" -d "{\"messages\": [{
                                    \"isbot\": \"false\", \"msg\": \"你好\" }],\"model\": \"MODEL_YOU_WANT_TO_USE\"}"
                                    WEB_ENDPOINT
                                    <br><br><pre class="text-green-300">{
    "status": "success",
    "message": "Authentication successful",
    "tokenable_id": -1,
    "name": "YOUR_NAME",
    "output": "你好！我今天可以如何協助你？"
}</pre>{{-- blade-formatter-enable --}}
                                </div>
                            @else
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
                                @endenv
                            </div>
                        </div>
                    </div>

                    <div
                        class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div class="flex flex-col w-full">
                            <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">
                                @env('nuk')
                                {{ __('Portalable & Distributed') }}
                            @else
                                {{ __('Summarization') }}
                                @endenv
                            </h2>
                            <div id="chatroom" class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">
                                @env('nuk')
                                <div class="flex justify-center items-center">
                                    <img class="w-auto dark:hidden" src="{{ asset('images/system_light.png') }}">
                                    <img class="w-auto hidden dark:block"
                                        src="{{ asset('images/system_dark.png') }}">
                                </div>
                            @else
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
                                @endenv
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-center mt-4 px-0 sm:items-center sm:justify-between">
                <div class="text-center text-sm text-gray-500 dark:text-gray-400 sm:text-left">
                    <div class="flex items-center gap-4">
                        @env('nuk')
                        <a href="https://www.csie.nuk.edu.tw/" target="_blank"
                            class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">由國立高雄大學
                            資訊工程學系<br>開發與維護的語言模型平台</a>
                    @else
                        <a href="https://www.nuk.edu.tw/" target="_blank"
                            class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                            {!! __('Developed by NUK and NARLabs') !!}
                        </a>
                        @endenv
                    </div>
                </div>

                <div class="ml-4 text-center text-sm text-gray-500 dark:text-gray-400 sm:text-right sm:ml-0">
                    @env('nuk')
                    <a class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                        href="https://www.nuk.edu.tw/" target="_blank">國立高雄大學</a>
                @else
                    <a class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                        href="https://www.twcc.ai/" target="_blank">{{ __('Powered by TWCC') }}</a>
                    @endenv
                    <span class="text-black dark:text-white flex justify-end text-sm">{{ __('Version') }}
                        0.0.7.1</span>
                </div>
            </div>

        </div>
    </div>
</body>

</html>

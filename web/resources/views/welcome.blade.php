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
                        href="https://taide.tw/"><img class="mr-3" src="{{ asset('images/TAIDE2.png') }}"><span
                            class="pt-4" style="font-size:75px;">Chat</span></a></h3>

            </div>

            @if (App::environment('develope'))
                <div class="mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                        <div
                            class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                            <div class="flex flex-col w-full">
                                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">LLaMA
                                </h2>
                                <div id="chatroom"
                                    class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                    <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                        <div>
                                            <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                                <p class="text-sm whitespace-pre-line">請問關於臺灣的開放資料，我要去哪裡找？</p>
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
                                            <img src="{{ asset('images/MetaAI.png') }}">
                                        </div>
                                        <div>
                                            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                                <p class="text-sm whitespace-pre-line">
                                                    臺灣的開源資料數據可以從該國內的一些大型開源資料中心或與組織來尋找，例如國家圖書館、臺灣大學、臺灣科學院、政府機構和公共圖書館。這些地方提供可用於學習、研究和開發的開源資源，包括軟件，文檔，圖書，數據庫等。此外，也可以通過網上平臺或社交媒體獲取和瀏覽開開源資料。
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                            <div class="flex flex-col w-full">
                                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">Dolly</h2>
                                <div id="chatroom"
                                    class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                    <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                        <div>
                                            <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                                <p class="text-sm whitespace-pre-line">Introduce yourself as a chatbot
                                                    named
                                                    dolly, as long as possible</p>
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
                                                <p class="text-sm whitespace-pre-line">Hi everyone! I'm dolly, an
                                                    interactive AI chatbot. I'm here to help you with all your
                                                    questions, no
                                                    matter how big or small they may be. I'm learning from you, so I can
                                                    become better at understanding and responding to your queries. What
                                                    do
                                                    you think? Do you have any additional questions for me? I can handle
                                                    any
                                                    of them!</p>
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
                                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">Bloom
                                </h2>
                                <div id="chatroom"
                                    class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                    <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                        <div>
                                            <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                                <p class="text-sm whitespace-pre-line">"I'm Bloom, a Large language
                                                    model,
                                                    Is there anything you want to ask?" 翻譯為中文</p>
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
                                            <img src="{{ asset('images/bloom.png') }}">
                                        </div>
                                        <div>
                                            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                                <p class="text-sm whitespace-pre-line">你好，我是Bloom，一個具有回答能力的大型語言生成模型</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                            <div class="flex flex-col w-full">
                                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">OPT</h2>
                                <div id="chatroom"
                                    class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                    <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                        <div>
                                            <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                                <p class="text-sm whitespace-pre-line">Tell me a list of 5 tallest
                                                    building
                                                    in USA</p>
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
                                            <img src="{{ asset('images/MetaAI.png') }}">
                                        </div>
                                        <div>
                                            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                                <p class="text-sm whitespace-pre-line">5 tallest building in USA are:
                                                    The Sears Tower in Chicago
                                                    The Empire State Building in New York City
                                                    The Burj Al Arab in UAE
                                                    The Chrysler Building in New York City</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                        <div
                            class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                            <div class="flex flex-col w-full">
                                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">寫信
                                </h2>
                                <div id="chatroom"
                                    class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                    <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                        <div>
                                            <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                                <p class="text-sm whitespace-pre-line">給一位朋友寫一封信，推薦他們去臺灣旅遊的五個必去景點以及各自的特色，並說明為什麼這些地方值得一遊。</p>
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
                                                <p class="text-sm whitespace-pre-line">親愛的朋友，
                                                    在這封信中，我將向您推薦五個台灣必去的景點，以及每個地方的特色。這些景點都值得您一遊，以下是我對它們的詳細介紹：
                                                    1. 台北101：台北101曾經是世界上最高的摩天大樓，現在仍然是台灣的象徵之一。您可以搭乘快速電梯直達觀景台，俯瞰整個台北市的美景。在這裡，您還可以品嚐當地美食和購買紀念品。
                                                    2. 九份老街：九份老街位於新北市瑞芳區，是一個充滿懷舊氛圍的街道。這禮有許多茶館、小吃攤和手工藝品店，讓您感受到台灣的歷史和文化。此外，九份老街的山海景觀也非常美麗。
                                                    3. 日月潭：日月潭位於台中市和南投縣交界處，是台灣最著名的湖泊之一。這個地方以其美麗的自然風光和豐富的活動而聞名。您可以乘船遊覽湖泊，欣賞湖光山色，或者參加各種戶外活動，如划艇、划槳和徒步旅行。
                                                    4. 墾丁國家公園：墾丁位於台灣南部，是台灣著名的海岸度假勝地。這裏有美麗的沙灘、清澈的海水和豐富的海洋生物。此外，墾丁還提供各種水上活動，如衝浪、潛水和獨木舟。
                                                    5. 阿里山國家風景區：阿里山位於南部中心地帶，是台灣一個森林遊樂區。這座山脈擁有豐富的生態資源和多樣化的植被。您可以在這裏欣賞到壯觀的山林景色，並體驗臺灣原住民的生活和文化。
                                                    以上五個景點都具有獨特的特色和魅力。無論您是尋求冒險還是感受文化氛圍，這些地方都值得您去探索。希望您在來台灣時能夠充分體驗，並在此停留一段時間。
                                                    祝您旅途愉快！
                                                    敬祝順心，
                                                    [您的名字]</p>
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
                                <div id="chatroom"
                                    class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                    <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                        <div>
                                            <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                                <p class="text-sm whitespace-pre-line">請提供這篇文章的要點概述。臺灣人工智慧實驗室臺灣，DEEPMIND近日又開發出新一代AI棋類高手ALPHAZERO，只靠增強式學習演算法和遊戲規則，從隨機下棋開始歷經數百萬回自我博弈，來自學圍棋，西洋棋和日本象棋，並打敗這些棋賽的電腦程式冠軍，甚至在圍棋對弈中贏過ALPHAGO。電子大廠三星日前於2018年北美放射學會上，連同自家醫療裝置子公司三星MEDISON，展示了用於超音波、數位放射攝影、電腦斷層掃描和核磁共振影像的AI診斷成像軟體、包括協助判讀乳房病變的S-DETECT軟體、AI顱內出血系統，以及可針對膝關節問題，顯示患者膝關節軟骨厚度和膝關節炎患者，臺灣人工智慧學校正式開學了！密集技術領域培訓班的學員，以及來自電子業及製造業、資訊業、金融業和生技醫療4大產業佔大多數；臺灣人工智慧實驗室，近日推出記者快抄預覽版第一版，透過AI技術將PTT版上的熱門文章，重新撰寫為新聞，每天可以從PTT熱門文章中，自動產出大臺灣人工智慧實驗室創造的記者快抄預覽版第一版上線了！透過AI技術將PTT版上的熱門文章，重新撰寫為新聞，一天就可以自動產生大約500篇文章。</p>
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
                                                <p class="text-sm whitespace-pre-line">本文概述如下：
                                                    1. 開發出一代新的AI棋高手AlphaZero
                                                    2. 三星在放射學會展示了多項AI影像診斷軟體
                                                    3. AI學校正式開學，密集技術培訓
                                                    4. PTT版熱門文章可自動產出，每日500篇</p>
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
                                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">文本翻譯
                                </h2>
                                <div id="chatroom"
                                    class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                    <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                        <div>
                                            <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                                <p class="text-sm whitespace-pre-line">請翻譯成繁體中文：The International Federation of the Phonographic Industry has announced it's latest Global Artist Chart, which features a Taiwanese artist in the top 10. Joining U.S. artists such as Taylor Swift and Drake, and K-Pop sensation BTS, was Taiwan's own Jay Chou, at No. 9 in the world, He's the first Taiwanese artist to appear on the chart, fresh off the release of his latest album last year titled "Greatest Works of Art."</p>
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
                                                <p class="text-sm whitespace-pre-line">國際唱片業協會公佈了最新的全球藝術家排行榜，其中一位臺灣藝術家上升至前十名。與美國藝術家泰勒·斯威夫特和韓國男子音樂團體BTS一同上榜的是臺灣歌手周杰倫，他的最新專輯《藝術家》在全球範圍內獲得了廣泛關注和好評。</p>
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
                            <div id="chatroom"
                                class="flex-1 p-4 overflow-hidden flex flex-col scrollbar rounded-lg">

                                <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                                    <div>
                                        <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                            <p class="text-sm whitespace-pre-line">寫一篇文章關於你曾收到的一份特別的禮物。描述你在何種情況下收到這份禮物，以及禮物的特別之處。題目：《一份特別的禮物》</p>
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
                                            <p class="text-sm whitespace-pre-line">我曾經收到一份特別珍貴的禮物，那是我的朋友送我的。當時我們正在一個朋友的生日派對上，他突然走到我身旁，告訴我他有一個禮物想給我。我很驚訝，因為我不知他有什麼禮物可以送給我。當他拿出一個小盒子時，我簡直不敢相信自己的眼睛。那是一個非常精美的小盒子，上面鑲嵌著閃閃發光的水晶，看起來非常珍貴。他告訴我這是我的生日禮物，我非常感激他的體貼和慷慨。這是一份非常特別的禮品，因為它不僅僅是一個物質的禮物，而且還帶有他對我的關愛和友誼的深厚感激之情。這份寶貴的禮物讓我感到非常溫暖和幸福，也讓我更加珍惜我和我的朋友之間的友誼。</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                        
                    </div>
                </div>
            @endif
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
            <span class="text-white flex justify-end text-sm">Version 0.0.6</span>
        </div>
    </div>
</body>

</html>

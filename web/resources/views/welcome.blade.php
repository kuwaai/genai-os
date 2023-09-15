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
                                                <p class="text-sm whitespace-pre-line">國際唱片業協會公佈2020年上半年全球版稅收入報告，華語歌手排名前三，僅次於西洋天王后泰勒·斯威夫特（Taylor Swift）和英國天后艾黛·阿德金斯（Adele Adkins）。據報告顯示，斯威夫特在全球版稅上半年收入高達3,441萬美元，她的專輯《Fearless》也是全球收入最高的專載；而阿德金斯則以3,081萬美元位居第二名。而華語歌手的代表人物李宗盛、張學友和陶喆則分列第12名、第22名和第36名，顯示強勁的實力和超強的市場潛力。 Chou是亞洲歌手中唯一入選的歌手。這是他自2015年以來第四次上榜。Jay Chou在亞洲、歐洲和北美都有廣泛的粉絲群體。 他還開設了自己的Youtuber平台，在YouTube上擁有超過2200萬的關注者，是全球最成功的華語歌手之一。</p>
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
                                            <p class="text-sm whitespace-pre-line">寫一篇文章關於你曾收到的一份特別的禮物。描述你在任何種情況下收到這份禮物，以及禮物的特別之處。題目：《一份特別的禮物》</p>
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
                                            <p class="text-sm whitespace-pre-line">對我而言，最特別的禮貌之一，是一份來自家人的禮物。在我人生中最難忘的一天，它送上了一份神秘的小包裹。當我打開它時，裡面有一張小紙條，上面寫著一個詞語：“生日快樂！”

                                                這個小小的紙條帶給我一份無法形容的喜悅。這是我第一次收到家人的生日禮物，它讓我感到非常特別和被愛。我知道，在我生活中最重要的人們，那些真正能夠帶給無形快樂的人們，他們送上了這份特別的生日禮貌。
                                                
                                                當我回頭看，我發現家人為我做了其他的事情，但是這份小小的生日禮品，將永遠是我心中最珍貴的寶藏。它給了我一份特別而甜美的回憶，更是一個永遠不能忘記的時刻。
                                                
                                                當然，這份生日禮物不僅僅帶給了我甜美的回忆，它也提醒我們，儘管大多數的禮物都只能維持數日的時間，但是類似這樣尖銳、獨特、特別的回憶，將永遠留存在我的心中，甚至是我的生命。</p>
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

                                                    您好！我寫信給您，是想向您介紹一個非常值得一遊的地方——臺灣。臺灣地處亞洲中心地帶，擁有豐富的自然景觀和豐富的文化遺產。以下是我推薦給您的臺灣五大必去景點及其特色。
                                                    
                                                    1. 台北101大樓：位於台北的101大樂樂擁有全世界最高的觀光台，可以在這裡欣賞到絕美的台北風景。此外，還可以體驗一場高空觀光的刺激。
                                                    2. 鹿野風景區：這裡是臺灣的著名茶鄉。您可以在此品味到美味的臺灣茶，並享受到美麗的茶園風光。
                                                    3. 日月潭：這是臺灣最大的淡水湖，也是臺灣著名的觀光勝地之一。您可以乘坐電動船遊覽湖面，同時欣賞湖畔的美景。
                                                    4. 玉山：玉山是臺灣最高峰，也是台灔八大奇景之一。您在這裏可以體體驗到踏上臺灣最高山峰的快感。
                                                    5. 野柳地質公園：這裏的地質特殊，以其各種奇形怪狀的海岸景觀而聞名。您可以親身體驗各種不同的海蝕棧道景觀。
                                                    
                                                    以上是我推荐的臺灣五個必去的景點及其主要特色。臺灣有許多美麗的風景和豐富的人文景觀，這些景點都值得您一遊。希望您能在疫情過後實現這次旅遊之旅。祝您行遊愉快！
                                                    祝好！
                                                    
                                                    您真誠的朋友</p>
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
                                                <p class="text-sm whitespace-pre-line">1. 台灣人工智能實驗所開發出最新一代AI棄類高手ALPHACARO，利用增強學習漫算法和遊具規則進行自我博戰，學會圍棋、西洋棋和中日三棋，且在圍棒比賽中贏運原本的圍棋世界冠軍AlphaGo。
                                                    2. 三星展示了用施超音波、数動放射攥、電腦掃描、核磁感共振影像等AI診施成像軟件，包括协助判诊乳房症變的S- Detect軟件、AI领内出血系统以及可叉对膝关节问题的农关系统。
                                                    3. 台灣AI人工學校已正式開學，學員包括印電子業、制造業、金融业和生命医疗4大领域。
                                                    4. 台灣RI人工實驗中心推出記快抄快预览版第一版，采用AI技术自动从PTT版热门文章中生成新闻，一天可生成大约500篇新闻。</p>
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

<div class="mt-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
        <div
            class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline-red-500">
            <div class="flex flex-col w-full">
                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">
                    {{ __('welcome.comparative') }}</h2>
                <div id="chatroom" class="flex-1 p-4 justify-center overflow-hidden flex flex-col scrollbar rounded-lg">
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
                            <img src="{{ asset('images/TAIDE.png') }}">
                        </div>
                        <div>
                            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                <p class="text-sm">
                                    您好！我是TAIDE，一個來自台灣的AI助理，樂於以台灣人的立場幫助您，使用繁體中文來回答您的問題。請您隨時提出問題，我將盡我所能給予協助。
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex w-full mt-2 space-x-3 ">
                        <div
                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('images/taibun.png') }}">
                        </div>
                        <div>
                            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                <p class="text-sm">你好！有啥物我會使幫助你的？</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex w-full mt-2 space-x-3 ">
                        <div
                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('images/hakka.png') }}">
                        </div>
                        <div>
                            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                <p class="text-sm">你好！當歡喜看著你。有麼个𠊎做得𢯭手个無？</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex w-full mt-2 space-x-3 ">
                        <div
                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('images/meta.png') }}">
                        </div>
                        <div>
                            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                <p class="text-sm">Hello! 😊 I'm here to help you with any questions or
                                    concerns you may have. Please feel free to...</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex w-full mt-2 space-x-3 ">
                        <div
                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('images/chatglm.png') }}">
                        </div>
                        <div>
                            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                <p class="text-sm">你好👋！我是人工智能助手 ChatGLM3-6B，很高兴见到你，欢迎问我任何问题。</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline-red-500">
            <div class="flex flex-col w-full">
                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">
                    {{ __('welcome.deployment') }}</h2>
                <div id="chatroom"
                    class="flex-1 p-4 justify-center overflow-hidden flex flex-col scrollbar rounded-lg">
                    <div class="flex justify-center items-center">
                        <img class="w-auto dark:hidden" src="{{ asset('images/deployment_light.png') }}">
                        <img class="w-auto hidden dark:block" src="{{ asset('images/deployment_dark.png') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="mt-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
        <div
            class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline-red-500">
            <div class="flex flex-col w-full">
                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">
                    {{ __('welcome.export_and_import') }}</h2>
                <div id="chatroom"
                    class="flex-1 p-4 justify-center overflow-hidden flex flex-col scrollbar rounded-lg">
                    <div class="flex justify-center items-center">
                        <img class="w-auto dark:hidden" src="{{ asset('images/feedback.png') }}">
                        <img class="w-auto hidden dark:block" src="{{ asset('images/feedback.png') }}">
                    </div>
                </div>
            </div>
        </div>

        <div
            class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline-red-500">
            <div class="flex flex-col w-full">
                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">
                    {{ __('welcome.application') }}</h2>
                <div id="chatroom"
                    class="flex-1 p-4 justify-center overflow-hidden flex flex-col scrollbar rounded-lg">
                    <div class="flex justify-center items-center">
                        <img class="w-auto dark:hidden" src="{{ asset('images/rag_light.png') }}">
                        <img class="w-auto hidden dark:block" src="{{ asset('images/rag_dark.png') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

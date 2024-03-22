<div class="mt-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
        <div
            class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline-red-500">
            <div class="flex flex-col w-full">
                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">
                    {{ __('welcome.concurrent_multichat') }}</h2>
                <div id="chatroom" class="flex-1 p-4 justify-center overflow-hidden flex flex-col scrollbar rounded-lg">
                    <div class="flex w-full mt-2 space-x-3 ml-auto justify-end">
                        <div>
                            <div class="p-3 bg-blue-600 text-white rounded-l-lg rounded-br-lg">
                                <p class="text-sm">Hi bot!</p>
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
                            <img src="{{ asset('images/chatgpt.png') }}">
                        </div>
                        <div>
                            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                <p class="text-sm">Hello! How can I assist you today?</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex w-full mt-2 space-x-3 ">
                        <div
                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('images/geminipro.png') }}">
                        </div>
                        <div>
                            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                <p class="text-sm">Hello! How can I help you today?</p>
                            </div>
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
                                    Hello! I am TAIDE, an AI assistant from Taiwan. I am delighted to help you and answer questions. Is there anything I can assist you with?
                                </p>
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
                                <p class="text-sm">Hello! I'm here to help you with any questions or concerns you may have. I'm programmed to provide respectful, honest, and socially unbiased responses, and I will always do my best to assist you in a positive and safe manner. </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline-red-500">
            <div class="flex flex-col w-full">
                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">{{ __('welcome.opensource') }}</h2>
                <div id="chatroom"
                    class="flex-1 p-4 justify-center overflow-hidden flex flex-col scrollbar rounded-lg">
                    <div class="flex justify-center items-center">
                        <img class="w-auto" src="{{ asset('images/architecture.svg') }}">
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
                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">{{ __('welcome.onedgecloud') }}</h2>
                <div id="chatroom"
                    class="flex-1 p-4 justify-center overflow-hidden flex flex-col scrollbar rounded-lg">
                    <div class="flex justify-center items-center">
                        <img class="w-auto" src="{{ asset('images/crossplatform.png') }}">
                    </div>
                </div>
            </div>
        </div>

        <div
            class="scale-100 justify-center p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline-red-500">
            <div class="flex flex-col w-full">
                <h2 class="text-xl font-semibold text-center text-gray-900 dark:text-white">
                    {{ __('welcome.totalsolution') }}</h2>
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

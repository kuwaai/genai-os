<div class="flex items-center flex-col">
    @env(['kuwa', 'arena', 'nuk', 'chipllm', 'icdesign'])
    <h3 class="text-5xl font-bold mb-2 text-blue-600 dark:text-cyan-200">
        <div class="flex items-center justify-center overflow-hidden">
            @env(['nuk'])
            <h3 class="text-5xl font-bold mb-2 text-blue-600 dark:text-cyan-200">
                <a class="flex items-center overflow-hidden" href="https://www.nuk.edu.tw/" target="_blank">
                    <div class="hidden md:block">
                        <img class="mr-3 hidden dark:block" src="{{ asset('images/nuk.png') }}">
                        <img class="mr-3 dark:hidden block" src="{{ asset('images/nuk_dark.png') }}">
                    </div>
                    <img class="block md:hidden rounded-full w-[150px]" src="{{ asset('images/nuk_logo.jpg') }}">
                    <div class="flex md:hidden flex-col ml-4 text-[50px]">
                        <span>NUK</span>
                        <span class="pt-4">Chat</span>
                    </div>
                    <span class="hidden md:block text-[75px]">Chat</span>
                </a>
            </h3>
            @endenv
            @env(['csie'])
            <a class="rounded-full overflow-hidden" href="https://www.csie.nuk.edu.tw/" target="_blank">
                <img class="w-[150px]" src="{{ asset('images/csie.png') }}">
            </a>
            @endenv
            @env(['kuwa', 'arena'])
            <a class="rounded-full overflow-hidden" href="https://www.gai.tw/" target="_blank">
                <img class="w-[150px]" src="{{ asset('images/csie.png') }}">
            </a>
            @endenv
            @env(['chipllm', 'icdesign'])
            <a class="rounded-full overflow-hidden" href="https://www.gai.tw/" target="_blank">
                <img class="w-[150px]" src="{{ asset('images/icdesign.jpg') }}">
            </a>
            @endenv
            @env('csie')
            <div class="flex flex-col ml-4 text-[50px]">
                <span>LLM</span>
                <span class="pt-4">Workspace</span>
            </div>
            @endenv
            @env('kuwa', 'arena', 'chipllm')
            <div class="flex ml-4 justify-center items-end space-x-5">
                <span class="text-[72px] text-orange-300">Kuwa</span>
                @env('kuwa')
                <span class="text-[60px]">Chat</span>
                @endenv
                @env('arena')
                <span class="text-[60px]">Arena</span>
                @endenv
                @env('chipllm')
                <span class="text-[60px]">Chip</span>
                @endenv
            </div>
            @endenv
            @env('icdesign')
            <div class="flex flex-col ml-4 text-[50px]">
                <span class="text-[72px] text-orange-300">Kuwa</span>
                <span class="text-[60px]">IC Design</span>
            </div>
            @endenv
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

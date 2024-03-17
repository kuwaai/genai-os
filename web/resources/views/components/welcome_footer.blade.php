<div class="flex justify-center mt-4 px-0 sm:items-center sm:justify-between">
    <div class="text-center text-sm text-gray-500 dark:text-gray-400 sm:text-left">
        <div class="flex items-center gap-4">
            @env(['kuwa', 'arena', 'nuk', 'csie', 'chipllm', 'icdesign'])
            <a href="https://www.gai.tw/" target="_blank"
                class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:rounded-sm focus:outline-red-500">由國立高雄大學
                資訊工程學系<br>開發與維護的語言模型平台</a>
        @else
            <a href="https://www.nuk.edu.tw/" target="_blank"
                class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:rounded-sm focus:outline-red-500">
                {!! __('welcome.develope_by') !!}
            </a>
            @endenv
        </div>
    </div>

    <div class="ml-4 text-center text-sm text-gray-500 dark:text-gray-400 sm:text-right sm:ml-0">
        @env(['kuwa', 'arena', 'nuk', 'csie', 'chipllm', 'icdesign'])
        @env(['nuk', 'csie'])
        <a class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:rounded-sm focus:outline-red-500"
            href="https://www.nuk.edu.tw/" target="_blank">國立高雄大學</a>
    @else
        <a class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:rounded-sm focus:outline-red-500"
            href="https://www.gai.tw/" target="_blank">Kuwa</a>
        @endenv
    @else
        <a class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:rounded-sm focus:outline-red-500"
            href="https://www.twcc.ai/" target="_blank">{{ __('welcome.powered_by') }}</a>
        @endenv
        <span class="text-black dark:text-white flex justify-end text-sm">{{ __('welcome.version') }}
            {{config("app.Version")}}</span>
    </div>
</div>
<div class="flex flex-1">
    <!-- Navigator Sidebar -->
    <div class="bg-gray-100 dark:bg-gray-700 p-4 overflow-y-auto">
        <ul data-tabs-toggle="#Contents" role="tablist" class="space-y-2">
            <li role="presentation">
                <button id="executors-tab" data-tabs-target="#executors" type="button" role="tab"
                    aria-controls="executors" aria-selected="true"
                    class="block w-full p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('manage.kernel.tab.executors') }}
                </button>
            </li>
            <li role="presentation">
                <button id="hub-tab" data-tabs-target="#hub" type="button" role="tab" aria-selected="false"
                    aria-controls="hub"
                    onclick="$('#ollama, #kuwa, #huggingface').hide(); $('#button-container').show();"
                    class="block w-full p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('manage.kernel.tab.hub') }}
                </button>
            </li>
            <li role="presentation">
                <button id="storage-tab" data-tabs-target="#storage" type="button" role="tab" aria-selected="false"
                    aria-controls="storage"
                    class="block w-full p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('manage.kernel.tab.storage') }}
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Contents -->
    <div id="Contents" class="flex flex-1 overflow-hidden">
        <!-- executors Tab Panel -->
        <div class="flex-1 bg-gray-50 dark:bg-gray-800 overflow-hidden" id="executors" role="tabpanel"
            aria-labelledby="executors-tab">
            @include('manage.tabs.kernel.tabs.executors')
        </div>

        <!-- Hub Tab Panel -->
        <div class="flex-1 bg-gray-50 dark:bg-gray-800 overflow-hidden flex justify-center items-center" id="hub" role="tabpanel"
            aria-labelledby="hub-tab">
            <div class="flex justify-center">
                <div class="bg-gray-100 dark:bg-gray-700 p-4 m-4 rounded-lg flex justify-center items-center flex-col"
                    id="button-container">
                    <button id="huggingface-tab"
                        class="tab-button block p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600"
                        onclick="$('#button-container').hide(); $('#huggingface').show(); $('#ollama, #kuwa').hide();">
                        {{ __('hub.tab.huggingface') }}
                    </button>
                    <button id="ollama-tab"
                        class="tab-button block p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600"
                        onclick="$('#button-container').hide(); $('#ollama').show(); $('#huggingface, #kuwa').hide();">
                        {{ __('hub.tab.ollama') }}
                    </button>
                    <button id="kuwa-tab"
                        class="tab-button block p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600"
                        onclick="$('#button-container').hide(); $('#kuwa').show(); $('#huggingface, #ollama').hide();">
                        {{ __('hub.tab.kuwa') }}
                    </button>
                </div>
            </div>
            <!-- Hugging Face Tab Panel -->
            <div class="flex-1 bg-gray-50 dark:bg-gray-800 overflow-hidden h-full hidden" id="huggingface"
                role="tabpanel" aria-labelledby="huggingface-tab">
                @include('manage.tabs.kernel.tabs.hub.huggingface')
            </div>

            <!-- Ollama Tab Panel -->
            <div class="hidden flex-1 bg-gray-50 dark:bg-gray-800 overflow-hidden" id="ollama" role="tabpanel"
                aria-labelledby="ollama-tab">
                <!-- Ollama content goes here -->
                WIP
            </div>

            <!-- Kuwa Tab Panel -->
            <div class="hidden flex-1 bg-gray-50 dark:bg-gray-800 overflow-x-hidden overflow-y-auto scrollbar p-6"
                id="kuwa" role="tabpanel" aria-labelledby="kuwa-tab">
                <!-- Kuwa content goes here -->
                WIP
            </div>
        </div>

        <!-- Storage Tab Panel -->
        <div class="hidden flex-1 bg-gray-50 dark:bg-gray-800 overflow-x-hidden overflow-y-auto scrollbar p-6"
            id="storage" role="tabpanel" aria-labelledby="storage-tab">
            @include('manage.tabs.kernel.tabs.storage')
        </div>
    </div>
</div>

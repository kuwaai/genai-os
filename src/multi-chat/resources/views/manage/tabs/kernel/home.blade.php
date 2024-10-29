<div class="flex flex-1">
    <!-- Navigator Sidebar -->
    <div class="bg-gray-100 dark:bg-gray-700 p-4 overflow-y-auto">
        <ul data-tabs-toggle="#Contents" role="tablist" class="space-y-2">
            <li role="presentation">
                <button id="record-tab" data-tabs-target="#record" type="button" role="tab" aria-controls="record"
                    aria-selected="true"
                    class="block w-full p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('Records') }}
                </button>
            </li>
            <li role="presentation">
                <button id="huggingface-tab" data-tabs-target="#huggingface" type="button" role="tab"
                    aria-selected="false" aria-controls="huggingface"
                    class="block w-full p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('Huggingface') }}
                </button>
            </li>
            <li role="presentation">
                <button id="storage-tab" data-tabs-target="#storage" type="button" role="tab" aria-selected="false"
                    aria-controls="storage"
                    class="block w-full p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('Storage') }}
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Contents -->
    <div id="Contents" class="flex flex-1 overflow-hidden">
        <!-- Record Tab Panel -->
        <div class="flex-1 bg-gray-50 dark:bg-gray-800 overflow-hidden" id="record" role="tabpanel"
            aria-labelledby="record-tab">
            @include('manage.tabs.kernel.tabs.records')
        </div>

        <!-- Huggingface Tab Panel -->
        <div class="flex-1 bg-gray-50 dark:bg-gray-800 overflow-hidden" id="huggingface" role="tabpanel"
            aria-labelledby="huggingface-tab">
            @include('manage.tabs.kernel.tabs.huggingface')
        </div>

        <!-- Storage Tab Panel -->
        <div class="hidden flex-1 bg-gray-50 dark:bg-gray-800 overflow-x-hidden overflow-y-auto scrollbar p-6"
            id="storage" role="tabpanel" aria-labelledby="storage-tab">
            @include('manage.tabs.kernel.tabs.storage')
        </div>
    </div>
</div>

<div class="flex flex-1">
    <!-- Navigator Sidebar -->
    <div class="bg-gray-100 dark:bg-gray-700 p-4 overflow-y-auto">
        <ul data-tabs-toggle="#Contents" role="tablist" class="space-y-2">
            <li role="presentation">
                <button id="executors-tab" data-tabs-target="#executors" type="button" role="tab" aria-controls="executors"
                    aria-selected="true"
                    class="block w-full p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('manage.kernel.tab.executors') }}
                </button>
            </li>
            <li role="presentation">
                <button id="hub-tab" data-tabs-target="#hub" type="button" role="tab"
                    aria-selected="false" aria-controls="hub"
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

        <!-- hub Tab Panel -->
        <div class="flex-1 bg-gray-50 dark:bg-gray-800 overflow-hidden" id="hub" role="tabpanel"
            aria-labelledby="hub-tab">
            @include('manage.tabs.kernel.tabs.hub')
        </div>

        <!-- Storage Tab Panel -->
        <div class="hidden flex-1 bg-gray-50 dark:bg-gray-800 overflow-x-hidden overflow-y-auto scrollbar p-6"
            id="storage" role="tabpanel" aria-labelledby="storage-tab">
            @include('manage.tabs.kernel.tabs.storage')
        </div>
    </div>
</div>

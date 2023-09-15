<x-app-layout>
    <div class="py-2 h-full">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 h-full">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
                <div class="p-6 text-gray-900 dark:text-gray-100 h-full">
                    <script>
                        $groups = {}
                    </script>
                    <section class="flex flex-col h-full overflow-hidden">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Management Interface') }}
                            </h2>
                        </header>
                        <div class="flex-1 flex overflow-hidden flex-col border-gray-700 rounded border-2">
                            <div class="border-b border-gray-200 dark:border-gray-700">
                                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center"
                                    data-tabs-toggle="#Contents" role="tablist">
                                    <li class="mr-2" role="presentation">
                                        <button class="inline-block p-4 border-b-2 rounded-t-lg" id="groups-tab"
                                            data-tabs-target="#groups" type="button" role="tab"
                                            aria-controls="groups" aria-selected="{{session('last_tab') ? 'false' : 'true'}}">Groups</button>
                                    </li>
                                    <li class="mr-2" role="presentation">
                                        <button
                                            class="inline-block p-4 border-b-2 rounded-t-lg"
                                            id="users-tab" data-tabs-target="#users" type="button" role="tab"
                                            aria-controls="users" aria-selected="{{session('last_tab') == 'users' ? 'true' : 'false'}}">Users</button>
                                    </li>
                                    <li class="mr-2" role="presentation">
                                        <button
                                            class="inline-block p-4 border-b-2 rounded-t-lg"
                                            id="llms-tab" data-tabs-target="#llms" type="button" role="tab"
                                            aria-controls="llms" aria-selected="{{session('last_tab') == 'llms' ? 'true' : 'false'}}">LLMs</button>
                                    </li>
                                    <li class="mr-2" role="presentation">
                                        <button
                                            class="inline-block p-4 border-b-2 rounded-t-lg"
                                            id="settings-tab" data-tabs-target="#settings" type="button" role="tab"
                                            aria-controls="settings" aria-selected="{{session('last_tab') == 'settings' ? 'true' : 'false'}}">Settings</button>
                                    </li>
                                </ul>
                            </div>
                            <div id="Contents" class="flex flex-1 overflow-hidden">
                                <div class="{{session('last_tab') ? 'hidden' : ''}} bg-gray-50 flex flex-1 dark:bg-gray-800" id="groups" role="tabpanel"
                                    aria-labelledby="groups-tab">
                                    @include('manage.tabs.groups')
                                </div>
                                <div class="{{session('last_tab') == 'users' ? '' : 'hidden'}} bg-gray-50 flex flex-1 dark:bg-gray-800" id="users"
                                    role="tabpanel" aria-labelledby="users-tab">
                                    @include('manage.tabs.users')
                                </div>
                                <div class="{{session('last_tab') == 'llms' ? '' : 'hidden'}} bg-gray-50 flex flex-1 dark:bg-gray-800" id="llms"
                                    role="tabpanel" aria-labelledby="llms-tab">
                                    @include('manage.tabs.llms')
                                </div>
                                <div class="{{session('last_tab') == 'settings' ? '' : 'hidden'}} bg-gray-50 flex flex-1 dark:bg-gray-800" id="settings"
                                    role="tabpanel" aria-labelledby="settings-tab">
                                    @include('manage.tabs.settings')
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

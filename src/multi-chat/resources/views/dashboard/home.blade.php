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
                                {{ __('dashboard.header.interface') }}
                            </h2>
                        </header>
                        <div class="flex-1 flex overflow-hidden flex-col border-gray-700 rounded border-2">
                            <div class="border-b border-gray-200 dark:border-gray-700">
                                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center"
                                    data-tabs-toggle="#Contents" role="tablist">
                                    @if (Auth::user()->hasPerm('Dashboard_read_statistics'))
                                        <li class="mr-2" role="presentation">
                                            <button class="inline-block p-4 border-b-2 rounded-t-lg" id="statistics-tab"
                                                data-tabs-target="#statistics" type="button" role="tab"
                                                aria-controls="statistics"
                                                aria-selected="{{ session('last_tab') ? 'false' : 'true' }}">{{ __('dashboard.tab.statistics') }}</button>
                                        </li>
                                    @endif
                                    @if (Auth::user()->hasPerm('Dashboard_read_blacklist'))
                                        <li class="mr-2" role="presentation">
                                            <button class="inline-block p-4 border-b-2 rounded-t-lg" id="blacklist-tab"
                                                data-tabs-target="#blacklist" type="button" role="tab"
                                                aria-controls="blacklist"
                                                aria-selected="{{ session('last_tab') == 'blacklist' ? 'true' : 'false' }}">{{ __('dashboard.tab.blacklist') }}</button>
                                        </li>
                                    @endif
                                    @if (Auth::user()->hasPerm('Dashboard_read_feedbacks'))
                                        <li class="mr-2" role="presentation">
                                            <button class="inline-block p-4 border-b-2 rounded-t-lg" id="feedbacks-tab"
                                                data-tabs-target="#feedbacks" type="button" role="tab"
                                                aria-controls="feedbacks"
                                                aria-selected="{{ session('last_tab') == 'feedbacks' ? 'true' : 'false' }}">{{ __('dashboard.tab.feedbacks') }}</button>
                                        </li>
                                    @endif
                                    @if (Auth::user()->hasPerm('Dashboard_read_logs'))
                                        <li class="mr-2" role="presentation">
                                            <button class="inline-block p-4 border-b-2 rounded-t-lg" id="logs-tab"
                                                data-tabs-target="#logs" type="button" role="tab"
                                                aria-controls="logs"
                                                aria-selected="{{ session('last_tab') == 'logs' ? 'true' : 'false' }}">{{ __('dashboard.tab.logs') }}</button>
                                        </li>
                                    @endif
                                    @if (Auth::user()->hasPerm('Dashboard_read_safetyguard'))
                                        <li class="mr-2" role="presentation">
                                            <button class="inline-block p-4 border-b-2 rounded-t-lg"
                                                id="safetyguard-tab" data-tabs-target="#safetyguard" type="button"
                                                role="tab" aria-controls="safetyguard"
                                                aria-selected="{{ session('last_tab') == 'safetyguard' ? 'true' : 'false' }}">{{ __('dashboard.tab.safetyguard') }}</button>
                                        </li>
                                    @endif
                                    @if (Auth::user()->hasPerm('Dashboard_read_inspect'))
                                        <li class="mr-2" role="presentation">
                                            <button class="inline-block p-4 border-b-2 rounded-t-lg" id="inspect-tab"
                                                data-tabs-target="#inspect" type="button" role="tab"
                                                aria-controls="inspect"
                                                aria-selected="{{ session('last_tab') == 'inspect' ? 'true' : 'false' }}">{{ __('dashboard.tab.inspect') }}</button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            <div id="Contents" class="flex flex-1 overflow-hidden">
                                @if (Auth::user()->hasPerm('Dashboard_read_statistics'))
                                    <div class="{{ session('last_tab') ? 'hidden' : '' }} bg-gray-200 flex flex-1 dark:bg-gray-700"
                                        id="statistics" role="tabpanel" aria-labelledby="statistics-tab">
                                        @include('dashboard.tabs.statistics')
                                    </div>
                                @endif
                                @if (Auth::user()->hasPerm('Dashboard_read_blacklist'))
                                    <div class="{{ session('last_tab') == 'blacklist' ? '' : 'hidden' }} bg-gray-200 flex flex-1 dark:bg-gray-700"
                                        id="blacklist" role="tabpanel" aria-labelledby="blacklist-tab">
                                        @include('dashboard.tabs.blacklist')
                                    </div>
                                @endif
                                @if (Auth::user()->hasPerm('Dashboard_read_feedbacks'))
                                    <div class="{{ session('last_tab') == 'feedbacks' ? '' : 'hidden' }} bg-gray-200 flex flex-1 dark:bg-gray-700"
                                        id="feedbacks" role="tabpanel" aria-labelledby="feedbacks-tab">
                                        @include('dashboard.tabs.feedbacks')
                                    </div>
                                @endif
                                @if (Auth::user()->hasPerm('Dashboard_read_logs'))
                                    <div class="{{ session('last_tab') == 'logs' ? '' : 'hidden' }} bg-gray-200 flex flex-1 dark:bg-gray-700 overflow-x-hidden"
                                        id="logs" role="tabpanel" aria-labelledby="logs-tab">
                                        @include('dashboard.tabs.logs')
                                    </div>
                                @endif
                                @if (Auth::user()->hasPerm('Dashboard_read_safetyguard'))
                                    <div class="{{ session('last_tab') == 'safetyguard' ? '' : 'hidden' }} bg-gray-200 flex flex-1 dark:bg-gray-700 overflow-x-hidden"
                                        id="safetyguard" role="tabpanel" aria-labelledby="safetyguard-tab">
                                        @include('dashboard.tabs.safetyguard')
                                    </div>
                                @endif
                                @if (Auth::user()->hasPerm('Dashboard_read_inspect'))
                                    <div class="{{ session('last_tab') == 'inspect' ? '' : 'hidden' }} bg-gray-200 flex flex-1 dark:bg-gray-700 overflow-x-hidden"
                                        id="inspect" role="tabpanel" aria-labelledby="inspect-tab">
                                        @include('dashboard.tabs.inspect')
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

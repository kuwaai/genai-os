<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('/') }}">
                        <x-APP-Logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                @if (Auth::user()->hasPerm('tab_Dashboard'))
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <x-nav-link :href="route('dashboard.home')" :active="request()->routeIs('dashboard.*')">
                            {{ __('dashboard.route') }}
                        </x-nav-link>
                    </div>
                @endif
                @if (Auth::user()->hasPerm('tab_Room'))
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <x-nav-link :href="route('room.home')" :active="request()->routeIs('room.*')">
                            {{ __('room.route') }}
                        </x-nav-link>
                    </div>
                @endif
                @if (Auth::user()->hasPerm('tab_Chat'))
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <x-nav-link :href="route('chat.home')" :active="request()->routeIs('chat.*')">
                            {{ __('chat.route') }}
                        </x-nav-link>
                    </div>
                @endif
                @if (Auth::user()->hasPerm('tab_Archive'))
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <x-nav-link :href="route('archive.home')" :active="request()->routeIs('archive.*')">
                            {{ __('archive.route') }}
                        </x-nav-link>
                    </div>
                @endif
                @if (Auth::user()->hasPerm('tab_Play'))
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <x-nav-link :href="route('play.home')" :active="request()->routeIs('play.*')">
                            {{ __('play.route') }}
                        </x-nav-link>
                    </div>
                @endif
                @if (Auth::user()->hasPerm('tab_Manage'))
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <x-nav-link :href="route('manage.home')" :active="request()->routeIs('manage.*')">
                            {{ __('manage.route') }}
                        </x-nav-link>
                    </div>
                @endif
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button onclick="$(this).children().eq(1).children().toggleClass('rotate-180')"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <i class="fas fa-chevron-up mx-3 transform duration-500 rotate-180"
                                    style="font-size:10px;"></i>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">

                        <a class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out"
                            href="https://www.taide.tw/" target="_blank">{{ __('Information') }}</a>

                        @if (\App\Models\SystemSetting::where('key', 'announcement')->first()->value != '')
                            <button
                                class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out"
                                onclick="$modal1 = new Modal(document.getElementById('system_announcement_modal'), {backdrop: 'static',closable: true,onHide: () => {}}); $modal1.show();">{{ __('Announcement') }}</button>
                        @endif
                        @if (\App\Models\SystemSetting::where('key', 'tos')->first()->value != '')
                            <button
                                class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out"
                                onclick="$modal2 = new Modal(document.getElementById('tos_modal'), {backdrop: 'static',closable: true,onHide: () => {}}); $modal2.show();">{{ __('Terms of Service') }}</button>
                        @endif
                        <hr class="border-gray-300 dark:border-gray-600">
                        <!-- Authentication -->
                        @if (Auth::user()->hasPerm('tab_Profile'))
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('profile.route') }}
                            </x-dropdown-link>
                        @endif
                        <x-dropdown-link :href="route('lang')">
                            {{ __('Change Language') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Sign out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if (Auth::user()->hasPerm('tab_Dashboard'))
                <x-responsive-nav-link :href="route('dashboard.home')" :active="request()->routeIs('dashboard.*')">
                    {{ __('dashboard.route') }}
                </x-responsive-nav-link>
            @endif
            @if (Auth::user()->hasPerm('tab_Room'))
                <x-responsive-nav-link :href="route('room.home')" :active="request()->routeIs('room.*')">
                    {{ __('room.route') }}
                </x-responsive-nav-link>
            @endif
            @if (Auth::user()->hasPerm('tab_Chat'))
                <x-responsive-nav-link :href="route('chat.home')" :active="request()->routeIs('chat.*')">
                    {{ __('chat.route') }}
                </x-responsive-nav-link>
            @endif
            @if (Auth::user()->hasPerm('tab_Archive'))
                <x-responsive-nav-link :href="route('archive.home')" :active="request()->routeIs('archive.*')">
                    {{ __('archive.route') }}
                </x-responsive-nav-link>
            @endif
            @if (Auth::user()->hasPerm('tab_Play'))
                <x-responsive-nav-link :href="route('play.home')" :active="request()->routeIs('play.*')">
                    {{ __('play.route') }}
                </x-responsive-nav-link>
            @endif
            @if (Auth::user()->hasPerm('tab_Manage'))
                <x-responsive-nav-link :href="route('manage.home')" :active="request()->routeIs('manage.*')">
                    {{ __('manage.route') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                @if (Auth::user()->hasPerm('tab_Profile'))
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('profile.route') }}
                    </x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('lang')">
                    {{ __('Change Language') }}
                </x-responsive-nav-link>
                <a class="block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out"
                    href="https://taide.tw/" target="_blank">{{ __('Information') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Sign out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

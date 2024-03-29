<x-app-layout>
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('play.interface.header') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('play.label.intro') }}
                            </p>
                        </header>
                        <div class="mt-3 mx-auto flex">
                            {{__('play.hint.no_games')}}
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

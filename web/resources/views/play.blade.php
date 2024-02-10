<x-app-layout>
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Play Ground') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __("There're our experiment games with LLMs") }}
                            </p>
                        </header>
                        <div class="mt-3 mx-auto flex">
                            @if (App\Models\SystemSetting::where('key', 'ai_election_enabled')->first()->value == 'true')
                                <a class="text-blue-400 hover:text-blue-500"
                                    href="{{ route('play.ai_elections.home') }}">AI Election</a>
                            @endif
                            @if (request()->user()->hasPerm('tab_Room'))
                                <a class="text-blue-400 hover:text-blue-500 mr-3"
                                    href="{{ route('play.bots.home') }}">Bots</a>
                            @endif
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

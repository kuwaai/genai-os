<div class="flex flex-1 h-full mx-auto flex-col scrollbar overflow-y-auto">
    <form method="post" action="{{ route('manage.setting.update') }}"
        class="space-y-6 flex-1 m-4 border-2 border-gray-500 rounded p-2">
        <header class="flex">
            @csrf
            @method('patch')
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('System Settings') }}

                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('All the system settings here') }}
                </p>
            </div>
            <button type="submit" class="px-4 py-2 rounded bg-green-500 hover:bg-green-700 ml-auto text-white"><i
                    class="fas fa-save"></i></button>
        </header>

        <div class="max-w-xl">
            <label class="relative inline-flex items-center mr-5 cursor-pointer">
                <input type="checkbox" value="allow" name="allow_register" class="sr-only peer"
                    {{ \App\Models\SystemSetting::where('key', 'allowRegister')->first()->value == 'true' ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600">
                </div>
                <span
                    class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('Allow Register') }}</span>
            </label>
            <label class="relative inline-flex items-center mr-5 cursor-pointer">
                <input type="checkbox" value="allow" name="register_need_invite" class="sr-only peer"
                    {{ \App\Models\SystemSetting::where('key', 'register_need_invite')->first()->value == 'true' ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600">
                </div>
                <span
                    class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('Register Need Invite') }}</span>
            </label>
            <div>
                <x-input-label for="agent_location" :value="__('Agent API Location')" />
                <div class="flex items-center">
                    <x-text-input id="agent_location" name="agent_location" type="text"
                        class="mr-2 mb-1 block w-full"
                        value="{{ \App\Models\SystemSetting::where('key', 'agent_location')->first()->value }}" required
                        autocomplete="no" />
                </div>
            </div>

            <div>
                <x-input-label for="announcement" :value="__('System Announcement')" />
                <div class="flex items-center">
                    <textarea id="announcement" name="announcement" type="text" oninput="adjustTextareaRows(this)" rows="1"
                        max-rows="5"
                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mr-2 mb-1 block w-full resize-none">{{ \App\Models\SystemSetting::where('key', 'announcement')->first()->value }}</textarea>
                </div>
            </div>

            <div>
                <x-input-label for="tos" :value="__('Terms of Service')" />
                <div class="flex items-center">
                    <textarea id="tos" name="tos" type="text" oninput="adjustTextareaRows(this)" rows="1" max-rows="5"
                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mr-2 mb-1 block w-full resize-none">{{ \App\Models\SystemSetting::where('key', 'tos')->first()->value }}</textarea>
                </div>
            </div>
            <div>
                <x-input-label for="warning_footer" :value="__('Footer Warning')" />
                <div class="flex items-center">
                    <x-text-input id="warning_footer" name="warning_footer" type="text"
                        class="mr-2 mb-1 block w-full"
                        value="{{ \App\Models\SystemSetting::where('key', 'warning_footer')->first()->value }}" autocomplete="no" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                @if (session('last_action') === 'update' && session('status') === 'success')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-green-400">
                        {{ __('Saved.') }}</p>
                @elseif (session('last_action') === 'update' && session('status') === 'smtp_not_configured')
                    <p x-data="{ show: true }" x-show="show" class="text-sm text-red-600 dark:text-red-400">
                        {{ __("Failed to allow registering, SMTP haven't been configured!") }}</p>
                    <p x-data="{ show: true }" x-show="show" class="text-sm text-gray-600 dark:text-green-400">
                        {{ __('The rest of setting are saved.') }}</p>
                @endif
            </div>

            <div class="my-2"><a
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center"
                    href="{{ route('manage.setting.resetRedis') }}">{{ __('Reset Redis Caches') }}</a></div>
            @if (session('last_action') === 'resetRedis' && session('status') === 'success')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-green-400">
                    {{ __('Redis Cache Cleared.') }}</p>
            @endif
        </div>
    </form>
    <div id="ai_election_modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button"
                    class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                    data-modal-hide="ai_election_modal">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="px-6 py-6 ml:px-8">
                    <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">
                        {{ __('AI Election Configure') }}</h3>

                    <form method="post" enctype="multipart/form-data" autocomplete="off"
                        action="{{ route('play.ai_elections.update') }}" class="w-full max-w-xl">
                        @csrf
                        @method('patch')
                        <label class="relative inline-flex items-center mr-5 cursor-pointer">
                            <input type="checkbox" value="allow" name="ai_election_enabled" class="sr-only peer"
                                {{ \App\Models\SystemSetting::where('key', 'ai_election_enabled')->first()->value == 'true' ? 'checked' : '' }}>
                            <div
                                class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-600 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600">
                            </div>
                            <span
                                class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('Enable the game') }}</span>
                        </label>
                        <div class="text-center">
                            <button type="submit"
                                class="bg-green-500 hover:bg-green-600 text-white focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="space-y-6 flex-1 m-4 border-2 border-gray-500 rounded p-2">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Playground') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('You can configure games in this area') }}
            </p>
        </header>
        <div class="mt-3 mx-auto flex">
            <button data-modal-target="ai_election_modal" data-modal-toggle="ai_election_modal"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center">{{ __('AI Election') . __('[WIP]') }}</button>
        </div>
        <div class="flex items-center gap-4">
            @if (session('status') === 'play_setting_saved')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-green-400">
                    {{ __('Saved.') }}</p>
            @endif
        </div>
    </div>
</div>

<script>
    function adjustTextareaRows(obj) {
        obj = $(obj)
        if (obj.length) {
            const textarea = obj;
            const maxRows = parseInt(textarea.attr('max-rows')) || 5;
            const lineHeight = parseInt(textarea.css('line-height'));

            textarea.attr('rows', 1);

            const contentHeight = textarea[0].scrollHeight;
            const rowsToDisplay = Math.floor(contentHeight / lineHeight);

            textarea.attr('rows', Math.min(maxRows, rowsToDisplay));
        }
    }
    adjustTextareaRows($("#announcement"))
    adjustTextareaRows($("#tos"))
</script>

<div class="flex flex-1 h-full mx-auto flex-col scrollbar overflow-y-auto">
    <form method="post" action="{{ route('manage.setting.update') }}"
        class="space-y-6 flex-1 m-4 border-2 border-gray-500 rounded p-2">
        <header class="flex">
            @csrf
            @method('patch')
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('manage.header.settings') }}

                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('manage.label.settings') }}
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
                    class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('manage.label.allow_register') }}</span>
            </label>
            <label class="relative inline-flex items-center mr-5 cursor-pointer">
                <input type="checkbox" value="allow" name="register_need_invite" class="sr-only peer"
                    {{ \App\Models\SystemSetting::where('key', 'register_need_invite')->first()->value == 'true' ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600">
                </div>
                <span
                    class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('manage.label.need_invite') }}</span>
            </label>
            <div>
                <x-input-label for="agent_location" :value="__('manage.label.agent_API')" />
                <div class="flex items-center">
                    <x-text-input id="agent_location" name="agent_location" type="text"
                        class="mr-2 mb-1 block w-full"
                        value="{{ \App\Models\SystemSetting::where('key', 'agent_location')->first()->value }}" required
                        autocomplete="off" />
                </div>
            </div>
            <div>
                <x-input-label for="safety_guard_location" :value="__('manage.label.safety_guard_API')" />
                <div class="flex items-center">
                    <x-text-input id="safety_guard_location" name="safety_guard_location" type="text"
                        class="mr-2 mb-1 block w-full"
                        value="{{ \App\Models\SystemSetting::where('key', 'safety_guard_location')->first()->value }}"
                        autocomplete="off" />
                </div>
            </div>
            <div>
                <x-input-label for="announcement" :value="__('manage.label.anno')" />
                <div class="flex items-center">
                    <textarea id="announcement" name="announcement" type="text" oninput="adjustTextareaRows(this)" rows="1"
                        max-rows="5"
                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mr-2 mb-1 block w-full resize-none scrollbar">{{ \App\Models\SystemSetting::where('key', 'announcement')->first()->value }}</textarea>
                </div>
            </div>

            <div>
                <x-input-label for="tos" :value="__('manage.label.tos')" />
                <div class="flex items-center">
                    <textarea id="tos" name="tos" type="text" oninput="adjustTextareaRows(this)" rows="1" max-rows="5"
                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mr-2 mb-1 block w-full resize-none scrollbar">{{ \App\Models\SystemSetting::where('key', 'tos')->first()->value }}</textarea>
                </div>
            </div>
            <div>
                <x-input-label for="warning_footer" :value="__('manage.label.footer_warning')" />
                <div class="flex items-center">
                    <x-text-input id="warning_footer" name="warning_footer" type="text"
                        class="mr-2 mb-1 block w-full"
                        value="{{ \App\Models\SystemSetting::where('key', 'warning_footer')->first()->value }}" autocomplete="off" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                @if (session('last_action') === 'update' && session('status') === 'success')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-green-400">
                        {{ __('manage.hint.saved') }}</p>
                @elseif (session('last_action') === 'update' && session('status') === 'smtp_not_configured')
                    <p x-data="{ show: true }" x-show="show" class="text-sm text-red-600 dark:text-red-400">
                        {{ __("Failed to allow registering, SMTP haven't been configured!") }}</p>
                    <p x-data="{ show: true }" x-show="show" class="text-sm text-gray-600 dark:text-green-400">
                        {{ __('The rest of setting are saved.') }}</p>
                @endif
            </div>

            <div class="my-2"><a
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center"
                    href="{{ route('manage.setting.resetRedis') }}">{{ __('manage.button.reset_redis') }}</a></div>
            @if (session('last_action') === 'resetRedis' && session('status') === 'success')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-green-400">
                    {{ __('manage.hint.redis_cache_cleared') }}</p>
            @endif
        </div>
    </form>
    
    <!--<div class="space-y-6 flex-1 m-4 border-2 border-gray-500 rounded p-2">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('play.route') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('You can configure games in this area') }}
            </p>
        </header>
        <div class="mt-3 mx-auto flex">

        </div>
        <div class="flex items-center gap-4">
        </div>
    </div>-->
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

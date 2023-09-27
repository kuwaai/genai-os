<div class="flex flex-1 h-full mx-auto">
    <form method="post" action="{{ route('manage.setting.update') }}" class="space-y-6 flex-1 m-4">
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
                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Allow
                    Register</span>
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

            <div class="flex items-center gap-4">
                @if (session('last_action') === "update" && session('status') === 'success')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-green-400">
                        {{ __('Saved.') }}</p>
                @elseif (session('last_action') === "update" && session('status') === 'smtp_not_configured')
                    <p x-data="{ show: true }" x-show="show" class="text-sm text-red-600 dark:text-red-400">
                        {{ __("Failed to allow registering, SMTP haven't been configured!") }}</p>
                    <p x-data="{ show: true }" x-show="show" class="text-sm text-gray-600 dark:text-green-400">
                        {{ __('The rest of setting are saved.') }}</p>
                @endif
            </div>
            <div class="mt-2"><a
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center"
                href="{{ route('manage.setting.resetRedis') }}">Reset Redis Caches</a></div>
        @if (session('last_action') === "resetRedis" && session('status') === 'success')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600 dark:text-green-400">
                {{ __('Redis Cache Cleared.') }}</p>
        @endif
        </div>
    </form>
</div>

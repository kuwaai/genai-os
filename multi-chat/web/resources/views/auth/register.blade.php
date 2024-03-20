<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('login.label.name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
                autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('login.label.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('login.label.password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('login.label.confirm_password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            @if (App\Models\SystemSetting::where('key', 'register_need_invite')->where('value', 'true')->exists())
                <x-input-label for="invite_token" :value="__('auth.label.invite_token')" />
                <x-text-input id="invite_token" class="block mt-1 w-full" type="text" name="invite_token"
                    autocomplete="off" required />
            @else
                <x-input-label for="invite_token" :value="__('auth.label.invite_token_optional')" />
                <x-text-input id="invite_token" class="block mt-1 w-full" type="text" name="invite_token"
                    autocomplete="off" />
            @endif

            <x-input-error :messages="$errors->get('invite_token')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                href="{{ route('login') }}">
                {{ __('login.button.already_registered') }}
            </a>

            <x-primary-button class="ml-4">
                {{ __('login.button.register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

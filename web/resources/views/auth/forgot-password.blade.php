<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('login.forgot_password.label') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('login.label.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="dark:text-white text-black dark:hover:text-gray-300 hover:text-gray-700 mr-auto" href="/login">{{ __('login.button.return') }}</a>
            <x-primary-button>
                {{ __('login.button.send_reset_password_link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

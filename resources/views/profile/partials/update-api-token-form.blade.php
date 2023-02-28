<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Personal API Token') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Manage your API Token, Please keep it secret!") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.api.renew') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Personal API Token')" />
            <x-text-input type="text" class="mt-1 block w-full" :value="$user->tokens()->where('name', 'API_Token')->first()->token" disabled/>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Renew') }}</x-primary-button>

            @if (session('status') === 'apiToken-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-green-400"
                >{{ __('Renewed.') }}</p>
            @endif
        </div>
    </form>
</section>

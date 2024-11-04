<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('profile.header.interface') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('profile.header.personal_info') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}" autocomplete="off">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" autocomplete="off">
        @csrf
        @method('patch')

        @if (Auth::user()->hasPerm('Profile_update_name'))
            <div>
                <x-input-label for="name" :value="__('profile.label.name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                    required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
        @endif
        @if (Auth::user()->hasPerm('Profile_update_email'))
            <div>
                <x-input-label for="email" :value="__('profile.label.email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                    required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                            {{ __('Your email address is unverified.') }}

                            <button form="send-verification"
                                class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                {{ __('auth.email.new_verify_link_send') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        @endif

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('profile.button.save') }}</x-primary-button>

                @if (session('status') === 'profile-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-green-400">{{ __('profile.placeholder.saved') }}</p>
                @elseif (session('status') === 'no-changes')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-red-400">
                        {{ __('Failed to update, Please make sure you have permission to do that.') }}</p>
                @endif
            </div>
    </form>
</section>

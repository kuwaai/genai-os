<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('profile.header.api_manage') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('profile.label.api_manage') }}
        </p>
    </header>
    @if (Auth::user()->hasPerm('Profile_read_api_token'))
        <form method="post" action="{{ route('profile.api.renew') }}" class="mt-6 space-y-6" autocomplete="off">
            @csrf
            @method('patch')

            <div>
                <x-input-label for="taide_api" :value="__('profile.label.taide_api')" />
                <x-text-input type="text" id="taide_api" class="mt-1 block w-full" :value="$user
                    ->tokens()
                    ->where('name', 'API_Token')
                    ->first()->token" readonly />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button id="copyButton">{{ __('profile.button.copy') }}</x-primary-button>
                @if (Auth::user()->hasPerm('Profile_update_api_token'))
                    <x-primary-button>{{ __('profile.button.renew') }}</x-primary-button>
                    @if (session('status') === 'apiToken-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm text-gray-600 dark:text-green-400">{{ __('Renewed.') }}</p>
                    @endif
                @endif
            </div>
        </form>
    @endif


    @if (Auth::user()->hasPerm('Profile_update_openai_token'))
        <form method="post" action="{{ route('profile.chatgpt.api.update') }}" class="mt-6 space-y-6"
            autocomplete="off">
            @csrf
            @method('patch')

            <div>
                <x-input-label for="openai_token" :value="__('profile.label.openai_api')" />
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('profile.label.stay_secure') }}
                </p>
                <x-text-input type="password" id="openai_token" name="openai_token" class="mt-1 block w-full"
                    placeholder="{{ $user->openai_token ? '************************' : '' }}" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('profile.button.update') }}</x-primary-button>

                @if (session('status') === 'chatgpt-token-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-green-400">{{ __('profile.hint.updated') }}</p>
                @endif
            </div>
        </form>
    @endif
    @if (Auth::user()->hasPerm('Profile_read_api_token'))
        <script>
            $(document).ready(function() {
                $("#copyButton").click(function() {
                    event.preventDefault();
                    var copyText = document.getElementById("taide_api");
                    copyText.select();
                    document.execCommand("copy");

                    $(this).text("Copied!").addClass(
                        "bg-green-500 dark:bg-green-500 focus:bg-green-600 dark:focus:bg-green-600 hover:bg-green-600 dark:hover:bg-green-600"
                    );;

                    setTimeout(function() {
                        $("#copyButton").text("Copy").removeClass(
                            "bg-green-500 dark:bg-green-500 focus:bg-green-600 dark:focus:bg-green-600 hover:bg-green-600 dark:hover:bg-green-600"
                        );
                    }, 2000);
                });
            });
        </script>
    @endif
</section>

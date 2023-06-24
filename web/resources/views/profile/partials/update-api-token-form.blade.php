<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('API Token Management') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Manage your API Tokens, Please keep it secret!') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.api.renew') }}" class="mt-6 space-y-6" autocomplete="off">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="taide_api" :value="__('TAIDE Chat API Token')" />
            <x-text-input type="text" id="taide_api" class="mt-1 block w-full" :value="$user
                ->tokens()
                ->where('name', 'API_Token')
                ->first()->token" readonly />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button id="copyButton">{{ __('Copy') }}</x-primary-button>
            <x-primary-button>{{ __('Renew') }}</x-primary-button>

            @if (session('status') === 'apiToken-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-green-400">{{ __('Renewed.') }}</p>
            @endif
        </div>
    </form>


    <form method="post" action="{{ route('profile.chatgpt.api.update') }}" class="mt-6 space-y-6" autocomplete="off">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="openai_token" :value="__('OpenAI API Token')" />
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please aware that each message might cause the usage up to 2000 tokens') }}
            </p>
            <x-text-input type="password" id="openai_token" name="openai_token" class="mt-1 block w-full" :value="$user->openai_token" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Update') }}</x-primary-button>

            @if (session('status') === 'chatgpt-token-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-green-400">{{ __('Updated.') }}</p>
            @endif
        </div>
    </form>
    <script>
        $(document).ready(function() {
            $("#copyButton").click(function() {
                event.preventDefault();
                var copyText = document.getElementById("taide_api");
                copyText.select();
                document.execCommand("copy");

                $(this).text("Copied!").addClass("bg-green-500 dark:bg-green-500 focus:bg-green-600 dark:focus:bg-green-600 hover:bg-green-600 dark:hover:bg-green-600");;

                setTimeout(function() {
                    $("#copyButton").text("Copy").removeClass("bg-green-500 dark:bg-green-500 focus:bg-green-600 dark:focus:bg-green-600 hover:bg-green-600 dark:hover:bg-green-600");
                }, 2000);
            });
        });
    </script>
</section>

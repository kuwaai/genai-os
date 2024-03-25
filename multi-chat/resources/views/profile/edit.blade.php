<x-app-layout>
    <div class="flex-1 h-full py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (Auth::user()->hasPerm('Profile_update_email') || Auth::user()->hasPerm('Profile_update_name'))
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            @endif
            @if (Auth::user()->hasPerm('Profile_update_password'))
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
            @endif

            @if (Auth::user()->hasPerm('Profile_read_api_token') || Auth::user()->hasPerm('Profile_update_openai_token') || Auth::user()->hasPerm('Profile_update_api_token'))
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-api-token-form')
                </div>
            </div>
            @endif

            @if (Auth::user()->hasPerm('Profile_delete_account'))
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

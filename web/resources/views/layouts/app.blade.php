<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-hidden h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.css" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.socket.io/4.6.0/socket.io.min.js"
        integrity="sha384-c79GN5VsunZvi+Q/WObgk2in0CbZsHnjEqvFxC5DxHn9lTfNce2WW6h2pH6u/kF+" crossorigin="anonymous">
    </script>
</head>

<body class="font-sans antialiased h-full">
    @if (
        \App\Models\SystemSetting::where('key', 'announcement')->first()->value != '' &&
            hash('sha256', \App\Models\SystemSetting::where('key', 'announcement')->first()->value) !== session()->get('announcement'))
        <div data-modal-target="system_announcement_modal"></div>
        <div id="system_announcement_modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
            class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ __('System Announcement') }}
                        </h3>
                        <button type="button" onclick="$modal1.hide();"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="system_announcement_modal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-4">
                        @foreach (explode("\n", \App\Models\SystemSetting::where('key', 'announcement')->first()->value) as $paragraph)
                            <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                                {{ trim($paragraph) ?? "&nbsp;" }}
                            </p>
                        @endforeach
                    </div>
                    <!-- Modal footer -->
                    <div
                        class="flex items-center p-4 space-x-2 border-t border-gray-200 rounded-b dark:border-gray-600">
                        <button data-modal-hide="system_announcement_modal" type="button" onclick="$modal1.hide();"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (!Auth::user()->term_accepted)
        <div data-modal-target="tos_modal"></div>
        <div id="tos_modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
            class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ __('Terms of Service') }}
                        </h3>
                    </div>
                    <!-- Modal body -->
                    <div class="p-4">
                        @foreach (explode("\n", \App\Models\SystemSetting::where('key', 'tos')->first()->value) as $paragraph)
                            <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                                {{ trim($paragraph) == "" ? "　" : trim($paragraph) }}
                            </p>
                        @endforeach
                    </div>
                    <!-- Modal footer -->
                    <div
                        class="flex items-center p-4 space-x-2 border-t border-gray-200 rounded-b dark:border-gray-600">
                        <button data-modal-hide="tos_modal" type="button" onclick="$modal2.hide();"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">{{ __('I accepted') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="flex flex-col h-full bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif
        <!-- Page Content -->
        <main
            class="flex-1 overflow-y-{{ request()->routeIs('dashboard.*') || request()->routeIs('profile.edit') ? 'auto' : 'hidden' }} scrollbar">
            {{ $slot }}
        </main>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
    <script>
        @if (\App\Models\SystemSetting::where('key', 'tos')->first()->value != '' && !Auth::user()->term_accepted)
            $modal1 = new Modal(document.getElementById('system_announcement_modal'), {
                backdrop: 'static',
                closable: true,
                onHide: () => {
                    $.get("{{ route('announcement') }}")
                }
            });
            $modal2 = new Modal(document.getElementById('tos_modal'), {
                backdrop: 'static',
                closable: true,
                onHide: () => {
                    $.get("{{ route('tos') }}")
                    @if (
                        \App\Models\SystemSetting::where('key', 'announcement')->first()->value != '' &&
                            hash('sha256', \App\Models\SystemSetting::where('key', 'announcement')->first()->value) !== session()->get('announcement'))
                        $modal1.show();
                    @endif
                }
            });
            $modal2.show();
        @elseif (
            \App\Models\SystemSetting::where('key', 'announcement')->first()->value != '' &&
                hash('sha256', \App\Models\SystemSetting::where('key', 'announcement')->first()->value) !== session()->get('announcement'))
            $modal1 = new Modal(document.getElementById('system_announcement_modal'), {
                backdrop: 'static',
                closable: true,
                onHide: () => {
                    $.get("{{ route('announcement') }}")
                }
            });
            $modal1.show();
        @endif
    </script>
</body>

</html>

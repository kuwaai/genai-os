<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-hidden h-full">
@php
    $languages = config('app.LANGUAGES');
@endphp

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="{{ asset('css/flowbite.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/fontBunny.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/font_awesome..all.min.css') }}" />
    <link href="{{ asset('css/highlight_default.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/dracula.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/jquery-ui.css') }}" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/kuwa_api.js') }}"></script>
    <script src="{{ asset('js/flowbite.min.js') }}"></script>
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/marked.min.js') }}"></script>
    <script src="{{ asset('js/highlight.min.js') }}"></script>
    <script src="{{ asset('js/purify.min.js') }}"></script>
    <script src="{{ asset('js/ace/ace.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>

</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen h-full scrollbar overflow-y-auto p-4">
    <div class="w-full max-w-2xl mx-auto p-6 flex items-center justify-center h-full">
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8">
            <x-Logo class="mb-4" />
            <h1 class="text-3xl font-semibold text-center mb-6">Kuwa GenAI OS<br>Quick Configuration Setup</h1>

            <div id="step-1" class="step">
                <h2 class="text-xl font-semibold mb-4">Step 1: Select Bots to install</h2>
                <form>
                    @foreach (['Chiness to English', 'DocQA', 'Painter'] as $package)
                        <div class="flex items-center mb-4">
                            <input id="package-{{ $loop->index }}" type="checkbox" value="{{ $package }}" class="beautiful-checkbox" />
                            <label for="package-{{ $loop->index }}" class="ml-3 cursor-pointer text-gray-700 dark:text-gray-300">{{ $package }}</label>
                        </div>
                    @endforeach
                    <button type="button" class="next-step bg-blue-600 text-white py-2 px-4 rounded mt-4 w-full hover:bg-blue-700 transition">Next</button>
                </form>
            </div>

            <div id="step-2" class="step hidden">
                <h2 class="text-xl font-semibold mb-4">Step 2: Install Models</h2>
                <form id="step-2-form">
                    @foreach (['TAIDE', 'Meta LLaMA 3', 'Claude'] as $model)
                        <div class="flex items-center mb-4">
                            <input id="model-{{ $loop->index }}" type="checkbox" value="{{ $model }}" class="model-checkbox beautiful-checkbox" />
                            <label for="model-{{ $loop->index }}" class="ml-3 cursor-pointer text-gray-700 dark:text-gray-300">{{ $model }}</label>
                        </div>
                    @endforeach
                    <button type="button" class="prev-step bg-gray-400 text-white py-2 px-4 rounded mt-4 w-full hover:bg-gray-500 transition">Back</button>
                    <button type="button" class="next-step bg-blue-600 text-white py-2 px-4 rounded mt-4 w-full hover:bg-blue-700 transition">Next</button>
                </form>
            </div>

            <div id="step-3" class="step hidden">
                <h2 class="text-xl font-semibold mb-4">Step 3: Select Models to Launch by Default</h2>
                <form id="step-3-form">
                    <!-- Models selected in Step 2 will be populated here -->
                </form>
                <button type="button" class="prev-step bg-gray-400 text-white py-2 px-4 rounded mt-4 w-full hover:bg-gray-500 transition">Back</button>
                <button type="button" class="show-summary bg-blue-600 text-white py-2 px-4 rounded mt-4 w-full hover:bg-blue-700 transition">Next</button>
            </div>

            <div id="summary" class="step hidden">
                <h2 class="text-xl font-semibold mb-4">Summary of Your Selections</h2>
                <div id="summary-list" class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow-inner mb-4">
                    <!-- Summary details will be populated here -->
                </div>
                <button type="button" class="prev-step bg-gray-400 text-white py-2 px-4 rounded mt-4 w-full hover:bg-gray-500 transition">Back</button>
                <button type="button" class="confirm bg-green-600 text-white py-2 px-4 rounded mt-4 w-full hover:bg-green-700 transition">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let currentStep = 1;

            function showStep(step) {
                $('.step').addClass('hidden');
                $('#step-' + step).removeClass('hidden');
            }

            $('.next-step').on('click', function() {
                if (currentStep === 1) {
                    // Step 1 to Step 2
                    currentStep++;
                } else if (currentStep === 2) {
                    const selectedModels = $('input.model-checkbox:checked').map(function() {
                        return $(this).val();
                    }).get();

                    const step3Form = $('#step-3-form');
                    step3Form.empty();
                    if (selectedModels.length > 0) {
                        selectedModels.forEach((model, index) => {
                            step3Form.append(`
                                <div class="flex items-center mb-4">
                                    <input id="launch-${index}" type="checkbox" value="${model}" class="beautiful-checkbox" />
                                    <label for="launch-${index}" class="ml-3 cursor-pointer text-gray-700 dark:text-gray-300">${model}</label>
                                </div>
                            `);
                        });
                    } else {
                        step3Form.append('<p class="text-gray-500 dark:text-gray-400">No models selected in the previous step.</p>');
                    }
                    currentStep++;
                } else if (currentStep === 3) {
                    // Step 2 to Step 3
                    currentStep++;
                }
                showStep(currentStep);
            });

            $('.prev-step').on('click', function() {
                currentStep--;
                showStep(currentStep);
            });

            $('.show-summary').on('click', function() {
                const summaryList = $('#summary-list');
                summaryList.empty();

                summaryList.append('<h3 class="font-semibold mb-2">Installable Packages:</h3>');
                summaryList.append('<ul class="mb-4">');
                $('input[type=checkbox]:checked').each(function() {
                    if ($(this).attr('id').startsWith('package-')) {
                        summaryList.append('<li class="ml-4">- ' + $(this).val() + '</li>');
                    }
                });
                summaryList.append('</ul>');

                summaryList.append('<h3 class="font-semibold mb-2">Installed Models:</h3>');
                summaryList.append('<ul class="mb-4">');
                $('input[type=checkbox]:checked').each(function() {
                    if ($(this).attr('id').startsWith('model-')) {
                        summaryList.append('<li class="ml-4">- ' + $(this).val() + '</li>');
                    }
                });
                summaryList.append('</ul>');

                summaryList.append('<h3 class="font-semibold mb-2">Models to Launch:</h3>');
                summaryList.append('<ul>');
                $('input[type=checkbox]:checked').each(function() {
                    if ($(this).attr('id').startsWith('launch-')) {
                        summaryList.append('<li class="ml-4">- ' + $(this).val() + '</li>');
                    }
                });
                summaryList.append('</ul>');

                $('.step').addClass('hidden');
                $('#summary').removeClass('hidden');
            });

            $('.confirm').on('click', function() {
                alert('Configuration confirmed!');
            });
        });
    </script>

    <style>
        /* Custom beautiful checkbox styles */
        .beautiful-checkbox {
            appearance: none;
            background-color: #fff;
            border: 2px solid #d1d5db;
            border-radius: 0.25rem;
            width: 1.25rem;
            height: 1.25rem;
            transition: background-color 0.2s, border-color 0.2s;
            cursor: pointer;
        }

        .beautiful-checkbox:checked {
            background-color: #3b82f6; /* Blue-500 */
            border-color: #3b82f6;
        }

        .beautiful-checkbox:checked::before {
            content: '✓';
            color: #fff;
            font-weight: bold;
            display: block;
            text-align: center;
            line-height: 1.25rem;
        }

        .beautiful-checkbox:hover {
            border-color: #3b82f6; /* Blue-500 */
        }
    </style>
</body>

</html>

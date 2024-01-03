<div class="bg-gray-600 w-full flex">
    <div method="post" action="{{ route('dashboard.feedback') }}" class="w-full flex flex-col">
        @csrf
        <div class="flex-1 flex overflow-hidden">
            <form class="flex-1 flex flex-col overflow-hidden" method="post" action="{{ route('dashboard.feedback') }}"
                onsubmit="return validateForm()">
                @csrf
                <div class="flex-1 px-4 py-5 overflow-y-auto scrollbar">
                    <p class="text-center">{{ __('dashboard.header.ExportSetting') }}</p>
                    <div class="mb-2">
                        <p class="text-red-500">WIP, No option available</p>
                        <!--
                        <input id="feedback" name="feedback_only" type="checkbox" disabled>
                        <label for="feedback">Include Feedback Datas</label>-->
                    </div>
                    <p class="text-center">{{ __('dashboard.header.ModelFilter') }}</p>
                    <div>
                        <p>{{ __('dashboard.header.ActiveModels') }}</p>
                        <hr />
                        <div
                            class="grid flex flex-1 mx-auto max-w-screen-xl text-gray-900 dark:text-white lg:grid-cols-2">
                            @foreach (App\Models\LLMs::orderby('order')->orderby('order')->where('enabled', '=', true)->get() as $LLM)
                                <div>
                                    <input id="model_{{ $LLM->id }}" name="models[]" value="{{ $LLM->id }}"
                                        type="checkbox">
                                    <label for="model_{{ $LLM->id }}">{{ $LLM->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p>{{ __('dashboard.header.InactiveModels') }}</p>
                        <hr />
                        <div
                            class="grid flex flex-1 mx-auto max-w-screen-xl text-gray-900 dark:text-white lg:grid-cols-2">
                            @foreach (App\Models\LLMs::orderby('order')->orderby('order')->where('enabled', '=', false)->get() as $LLM)
                                <div>
                                    <input id="model_{{ $LLM->id }}" name="models[]" value="{{ $LLM->id }}"
                                        type="checkbox">
                                    <label for="model_{{ $LLM->id }}">{{ $LLM->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div style="display:none;"
                    class="bg-red-100 border border-red-400 mt-2 text-red-700 px-4 py-3 rounded relative"
                    id="error_alert" role="alert">
                    <span class="block sm:inline"></span>
                </div>
                <div class="flex justify-center items-center my-1">
                    <button
                        class="bg-green-600 py-1 px-2 rounded-lg hover:bg-green-700">{{ __('dashboard.button.ExportAndDownload') }}</button>
                </div>
            </form>
            <form class="flex-1 flex flex-col" method="post" action="{{ route('dashboard.feedback') }}"
                onsubmit="return validateForm2()">
                @csrf
                <textarea id="feedback_rawdata" name="rawdata" placeholder="{{ __('dashboard.hint.PasteRawDataHere') }}"
                    class="border-gray-300 flex-1 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mr-2 block w-full resize-none">{{ session('rawdata') ?? '' }}</textarea>
                <div class="flex justify-center items-center my-1 space-x-2">
                    <label for="import_file_input"
                        class="bg-orange-600 hover:bg-orange-700 py-1 px-2 rounded-lg cursor-pointer text-white">{{ __('dashboard.button.LoadFile') }}</label>
                    <input id="import_file_input" type='file' hidden>
                    <button
                        class="bg-green-600 hover:bg-green-700 py-1 px-2 rounded-lg">{{ __('dashboard.button.ConvertAndDownload') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function validateForm() {
        const checkboxes = document.querySelectorAll('input[name="models[]"]:checked');
        if (checkboxes.length === 0) {
            $("#error_alert >span").text(
                "{{ __('dashboard.msg.MustHave1Model') }}"
            )
            $("#error_alert").fadeIn();
            setTimeout(function() {
                $("#error_alert").fadeOut();
            }, 3000);
            return false;
        }
        return true;
    }

    function validateForm2() {
        // Retrieve the textarea value
        const textAreaValue = $('#feedback_rawdata').val();

        // Validate if the textarea content is valid JSON
        try {
            JSON.parse(textAreaValue);
            // If valid JSON, return true to submit the form
            return true;
        } catch (error) {
            // If not valid JSON, show an error message
            $("#error_alert > span").text("{{ __('dashboard.msg.InvalidJSONFormat') }}");
            $("#error_alert").fadeIn();
            setTimeout(function() {
                $("#error_alert").fadeOut();
            }, 3000);
            // Return false to prevent form submission
            return false;
        }
    }
    $(document).ready(function() {
        // Handle the file input change event
        $('#import_file_input').on('change', function() {
            loadFile($(this)[0], '#feedback_rawdata')
        });
        $('#feedback_rawdata').on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            loadFile(e.originalEvent.dataTransfer, '#feedback_rawdata');
        });
    });

    function loadFile(fileInput, input) {
        const file = fileInput.files[0];
        if (file) {
            if (file.type === 'text/plain' || file.type === 'application/json') {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $(input).val(e.target.result);
                    adjustTextareaRows(input);
                };
                reader.readAsText(file);
            } else {
                alert('Only .txt or .json files are accepted.');
            }
        }
    }
        @if (session('status') == 'rawdata-error')
            $("#error_alert > span").text("{{ __('dashboard.msg.InvalidJSONFormat') }}");
            $("#error_alert").fadeIn();
            setTimeout(function() {
                $("#error_alert").fadeOut();
            }, 3000);
        @endif
</script>

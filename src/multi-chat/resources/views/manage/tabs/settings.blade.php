<div class="flex flex-1 h-full mx-auto flex-col scrollbar overflow-y-auto">
    <form class="setting-form" method="post" action="{{ route('manage.setting.update') }}"
        onsubmit="submitSettings(false);" class="space-y-6 flex-1 m-4 border-2 border-gray-500 rounded p-2">
        <header class="flex">
            @csrf
            @method('patch')
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('manage.header.settings') }}

                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('manage.label.settings') }}
                </p>
            </div>
            <button type="submit" class="px-4 py-2 rounded bg-green-500 hover:bg-green-700 ml-auto text-white"><i
                    class="fas fa-save"></i></button>
        </header>

        <div class="max-w-xl">
            <label class="relative inline-flex items-center mr-5 cursor-pointer">
                <input type="checkbox" value="allow" name="allow_register" class="sr-only peer"
                    {{ \App\Models\SystemSetting::where('key', 'allowRegister')->first()->value == 'true' ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600">
                </div>
                <span
                    class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('manage.label.allow_register') }}</span>
            </label>
            <label class="relative inline-flex items-center mr-5 cursor-pointer">
                <input type="checkbox" value="allow" name="register_need_invite" class="sr-only peer"
                    {{ \App\Models\SystemSetting::where('key', 'register_need_invite')->first()->value == 'true' ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600">
                </div>
                <span
                    class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('manage.label.need_invite') }}</span>
            </label>
            <div>
                <x-input-label for="agent_location" :value="__('manage.label.agent_API')" />
                <div class="flex items-center">
                    <x-text-input id="agent_location" name="agent_location" type="text"
                        class="mr-2 mb-1 block w-full"
                        value="{{ \App\Models\SystemSetting::where('key', 'agent_location')->first()->value }}" required
                        autocomplete="off" />
                </div>
            </div>
            <div>
                <x-input-label for="safety_guard_location" :value="__('manage.label.safety_guard_API')" />
                <div class="flex items-center">
                    <x-text-input id="safety_guard_location" name="safety_guard_location" type="text"
                        class="mr-2 mb-1 block w-full"
                        value="{{ \App\Models\SystemSetting::where('key', 'safety_guard_location')->first()->value }}"
                        autocomplete="off" />
                </div>
            </div>
            <div>
                <x-input-label for="announcement" :value="__('manage.label.anno')" />
                <div class="flex items-center">
                    <textarea id="announcement" name="announcement" type="text" oninput="adjustTextareaRows(this)" rows="1"
                        max-rows="5"
                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mr-2 mb-1 block w-full resize-none scrollbar">{{ \App\Models\SystemSetting::where('key', 'announcement')->first()->value }}</textarea>
                </div>
            </div>

            <div>
                <x-input-label for="tos" :value="__('manage.label.tos')" />
                <div class="flex items-center">
                    <textarea id="tos" name="tos" type="text" oninput="adjustTextareaRows(this)" rows="1" max-rows="5"
                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mr-2 mb-1 block w-full resize-none scrollbar">{{ \App\Models\SystemSetting::where('key', 'tos')->first()->value }}</textarea>
                </div>
            </div>
            <div>
                <x-input-label for="warning_footer" :value="__('manage.label.footer_warning')" />
                <div class="flex items-center">
                    <x-text-input id="warning_footer" name="warning_footer" type="text"
                        class="mr-2 mb-1 block w-full"
                        value="{{ \App\Models\SystemSetting::where('key', 'warning_footer')->first()->value }}"
                        autocomplete="off" />
                </div>
            </div>
            <div>
                <x-input-label for="upload_max_size_mb" :value="__('manage.label.upload_max_size_mb')" />
                <div class="flex items-center">
                    <x-text-input id="upload_max_size_mb" name="upload_max_size_mb" type="text"
                        class="mr-2 mb-1 block w-full"
                        value="{{ \App\Models\SystemSetting::where('key', 'upload_max_size_mb')->first()->value }}"
                        autocomplete="off" />
                </div>
            </div>
            <div>
                <x-input-label for="upload_allowed_extensions" :value="__('manage.label.upload_allowed_extensions')" />
                <div class="flex items-center">
                    <x-text-input id="upload_allowed_extensions" name="upload_allowed_extensions" type="text"
                        class="mr-2 mb-1 block w-full"
                        value="{{ \App\Models\SystemSetting::where('key', 'upload_allowed_extensions')->first()->value }}"
                        autocomplete="off" />
                </div>
            </div>
            <div>
                <x-input-label for="upload_max_file_count" :value="__('manage.label.upload_max_file_count')" />
                <div class="flex items-center">
                    @php
                        $upload_max_file_count = \App\Models\SystemSetting::where(
                            'key',
                            'upload_max_file_count',
                        )->first()->value;
                    @endphp
                    <x-text-input id="upload_max_file_count" name="upload_max_file_count" type="text"
                        class="mr-2 mb-1 block w-full" value="{{ $upload_max_file_count }}"
                        data-original-value="{{ $upload_max_file_count }}" autocomplete="off" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                @if (session('last_action') === 'update' && session('status') === 'success')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-green-400">
                        {{ __('manage.hint.saved') }}</p>
                @elseif (session('last_action') === 'update' && session('status') === 'smtp_not_configured')
                    <p x-data="{ show: true }" x-show="show" class="text-sm text-red-600 dark:text-red-400">
                        {{ __("Failed to allow registering, SMTP haven't been configured!") }}</p>
                    <p x-data="{ show: true }" x-show="show" class="text-sm text-gray-600 dark:text-green-400">
                        {{ __('The rest of setting are saved.') }}</p>
                @endif
            </div>

            <div class="my-2">
                <div id="updateWebBtn"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center cursor-pointer">{{ __('manage.button.updateWeb') }}</div>
            </div>
            <div class="my-2"><a
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center"
                    href="{{ route('manage.setting.resetRedis') }}">{{ __('manage.button.reset_redis') }}</a></div>
            @if (session('last_action') === 'resetRedis' && session('status') === 'success')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-green-400">
                    {{ __('manage.hint.redis_cache_cleared') }}</p>
            @endif
        </div>
    </form>
</div>

<div class="show-confirm-modal hide" data-modal-target="confirm-modal" data-modal-show="confirm-modal"></div>
<div id="confirm-modal" data-modal-backdrop="static" tabindex="-2"
    class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <button type="button"
                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                data-modal-hide="confirm-modal">
                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="p-6 text-center">
                <svg aria-hidden="true" class="mx-auto mb-4 text-gray-400 w-14 h-14 dark:text-gray-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="message mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                </h3>
                <button data-modal-hide="confirm-modal" type="button"
                    class="text-white bg-red-600 hover:bg-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2"
                    onclick="submitSettings(true);">
                    {{ __('manage.button.yes') }}
                </button>
                <!-- Modal for showing progress -->
                <div id="outputModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
                    <div class="flex items-center justify-center min-h-screen">
                        <div class="bg-white rounded-lg p-6 shadow-lg max-w-3xl w-full">
                            <h2 class="text-xl font-bold mb-4">Command Execution Progress</h2>
                            <pre id="commandOutput" class="bg-gray-100 p-4 rounded-lg text-sm h-96 overflow-auto"></pre>
                            <button id="closeModal" class="mt-4 bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600 focus:outline-none">Close</button>
                        </div>
                    </div>
                </div>
        
                <div id="status" class="mt-4"></div>
                <button data-modal-hide="confirm-modal" type="button"
                    class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                    {{ __('manage.button.no') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function adjustTextareaRows(obj) {
        obj = $(obj)
        if (obj.length) {
            const textarea = obj;
            const maxRows = parseInt(textarea.attr('max-rows')) || 5;
            const lineHeight = parseInt(textarea.css('line-height'));

            textarea.attr('rows', 1);

            const contentHeight = textarea[0].scrollHeight;
            const rowsToDisplay = Math.floor(contentHeight / lineHeight);

            textarea.attr('rows', Math.min(maxRows, rowsToDisplay));
        }
    }
    adjustTextareaRows($("#announcement"))
    adjustTextareaRows($("#tos"))

    function submitSettings(confirmed) {
        let form = $(".setting-form");
        // Truly submit the data.
        if (form.data("confirmed") || false) {
            return;
        }

        let confirm_needed = false;
        let messages = [];
        let cur_max_file_cnt = $("#upload_max_file_count").val();
        let orig_max_file_cnt = $("#upload_max_file_count").data("original-value");
        let parse_int = (x) => parseInt(x) || 0;
        let parse_max_file_cnt = (x) => parse_int(x) == -1 ? Number.MAX_SAFE_INTEGER : parse_int(x);
        if (parse_max_file_cnt(cur_max_file_cnt) < parse_max_file_cnt(orig_max_file_cnt)) {
            messages.push("{{ __('manage.modal.confirm_setting_modal.shrink_max_upload_file_count') }}");
            confirm_needed = true;
        }

        if (confirm_needed && !confirmed) {
            // Display the confirm modal
            event.preventDefault();
            $("#confirm-modal").find(".message").html(messages.join("<br>"));
            $(".show-confirm-modal").click();
        } else {
            // Submit the form again with confirmed attribute.
            form.data("confirmed", true);
            form.submit();
        }
    }
    $('#updateWebBtn').click(function() {
        $('#commandOutput').text(''); // Clear previous output
        $('#outputModal').removeClass('hidden'); // Show modal

        $.ajax({
            url: "{{ route('manage.setting.updateWeb') }}",
            type: 'GET',
            xhrFields: {
                onprogress: function(e) {
                    var newOutput = e.currentTarget.response;
                    $('#commandOutput').text(newOutput); // Update the modal with real-time output
                }
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#commandOutput').append('\nCompleted successfully!');
                } else {
                    $('#commandOutput').append('\nError: ' + response.message);
                }
            },
            error: function(xhr) {
                $('#commandOutput').append('\nAn error occurred while executing the commands.');
            }
        });
    });

    // Close the modal
    $('#closeModal').click(function() {
        $('#outputModal').addClass('hidden');
    });
</script>

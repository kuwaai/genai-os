@props([
    'chained' => true,
    'chatId' => request()->route('chat_id'),
    'disabled' => false,
])

<form method="post" action="{{ route('chat.request') }}" id="prompt_area">
    <div class="flex items-end justify-end {{ $disabled ? 'hidden' : '' }}">
        @csrf
        <input name="chat_id" value="{{ $chatId }}" style="display:none;">
        <input id="chained" style="display:none;" {{ $chained ? '' : 'disabled' }}>
        <button type="button" onclick="chain_toggle()" id="chain_btn"
            class="whitespace-nowrap my-auto text-white mr-3 {{ $chained ? 'bg-green-500 hover:bg-green-600' : 'bg-red-600 hover:bg-red-700' }} px-3 py-2 rounded">
            {{ $chained ? __('Chained') : __('Unchain') }}
        </button>
        <textarea tabindex="0" data-id="root" placeholder="{{ __('Send a message') }}" rows="1" max-rows="5"
            oninput="adjustTextareaRows(this)" id="chat_input" name="input" readonly
            class="w-full pl-4 pr-12 py-2 rounded text-black scrollbar dark:text-white placeholder-black dark:placeholder-white bg-gray-200 dark:bg-gray-600 border border-gray-300 focus:outline-none shadow-none border-none focus:ring-0 focus:border-transparent rounded-l-md resize-none"></textarea>
        <div class="ml-auto right-[12px] relative bottom-[4px] flex justify-end items-end">
            <button type="submit" id='submit_msg' style='display:none;'
                class="inline-flex items-center justify-center fixed w-[32px] bg-blue-600 h-[32px] rounded hover:bg-blue-500 dark:hover:bg-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none"
                    class="w-5 h-5 text-white dark:text-gray-300 icon-sm m-1 md:m-0">
                    <path
                        d="M.5 1.163A1 1 0 0 1 1.97.28l12.868 6.837a1 1 0 0 1 0 1.766L1.969 15.72A1 1 0 0 1 .5 14.836V10.33a1 1 0 0 1 .816-.983L8.5 8 1.316 6.653A1 1 0 0 1 .5 5.67V1.163Z"
                        fill="currentColor"></path>
                </svg>
            </button>
            <button id='abort_btn' style='display:none;' onclick="return abortGenerate()"
                class="text-white inline-flex items-center justify-center fixed w-[32px] bg-orange-600 h-[32px] rounded hover:bg-orange-500 dark:hover:bg-orange-700">
                <i class="far fa-stop-circle"></i></button>
        </div>

    </div>
    <p class="text-xs text-center mb-[-8px] mt-[8px] leading-3 dark:text-gray-200">
        {{ \App\Models\SystemSetting::where('key', 'warning_footer')->first()->value ?? '' }}</p>

</form>

<script>
    function chain_toggle() {
        $.get("{{ route('chat.chain') }}", {
            switch: $('#chained').prop('disabled')
        }, function() {
            $('#chained').prop('disabled', !$('#chained').prop('disabled'));
            $('#chain_btn').toggleClass('bg-green-500 hover:bg-green-600 bg-red-600 hover:bg-red-700');
            $('#chain_btn').text($('#chained').prop('disabled') ? '{{ __('Unchain') }}' :
                '{{ __('Chained') }}')
        })
    }

    function abortGenerate() {
        $.get("{{ route('chat.abort', $chatId) }}");
        return false;
    }

    function isValidURL(url) {
        var urlPattern = /^(https?|ftp):\/\/(-\.)?([^\s/?\.#-]+\.?)+([^\s]*)$/;
        return urlPattern.test(url);
    }
    @if (request()->route('llm_id'))
        @if (strpos(App\Models\LLMs::find(request()->route('llm_id'))->access_code, 'web_qa') === 0)
            if ($("#prompt_area")) {
                $("#prompt_area").on("submit", function(event) {
                    event.preventDefault();
                    if (isValidURL($("#chat_input").val().trim())) {
                        $("#prompt_area")[0].submit()
                    } else {
                        $("#error_alert >span").text(
                            "{{ __('The first message for this LLM allows URL only!') }}")
                        $("#error_alert").fadeIn();
                        setTimeout(function() {
                            $("#error_alert").fadeOut();
                        }, 3000);
                    }
                })
            }
        @endif
    @endif
</script>

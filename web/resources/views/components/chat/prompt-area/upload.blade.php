@props(['llmId' => request()->route('llm_id')])

@if (request()->user()->hasPerm('Chat_update_upload_file'))
    <form method="post" action="{{ route('chat.upload') }}" class="flex flex-col justify-center items-center" enctype="multipart/form-data">
        @csrf
        <input name="llm_id" style="display:none;" value="{{ $llmId }}">
        <input id="upload" type="file" name="file" style="display: none;" onchange="uploadcheck()">
        <label for="upload" id="upload_btn"
            class="bg-green-500 hover:bg-green-600 px-3 py-2 rounded cursor-pointer text-white">{{ __('chat.button.upload_file') }}</label>
        <p class="text-xs text-center mb-[-8px] mt-[8px] leading-3 dark:text-gray-200">
            {{ \App\Models\SystemSetting::where('key', 'warning_footer')->first()->value ?? '' }}</p>
    </form>
@else
    <p class="text-black dark:text-white mx-auto">
        {{ __("Sorry, but it seems like you don't have permission to upload a file.") }}
    </p>
@endif

<script>
    function uploadcheck() {
        if ($("#upload")[0].files && $("#upload")[0].files.length > 0 && $("#upload")[0].files[0].size <= 10 * 1024 *
            1024) {
            $("#upload").parent().submit();
        } else {
            $("#upload_btn").text('{{ __('chat.hint.file_too_large') }}')
            $("#upload_btn").toggleClass("bg-green-500 hover:bg-green-600 bg-red-600 hover:bg-red-700")
            $("#upload").val("");


            setTimeout(function() {
                $("#upload_btn").text('{{ __('chat.button.upload_file') }}')
                $("#upload_btn").toggleClass("bg-green-500 hover:bg-green-600 bg-red-600 hover:bg-red-700")
            }, 3000);
        }
    }
</script>

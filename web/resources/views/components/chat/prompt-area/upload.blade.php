@props(['llmId' => request()->route('llm_id')])

@if (request()->user()->hasPerm('Chat_update_upload_file'))
    <form method="post" action="{{ route('chat.upload') }}" class="m-auto" enctype="multipart/form-data">
        @csrf
        <input name="llm_id" style="display:none;" value="{{ $llmId }}">
        <input id="upload" type="file" name="file" style="display: none;" onchange="uploadcheck()">
        <label for="upload" id="upload_btn" class="bg-green-500 hover:bg-green-600 px-3 py-2 rounded cursor-pointer text-white">{{ __('Upload File') }}</label>
    </form>
@else
    <p class="text-black dark:text-white mx-auto">
        {{ __("Sorry, but it seems like you don't have permission to upload a file.") }}
    </p>
@endif
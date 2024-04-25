@props(['llms'])

<form method="post" action="{{ route('room.request') }}" id="prompt_area" enctype="multipart/form-data">
    @foreach ($llms as $llm)
        <input id="chatsTo_{{ $llm->id }}" name="chatsTo[]" value="{{ $llm->id }}" hidden>
    @endforeach
    <div id="attachment" class="text-xs mb-[8px] mt-[-8px] leading-3" style="display:none">
        <button onclick="event.preventDefault();$('#upload').val(''); $(this).parent().hide();"
            class="w-full h-full py-[8px] bg-blue-600 hover:bg-red-600 rounded-lg dark:text-gray-200 text-center">+
            filename</button>
    </div>
    <div class="flex overflow-hidden">
        @csrf
        <input name="room_id" value="{{ request()->route('room_id') }}" hidden>
        <input id="chained" {{ \Session::get('chained') ?? true ? '' : 'disabled' }} hidden>
        <input id="upload" type="file" name="file" style="display: none;" onchange="uploadcheck()">
        <div class="flex flex-1 items-end justify-end {{ count($llms) > 1 ? 'flex-col' : '' }} overflow-hidden">
            @if (count($llms) > 1)
                <div
                    class="flex flex-1 justify-center items-center w-full overflow-hidden dark:text-white mb-2 select-none">
                    <p>{{ __('chat.label.send_to') }}</p>
                    <div class="flex flex-1 items-center overflow-hidden">
                        <div class="flex flex-1 mr-auto overflow-auto scrollbar scrollbar-3">
                            @foreach ($llms as $llm)
                                <span
                                    @env('arena')  @else data-tooltip-target="llm_{{ $llm->id }}_toggle" data-tooltip-placement="top" @endenv
                                    id="btn_{{ $llm->id }}_toggle"
                                    onclick="$('#importTo_{{ $llm->id }}').prop('disabled',(i,val)=>{return !val});$('#chatsTo_{{ $llm->id }}').prop('disabled',(i,val)=>{return !val}); $(this).toggleClass('bg-green-500 hover:bg-green-600 bg-red-500 hover:bg-red-600')"
                                    class="cursor-pointer flex py-1 px-2 mx-1 bg-green-500 hover:bg-green-600 rounded-full">
                                    <div
                                        class="inline h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-black overflow-hidden">

                                        @env('arena')
                                        <div class="h-full w-full bg-black flex justify-center items-center text-white">
                                            ?</div>
                                    @else
                                        <img
                                            src="{{ $llm->image ? asset(Storage::url($llm->image)) : '/'. config('app.LLM_DEFAULT_IMG') }}">
                                        @endenv
                                    </div>
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <button type="button" onclick="chain_toggle()" id="chain_btn"
                        class="whitespace-nowrap ml-2 text-white {{ \Session::get('chained') ?? true ? 'bg-green-500 hover:bg-green-600' : 'bg-red-600 hover:bg-red-700' }} px-2 py-1 rounded">{{ \Session::get('chained') ?? true ? __('chat.button.chained') : __('chat.button.unchain') }}</button>
                </div>
            @else
                <button type="button" onclick="chain_toggle()" id="chain_btn"
                    class="whitespace-nowrap h-full mr-2 text-white {{ \Session::get('chained') ?? true ? 'bg-green-500 hover:bg-green-600' : 'bg-red-600 hover:bg-red-700' }} px-2 py-1 rounded">{{ \Session::get('chained') ?? true ? __('chat.button.chained') : __('chat.button.unchain') }}</button>
            @endif

            <textarea tabindex="0" data-id="root" placeholder="{{ __('chat.prompt_area.hint') }}" rows="1" max-rows="5"
                oninput="adjustTextareaRows(this)" id="chat_input" name="input" readonly
                class="w-full pl-4 pr-24 py-2 rounded text-black scrollbar dark:text-white placeholder-black dark:placeholder-white bg-gray-200 dark:bg-gray-600 border border-gray-300 focus:outline-none shadow-none border-none focus:ring-0 focus:border-transparent rounded-l-md resize-none"></textarea>
            <div class="ml-auto right-[12px] relative bottom-[4px] flex justify-end items-end">
                <label for="upload" id="upload_btn" style='display:none;'
                    class="cursor-pointer py-1 px-3 inline-flex items-center justify-center fixed w-[32px] bg-blue-600 h-[32px] hover:bg-blue-500 dark:hover:bg-blue-700 rounded mr-10 text-white dark:text-gray-300">
                    <i class="fas fa-paperclip"></i>
                </label>
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
    </div>
    <p class="text-xs text-center mb-[-8px] mt-[8px] leading-3 dark:text-gray-200">
        {{ \App\Models\SystemSetting::where('key', 'warning_footer')->first()->value ?? '' }}</p>
</form>

<x-room.prompt-area.chat-script :llms="$llms" />
<script>

    function abortGenerate() {
        $.get("{{ route('room.abort', request()->route('room_id')) }}");
        return false;
    }
</script>

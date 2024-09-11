@props(['llms' => null, 'tasks' => null])
<form method="post" enctype="multipart/form-data" action="{{ route('room.create') }}" id="prompt_area">
    @csrf
    @if (session('llms'))
        @foreach (session('llms') as $id)
            <input name="llm[]" value="{{ $id }}" style="display:none;">
            <input id="chatsTo_{{ $id }}" name="chatsTo[]" value="{{ $id }}" style="display:none;">
        @endforeach
    @endif
    <div id="attachment" class="text-xs mb-[8px] mt-[-8px] leading-3" style="display:none">
        <button onclick="event.preventDefault();$('#upload').val(''); $(this).parent().hide();"
            class="w-full h-full py-[8px] bg-blue-600 hover:bg-red-600 rounded-lg dark:text-gray-200 text-center">+
            filename</button>
    </div>
    <div class="flex overflow-hidden">
        <input id="upload" type="file" name="file" style="display: none;" onchange="uploadcheck()">

        <div class="flex flex-1 items-end justify-end flex-col overflow-hidden">
            @if ($llms && count($llms) > 1)
                <div
                    class="flex flex-1 justify-center items-center w-full overflow-hidden dark:text-white mb-2 select-none">
                    <input name="mode_track" value="0" hidden>
                    <div id="send_to_mode"
                        class="cursor-pointer bg-gray-600 hover:bg-gray-700 px-2 py-1 rounded-lg mr-2"
                        onclick="$(this).prev().val($(this).prev().val() == '0' ? '1' : '0');$(this).next().find('>div').each((e,i)=>{$(i).toggle()}); $(this).text($(this).next().find('>div:eq(0)').attr('style') == '' ? '{{ __('chat.label.multiple_send') }}' : '{{ __('chat.label.direct_send') }}')">
                        {{ __('chat.label.multiple_send') }}</div>
                    <div class="flex flex-1 items-center overflow-hidden">
                        <div class="flex mr-auto overflow-auto scrollbar scrollbar-3 min-w-[36px] sends">
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
                                            ?
                                        </div>
                                    @else
                                        <div id="llm_{{ $llm->id }}_toggle" role="tooltip"
                                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                                            {{ $llm->name }}
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                        <img
                                            src="{{ $llm->image ?? $llm->base_image ? asset(Storage::url($llm->image ?? $llm->base_image)) : '/' . config('app.LLM_DEFAULT_IMG') }}">
                                        @endenv
                                    </div>
                                </span>
                            @endforeach
                        </div>
                        <div class="flex flex-1 mr-auto overflow-auto scrollbar scrollbar-3" style="display:none">
                            @foreach ($llms as $llm)
                                <span
                                    @env('arena')  @else data-tooltip-target="llm_{{ $llm->id }}_direct_send" data-tooltip-placement="top" @endenv
                                    onclick="$('#prompt_area input[name=\'chatsTo[]\']').prop('disabled',true); $('#prompt_area .sends span').addClass('bg-red-500 hover:bg-red-600').removeClass('bg-green-500 hover:bg-green-600');$('span[data-tooltip-target=llm_{{ $llm->id }}_toggle]').removeClass('bg-red-500 hover:bg-red-600').addClass('bg-green-500 hover:bg-green-600');$('#chatsTo_{{ $llm->id }}').prop('disabled',false);$('#prompt_area').submit()"
                                    class="cursor-pointer flex py-1 px-2 mx-1 bg-blue-500 hover:bg-blue-600 rounded-full">
                                    <div
                                        class="inline h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-black overflow-hidden">

                                        @env('arena')
                                        <div class="h-full w-full bg-black flex justify-center items-center text-white">
                                            ?</div>
                                    @else
                                        <div id="llm_{{ $llm->id }}_direct_send" role="tooltip"
                                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                                            {{ $llm->name }}
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                        <img
                                            src="{{ $llm->image ?? $llm->base_image ? asset(Storage::url($llm->image ?? $llm->base_image)) : '/' . config('app.LLM_DEFAULT_IMG') }}">
                                        @endenv
                                    </div>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            <div class="flex w-full">
                <button type="button" onclick="chain_toggle()" id="chain_btn"
                    class="whitespace-nowrap h-full mr-2 text-white {{ \Session::get('chained') ?? true ? 'bg-green-500 hover:bg-green-600' : 'bg-red-600 hover:bg-red-700' }} px-2 py-1 rounded">{{ \Session::get('chained') ?? true ? __('chat.button.chained') : __('chat.button.unchain') }}</button>
                <textarea tabindex="0" data-id="root" placeholder="{{ __('chat.prompt_area.hint') }}" rows="1" max-rows="5"
                    oninput="adjustTextareaRows(this)" id="chat_input" name="input" readonly
                    class="w-full pl-4 pr-12 py-2 rounded text-black scrollbar dark:text-white placeholder-black dark:placeholder-white bg-gray-200 dark:bg-gray-600 border border-gray-300 focus:outline-none shadow-none border-none focus:ring-0 focus:border-transparent rounded-l-md resize-none"></textarea>
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
                </div>
            </div>
        </div>
    </div>
    <p class="text-xs text-center mb-[-8px] mt-[8px] leading-3 dark:text-gray-200">
        {{ \App\Models\SystemSetting::where('key', 'warning_footer')->first()->value ?? '' }}</p>
</form>
<x-room.prompt-area.chat-script :llms="$llms" :tasks="$tasks" />

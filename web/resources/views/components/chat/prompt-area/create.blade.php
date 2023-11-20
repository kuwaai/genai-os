@props([
    'chained' => false,
    'chatId' => request()->route('chat_id'),
])

<form method="post" action="{{ route('chat.request') }}" id="prompt_area">
    <div class="flex items-end justify-end">
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
        <button type="submit" id='submit_msg' style='display:none;'
                class="inline-flex items-center justify-center fixed w-[32px] bg-blue-600 h-[32px] my-[4px] mr-[12px] rounded hover:bg-blue-500 dark:hover:bg-blue-700">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none"
                 class="w-5 h-5 text-white dark:text-gray-300 icon-sm m-1 md:m-0">
                <path
                    d="M.5 1.163A1 1 0 0 1 1.97.28l12.868 6.837a1 1 0 0 1 0 1.766L1.969 15.72A1 1 0 0 1 .5 14.836V10.33a1 1 0 0 1 .816-.983L8.5 8 1.316 6.653A1 1 0 0 1 .5 5.67V1.163Z"
                    fill="currentColor"></path>
            </svg>
        </button>
    </div>
</form>

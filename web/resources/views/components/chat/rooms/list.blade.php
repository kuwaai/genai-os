@props(['LLM'])

<div class="flex flex-1 h-full overflow-y-hidden flex-col border border-black dark:border-white border-1 rounded-lg">
    <div class="border-b border-black dark:border-white">
        <div class="my-2 ml-4">
            <span class="inline whitespace-pre-line break-words menu-btn w-auto mr-auto h-6 transition duration-300 text-blue-800 dark:text-cyan-200">{{ $LLM->name }}</span>

            @if ($LLM->description)
                <span class="inline text-sm whitespace-pre-line break-words leading-none text-gray-500 dark:text-gray-400">
                    {{ $LLM->description }}
                </span>
            @endif
        </div>
    </div>
    <div class="overflow-y-auto scrollbar">
        @if (Auth::user()->hasPerm('Chat_update_new_chat'))
            <div class="m-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                <a class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('llm_id') == $LLM->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                    href="{{ route('chat.new', $LLM->id) }}">
                    <p class="flex-1 text-center text-gray-700 dark:text-white">
                        {{ __('New Chat') }}
                    </p>
                </a>
            </div>
        @endif
        @foreach (App\Models\Chats::where('user_id', Auth::user()->id)->where('llm_id', $LLM->id)->whereNull('roomID')->orderby('name')->get() as $chat)
            <div class="m-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                <a style="word-break: break-all"
                    class="flex menu-btn flex text-gray-700 dark:text-white w-full h-12 overflow-y-auto overflow-x-hidden scrollbar dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('chat_id') == $chat->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                    href="{{ route('chat.chat', $chat->id) }}">
                    <p class="flex-1 flex items-center my-auto justify-center text-center leading-none self-baseline">
                        {{ $chat->name }}
                    </p>
                </a>
            </div>
        @endforeach
    </div>
</div>

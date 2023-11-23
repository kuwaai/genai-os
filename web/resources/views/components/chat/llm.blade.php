@props(['result'])

@forelse ($result as $LLM)
    <div class="mb-2 border border-black dark:border-white border-1 rounded-lg">
        <a class="flex rounded-lg menu-btn flex items-center justify-center w-full dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('llm_id') == $LLM->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
            href="{{ route('chat.new', $LLM->id) }}">
            <div class="flex-shrink-0 m-2 h-5 w-5 rounded-full bg-black flex items-center justify-center overflow-hidden">
                <img class="h-full w-full"
                    src="{{ strpos($LLM->image, 'data:image/png;base64') === 0 ? $LLM->image : asset(Storage::url($LLM->image)) }}">
            </div>
            <div class="pl-2 overflow-hidden mr-auto my-2 ">
                {{-- blade-formatter-disable --}}
                <div class="w-full text-md text-gray-600 dark:text-white whitespace-pre-line break-words font-semibold leading-none">{{ $LLM->name }}</div>
                {{-- blade-formatter-enable --}}
                @if ($LLM->description)
                    {{-- blade-formatter-disable --}}
                    <div class="w-full text-sm leading-none whitespace-pre-line break-words text-gray-500 dark:text-gray-400">{{ $LLM->description }}</div>
                    {{-- blade-formatter-enable --}}
                @endif
            </div>
        </a>
    </div>
@empty
@endforelse

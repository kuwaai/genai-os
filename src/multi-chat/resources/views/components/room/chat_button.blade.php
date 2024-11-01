@foreach ($chats as $chat)
    <div class="mx-1 flex-shrink-0 h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-black flex items-center justify-center overflow-hidden">
        <div id="{{ $extra }}llm_{{ $chat->bot_id }}" role="tooltip"
            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
            {{ $chat->name }}
            <div class="tooltip-arrow" data-popper-arrow></div>
        </div>
        <div class="h-full w-full">
            <img data-tooltip-target="{{ $extra }}llm_{{ $chat->bot_id }}"
                data-tooltip-placement="top" class="h-full w-full"
                src="{{ $chat->image ? asset(Storage::url($chat->image)) : '/' . config('app.LLM_DEFAULT_IMG') }}">
        </div>
        <input name="llm[]" value="{{ $chat->bot_id }}" style="display:none;">
    </div>
@endforeach

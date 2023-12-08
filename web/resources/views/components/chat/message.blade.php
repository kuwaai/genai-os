<!-- resources/views/components/combined-history.blade.php -->

@props(['history', 'tasks'])

@php
    $img = App\Models\LLMs::findOrFail(App\Models\Chats::findOrFail($history->chat_id)->llm_id)->image;
    $botimgurl = strpos($img, 'data:image/png;base64') === 0 ? $img : asset(Storage::url($img));
@endphp

@if (in_array($history->id, $tasks))
    <div id="history_{{ $history->id }}" class="flex w-full mt-2 space-x-3">
        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
            <img data-tooltip-target="llm_{{$history->llm_id}}_chat" data-tooltip-placement="top" src="{{ $botimgurl }}">
        </div>
        <div class="overflow-hidden">
            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                {{-- blade-formatter-disable --}}
                <div class="text-sm space-y-3 break-words{{ $history->chained ? ' chain-msg' : '' }}{{ $history->isbot ? ' bot-msg' : '' }}" id="task_{{ $history->id }}">{{ $history->msg == "* ...thinking... *" ? "<pending holder>" : $history->msg }}</div>
                {{-- blade-formatter-enable --}}
                <x-chat.react-buttons :history="$history" :showOnFinished='true' />
            </div>
        </div>
    </div>
@else
    <div id="history_{{ $history->id }}"
        class="flex w-full mt-2 space-x-3 {{ $history->isbot ? '' : 'ml-auto justify-end' }}">
        @if ($history->isbot)
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
                <img data-tooltip-target="llm_{{$history->llm_id}}_chat" data-tooltip-placement="top" src="{{ $botimgurl }}">
            </div>
        @endif
        <div class="overflow-hidden">
            <div
                class="p-3 {{ $history->isbot ? 'bg-gray-300 rounded-r-lg rounded-bl-lg' : 'bg-blue-600 text-white rounded-l-lg rounded-br-lg' }}">
                {{-- blade-formatter-disable --}}
                <div class="text-sm space-y-3 break-words{{$history->chained ? ' chain-msg' : ''}}{{$history->isbot ? ' bot-msg' : ''}}">{{ __($history->msg) }}</div>
                {{-- blade-formatter-enable --}}
                @if ($history->isbot)
                    <x-chat.react-buttons :history="$history" :showOnFinished='false' />
                @endif
            </div>
        </div>
        @if (!$history->isbot)
            <div
                class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                User
            </div>
        @endif
    </div>
@endif

<script>
    histories[{{ $history->id }}] = $("#history_{{ $history->id }} div.text-sm.space-y-3.break-words").text()
    chatroomFormatter($("#history_{{ $history->id }}"))
</script>
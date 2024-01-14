@props(['history', 'tasks' => null, 'refers' => null, 'readonly' => false])

@php
    try {
        $img = App\Models\LLMs::findOrFail(App\Models\Chats::findOrFail($history->chat_id)->llm_id)->image;
        $botimgurl = strpos($img, 'data:image/png;base64') === 0 ? $img : asset(Storage::url($img));
    } catch (\Throwable $e) {
        $botimgurl = '';
    }
    $message = trim(str_replace(["\r\n"], "\n", $history->msg));
    $visable = true;
    if (!$history->isbot && $refers) {
        foreach ($refers->where('id', '<', $history->id) as $refer) {
            for ($i = 0; $i <= 1; $i++) {
                $referMsg = trim(str_replace(["\r\n"], "\n", $refer->msg));
                if ($i == 0) {
                    $referMsg = '"""' . $referMsg . '"""';
                }

                if ($refer->id !== $history->id) {
                    if ($message === $referMsg) {
                        $visable = false;
                        break;
                    }
                    $pos = strpos($message, $referMsg);

                    if ($pos !== false) {
                        $message = substr_replace($message, "\n##### <%ref-{$refer->id}%>\n", $pos, strlen($referMsg));
                    }
                }
            }
        }
    }
@endphp

@if ($tasks && in_array($history->id, $tasks))
    <div id="history_{{ $history->id }}" class="new-page flex w-full mt-2 space-x-3 {{ $visable ? '' : 'hidden' }}">
        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
            <img data-tooltip-target="llm_{{ $history->llm_id }}_chat" data-tooltip-placement="top"
                src="{{ $botimgurl }}" class="h-full w-full">
        </div>
        <div class="overflow-hidden">
            <div tabindex="0" hidefocus="true"
                class="{{ $history->isbot ? 'focus:cursor-auto cursor-pointer' : '' }} transition-colors p-3 bg-gray-300 rounded-r-lg rounded-bl-lg"
                @if ($history->isbot) onfocus="toggleHighlight(this, true)" onblur="toggleHighlight(this, false)" @endif>
                {{-- blade-formatter-disable --}}
                <div class="text-sm space-y-3 break-words{{ $history->chained ? ' chain-msg' : '' }}{{ $history->isbot ? ' bot-msg' : '' }}" id="task_{{ $history->id }}">{{ $history->msg == "* ...thinking... *" ? "<pending holder>" : $history->msg }}</div>
                {{-- blade-formatter-enable --}}
                <x-chat.react-buttons :history="$history" :showOnFinished='true' />
            </div>
        </div>
    </div>
@else
    <div id="history_{{ $history->id }}"
        class="new-page flex w-full mt-2 space-x-3 {{ $history->isbot ? '' : 'ml-auto justify-end' }} {{ $visable ? '' : 'hidden' }}">
        @if ($history->isbot)
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
                <img data-tooltip-target="llm_{{ $history->llm_id }}_chat" data-tooltip-placement="top"
                    src="{{ $botimgurl }}" class="h-full w-full">
            </div>
        @endif
        <div class="overflow-hidden">
            <div tabindex="0"
                @if ($history->isbot) onfocus="toggleHighlight(this, true)" onblur="toggleHighlight(this, false)" @endif
                class="p-3 transition-colors {{ $history->isbot ? 'bg-gray-300 focus:cursor-auto cursor-pointer rounded-r-lg rounded-bl-lg' : 'bg-cyan-500 text-white rounded-l-lg rounded-br-lg' }}">
                {{-- blade-formatter-disable --}}
                <div class="text-sm space-y-3 break-words{{$history->chained ? ' chain-msg' : ''}}{{$history->isbot ? ' bot-msg' : ''}}">{{ __($message) }}</div>
                {{-- blade-formatter-enable --}}
                @if (!$readonly && $history->isbot)
                    <x-chat.react-buttons :history="$history" :showOnFinished='false' />
                @endif
            </div>
        </div>
        @if (!$history->isbot)
            <div
                class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                {{ mb_substr(request()->user()->name, 0, 1, 'UTF-8') }}
            </div>
        @endif
    </div>
@endif
<div id="tmp_{{ $history->id }}" class="hidden">{{ $history->msg }}</div>

<script>
    histories[{{ $history->id }}] = $("#tmp_{{ $history->id }}").text();
    $("#tmp_{{ $history->id }}").remove();
    chatroomFormatter($("#history_{{ $history->id }}"))
</script>

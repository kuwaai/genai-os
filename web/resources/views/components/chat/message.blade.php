<!-- resources/views/components/combined-history.blade.php -->

@props(['history', 'tasks', 'refers'])

@php
    $img = App\Models\LLMs::findOrFail(App\Models\Chats::findOrFail($history->chat_id)->llm_id)->image;
    $botimgurl = strpos($img, 'data:image/png;base64') === 0 ? $img : asset(Storage::url($img));
    $message = trim(str_replace(["\r\n"], "\n", $history->msg));
    $visable = true;
    if (!$history->isbot) {
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

@if (in_array($history->id, $tasks))
    <div id="history_{{ $history->id }}" class="flex w-full mt-2 space-x-3 {{ $visable ? '' : 'hidden' }}">
        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
            <img data-tooltip-target="llm_{{ $history->llm_id }}_chat" data-tooltip-placement="top"
                src="{{ $botimgurl }}">
        </div>
        <div class="overflow-hidden">
            <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg" onmouseover="toggleHighlight(this, true)"
                onmouseout="toggleHighlight(this, false)">
                {{-- blade-formatter-disable --}}
                <div class="text-sm space-y-3 break-words{{ $history->chained ? ' chain-msg' : '' }}{{ $history->isbot ? ' bot-msg' : '' }}" id="task_{{ $history->id }}">{{ $history->msg == "* ...thinking... *" ? "<pending holder>" : $history->msg }}</div>
                {{-- blade-formatter-enable --}}
                <x-chat.react-buttons :history="$history" :showOnFinished='true' />
            </div>
        </div>
    </div>
@else
    <div id="history_{{ $history->id }}"
        class="flex w-full mt-2 space-x-3 {{ $history->isbot ? '' : 'ml-auto justify-end' }} {{ $visable ? '' : 'hidden' }}">
        @if ($history->isbot)
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
                <img data-tooltip-target="llm_{{ $history->llm_id }}_chat" data-tooltip-placement="top"
                    src="{{ $botimgurl }}">
            </div>
        @endif
        <div class="overflow-hidden">
            <div onmouseover="toggleHighlight(this, true)" onmouseout="toggleHighlight(this, false)"
                class="p-3 {{ $history->isbot ? 'bg-gray-300 rounded-r-lg rounded-bl-lg' : 'bg-blue-600 text-white rounded-l-lg rounded-br-lg' }}">
                {{-- blade-formatter-disable --}}
                <div class="text-sm space-y-3 break-words{{$history->chained ? ' chain-msg' : ''}}{{$history->isbot ? ' bot-msg' : ''}}">{{ __($message) }}</div>
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
<div id="tmp_{{ $history->id }}" class="hidden">{{ $history->msg }}</div>

<script>
    histories[{{ $history->id }}] = $("#tmp_{{ $history->id }}").text();
    $("#tmp_{{ $history->id }}").remove();
    chatroomFormatter($("#history_{{ $history->id }}"))

    function toggleHighlight(node, flag) {
        if ($(node).find(".bot-msg").length != 0) {
            if ($(node).find(".chain-msg").length != 0) {
                let $trigger = true;
                $prevMsgs = $(node).parent().parent().prevAll('div').filter(function() {
                    if ($(this).find("div div.bot-msg").length == 0) {
                        if ($trigger) {
                            $trigger = false;
                            return true
                        }
                        return false
                    } else if ($(this).find("img").attr("data-tooltip-target") == $(node).parent().parent()
                        .find(
                            "div img").attr("data-tooltip-target")) {
                        $trigger = true;
                        return true
                    }
                    return false
                }).find("div div");

                if (flag) {
                    $($prevMsgs).addClass("bg-yellow-500");
                    $(node).addClass("bg-orange-400");
                } else {
                    $($prevMsgs).removeClass("bg-yellow-500");
                    $(node).removeClass("bg-orange-400");
                }
            }
            $prevUser = $(node).parent().parent().prevAll('div').filter(function() {
                return $(this).find('div div div.bot-msg').length == 0;
            }).first()
            $prevUserMsg = $prevUser.find('div div div').text().trim()
            $refRecord = $(node).parent().parent().prevAll('div').filter(function() {
                $msgWindow = $(this).find('div div div.bot-msg');
                return $msgWindow.length != 0 && $msgWindow.text().trim() == $prevUserMsg;
            }).first().find("div div")
            if ($refRecord.length > 0) {
                if (flag) {
                    $($refRecord).addClass("bg-yellow-500");
                    $(node).addClass("bg-orange-400");
                } else {
                    $($refRecord).removeClass("bg-yellow-500");
                    $(node).removeClass("bg-orange-400");
                }
            } else {
                $prevUser = $prevUser.find("div div")
                if (flag) {
                    $($prevUser).addClass("bg-yellow-500");
                    $(node).addClass("bg-orange-400");
                } else {
                    $($prevUser).removeClass("bg-yellow-500");
                    $(node).removeClass("bg-orange-400");
                }
            }
        }
    }
</script>

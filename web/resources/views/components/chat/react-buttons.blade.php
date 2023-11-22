<!-- resources/views/components/ButtonGroupComponent.blade.php -->

@props(['history', 'showOnFinished'])

<div class="flex space-x-1{{ $showOnFinished ? ' show-on-finished' : '' }}"
    style="{{ $showOnFinished ? 'display:none;' : '' }}">
    <button class="flex text-black hover:bg-gray-400 p-2 rounded-lg"
        onclick="copytext($(this).parent().parent().children()[0])">
        <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
            stroke-linejoin="round" class="icon-sm" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2">
            </path>
            <rect x="8" y="2" width="8" height="4" rx="1" ry="1">
            </rect>
        </svg>
        <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
            stroke-linejoin="round" class="icon-sm" style="display:none;" height="1em" width="1em"
            xmlns="http://www.w3.org/2000/svg">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
    </button>
    @if (request()->user()->hasPerm('Chat_update_feedback'))
        <x-chat.modals.feedback />
        <button
            class="flex text-black hover:bg-gray-400 p-2 rounded-lg {{ $history->nice === true ? 'text-green-600' : 'text-black' }}"
            data-modal-target="feedback" data-modal-toggle="feedback"
            onclick="feedback({{ $history->id }},1,this,{!! htmlspecialchars(
                json_encode(['detail' => $history->detail, 'flags' => $history->flags, 'nice' => $history->nice]),
            ) !!});">
            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
                stroke-linejoin="round" class="icon-sm" height="1em" width="1em"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
                </path>
            </svg>
        </button>
        <button
            class="flex text-black hover:bg-gray-400 p-2 rounded-lg {{ $history->nice === false ? 'text-red-600' : 'text-black' }}"
            data-modal-target="feedback" data-modal-toggle="feedback"
            onclick="feedback({{ $history->id }},2,this,{!! htmlspecialchars(
                json_encode(['detail' => $history->detail, 'flags' => $history->flags, 'nice' => $history->nice]),
            ) !!});">
            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
                stroke-linejoin="round" class="icon-sm" height="1em" width="1em"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17">
                </path>
            </svg>
        </button>
    @endif
</div>

<script>
    var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;

    function copytext(node) {
        var textArea = document.createElement("textarea");
        textArea.value = node.textContent;

        document.body.appendChild(textArea);

        textArea.select();

        try {
            document.execCommand("copy");
        } catch (err) {
            console.log("Copy not supported or failed: ", err);
        }

        document.body.removeChild(textArea);

        $(node).parent().children().eq(1).children().eq(0).children().eq(0).hide();
        $(node).parent().children().eq(1).children().eq(0).children().eq(1).show();
        setTimeout(function() {
            $(node).parent().children().eq(1).children().eq(0).children().eq(0).show();
            $(node).parent().children().eq(1).children().eq(0).children().eq(1).hide();
        }, 3000);
    }
</script>

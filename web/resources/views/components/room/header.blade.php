@props(['llms', 'readonly' => false])

@if (!session('llms'))
    <form id="editChat" action="{{ route('room.edit') }}" method="post" class="hidden">
        @csrf
        <input name="id" value="{{ App\Models\ChatRoom::findOrFail(request()->route('room_id'))->id }}" />
        <input name="new_name" />
        <input type="hidden" name="limit" value="{{ request()->input('limit') > 0 ? request()->input('limit') : '0' }}">
    </form>
@endif
<div id="chatHeader"
    class="bg-gray-300 dark:bg-gray-700 p-2 sm:p-4 h-20 text-gray-700 dark:text-white flex items-center">
    @if ($readonly)
        @foreach (App\Models\Chats::join('llms', 'llms.id', '=', 'llm_id')->where('user_id', Auth::user()->id)->where('roomID', request()->route('room_id'))->orderby('llm_id')->get() as $chat)
            <div
                class="mx-1 flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
                <img data-tooltip-target="llm_{{ $chat->llm_id }}_toggle" data-tooltip-placement="top"
                    class="h-full w-full"
                    src="{{ strpos($chat->image, 'data:image/png;base64') === 0 ? $chat->image : asset(Storage::url($chat->image)) }}">
                <div id="llm_{{ $chat->llm_id }}_toggle" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                    {{ $chat->name }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="llm_{{ $chat->llm_id }}_chat" role="tooltip" access_code="{{ $chat->access_code }}"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ $chat->name }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
            </div>
        @endforeach
    @else
        <button
            class="block sm:hidden text-center text-white bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-6 py-2 dark:bg-gray-700 dark:hover:bg-gray-800 focus:outline-none dark:focus:ring-blue-800"
            type="button" data-drawer-target="chatlist_drawer" data-drawer-show="chatlist_drawer"
            aria-controls="chatlist_drawer">
            <i class="fas fa-bars"></i>
        </button>
    @endif
    <p class="flex-1 flex flex-wrap items-center mx-2 overflow-y-auto overflow-x-hidden scrollbar">
        @if (session('llms'))
            {{ __('room.header.new_room') }}
        @else
            {{ App\Models\ChatRoom::findOrFail(request()->route('room_id'))->name }}
        @endif
    </p>

    <div class="flex overflow-x-hidden">
        <div
            class="flex items-center mr-1 max-w-[144px] min-w-[40px] overflow-x-auto overflow-y-hidden scrollbar scrollbar-3">
            @if (session('llms'))
                @foreach ($llms as $llm)
                    <div
                        class="mx-1 flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
                        <img data-tooltip-target="llm_{{ $llm->id }}_toggle" data-tooltip-placement="top"
                            class="h-full w-full"
                            src="{{ strpos($llm->image, 'data:image/png;base64') === 0 ? $llm->image : asset(Storage::url($llm->image)) }}">
                        <div id="llm_{{ $llm->id }}_toggle" role="tooltip"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                            {{ $llm->name }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                        <div id="llm_{{ $llm->id }}_chat" role="tooltip"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                            {{ $llm->name }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                @endforeach
            @elseif(!$readonly)
                @foreach (App\Models\Chats::join('llms', 'llms.id', '=', 'llm_id')->where('user_id', Auth::user()->id)->where('roomID', request()->route('room_id'))->orderby('llm_id')->get() as $chat)
                    <div
                        class="mx-1 flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
                        <img data-tooltip-target="llm_{{ $chat->llm_id }}_toggle" data-tooltip-placement="top"
                            class="h-full w-full"
                            src="{{ strpos($chat->image, 'data:image/png;base64') === 0 ? $chat->image : asset(Storage::url($chat->image)) }}">
                        <div id="llm_{{ $chat->llm_id }}_toggle" role="tooltip"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                            {{ $chat->name }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                        <div id="llm_{{ $chat->llm_id }}_chat" role="tooltip" access_code="{{ $chat->access_code }}"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                            {{ $chat->name }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                @endforeach
                <div id="react_copy" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('chat.react_btn.copy') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="react_like" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('chat.react_btn.like') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="react_dislike" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('chat.react_btn.dislike') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="react_translate" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('chat.react_btn.translate') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="react_safetyGuard" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('chat.react_btn.safety_guard') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="react_quote" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('chat.react_btn.quote') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="ref-tooltip" role="tooltip"
                    class="absolute z-10 invisible whitespace-pre-wrap max-w-[600px] inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('Reference') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
            @endif
        </div>
        @if (!$readonly)
            @if (!session('llms'))
                @if (request()->user()->hasPerm('Room_read_export_chat'))
                    <button onclick="export_chat()" data-modal-target="exportModal" data-modal-toggle="exportModal"
                        class="bg-green-500 mr-3 hover:bg-green-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                        <i class="fas fa-share-alt"></i>
                    </button>

                    <script>
                        function export_chat() {
                            //JSON format
                            var chatMessages = [];

                            $("#chatroom > div > div.flex.w-full.mt-2.space-x-3 ").each(function(index, element) {
                                var history_id = $(element).prop("id").replace("history_", "")
                                var msgText = histories[history_id]
                                var isBot = $(element).children("div").children("div").children("div").hasClass("bot-msg")
                                var chained = $(element).children("div").children("div").children("div").hasClass("chain-msg");
                                if (isBot) {
                                    var message = {
                                        "role": "assistant",
                                        "model": $("#" + $.escapeSelector($(element).children("div").children("img").data(
                                                "tooltip-target")))
                                            .attr("access_code"),
                                        "content": msgText,
                                        "chain": chained
                                    };
                                } else {
                                    var message = {
                                        "role": "user",
                                        "content": msgText,
                                    };
                                }

                                chatMessages.push(message);
                            });

                            $("#export_json").val(JSON.stringify({
                                "messages": chatMessages
                            }, null, 4))
                            //Tab Separate Values
                            var csvContent = "role	model	content	chain\n"; // Define CSV header

                            $("#chatroom > div > div.flex.w-full.mt-2.space-x-3 ").each(function(index, element) {
                                var historyId = $(element).prop("id").replace("history_", "");
                                var msgText = JSON.stringify(histories[historyId]);
                                if (msgText.charAt(0) === '"' && msgText.charAt(msgText.length - 1) === '"') {
                                    msgText = msgText.substring(1, msgText.length - 1);
                                }
                                var isBot = $(element).children("div").children("div").children("div").hasClass("bot-msg");
                                var chained = $(element).children("div").children("div").children("div").hasClass("chain-msg");

                                var row = "";
                                if (isBot) {
                                    var model = $("#" + $.escapeSelector($(element).children("div").children("img").data(
                                            "tooltip-target")))
                                        .attr("access_code");
                                    if (model == undefined) model = "";
                                    row = `assistant	${model}	${msgText}	${chained}\n`;
                                } else {
                                    row = `user		${msgText}	\n`;
                                }

                                csvContent += row; // Add row to CSV content
                            });
                            $("#export_tsv").val(csvContent)
                        }
                    </script>
                @endif
                <button onclick="saveChat()"
                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center hidden">
                    <i class="fas fa-save"></i>
                </button>
                <button onclick="editChat()"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                    <i class="fas fa-pen"></i>
                </button>
                @if (request()->user()->hasPerm('Room_delete_chatroom'))
                    <button data-modal-target="delete_chat_modal" data-modal-toggle="delete_chat_modal"
                        class="bg-red-500 ml-3 hover:bg-red-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                        <i class="fas fa-trash"></i>
                    </button>
                @endif
            @else
                @if (request()->user()->hasPerm('Room_update_import_chat'))
                    <div class="flex">
                        <button data-modal-target="importModal" data-modal-toggle="importModal"
                            class="bg-green-500 ml-3 hover:bg-green-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                            <i class="fas fa-file-import"></i>
                        </button>
                    </div>
                @endif
            @endif
        @endif
    </div>
</div>

@if (!$readonly)
    <script>
        function editChat() {
            $("#chatHeader button").find('.fa-pen').parent().addClass('hidden');
            $("#chatHeader button").find('.fa-save').parent().removeClass('hidden');
            name = $("#chatHeader > p:eq(0)").text().trim();
            $("#chatHeader > p:eq(0)").html(
                `<input type='text' class='form-input rounded-md w-full bg-gray-200 dark:bg-gray-700 border-gray-300 border'/>`
            );
            $("#chatHeader > p:eq(0) input").val(name).attr('old', name);
            $("#chatHeader >p >input:eq(0)").keypress(function(e) {
                if (e.which == 13) saveChat();
            });
        }


        function saveChat() {
            input = $("#chatHeader >p >input:eq(0)")
            if (input.val() != input.attr("old")) {
                $("#editChat input:eq(2)").val(input.val())
                $("#editChat").submit();
            }
            $("#chatHeader button").find('.fa-pen').parent().removeClass('hidden');
            $("#chatHeader button").find('.fa-save').parent().addClass('hidden');
            $("#chatHeader >p").text(input.val())
        }
    </script>
@endif

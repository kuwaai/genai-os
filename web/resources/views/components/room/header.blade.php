@props(['llms', 'readonly' => false])
@if (!session('llms'))
    <form id="editChat" action="{{ route('room.edit') }}" method="post" class="hidden">
        @csrf
        <input name="id" value="{{ App\Models\ChatRoom::findOrFail(request()->route('room_id'))->id }}" />
        <input name="new_name" />
    </form>
@endif
<div id="chatHeader"
    class="bg-gray-300 dark:bg-gray-900/70 p-2 sm:p-4 h-20 text-gray-700 dark:text-white items-center flex">
    @if ($readonly)
        @foreach ($llms as $chat)
            <div
                class="mx-1 flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
                <img data-tooltip-target="llm_{{ $chat->id }}_toggle" data-tooltip-placement="top"
                    class="h-full w-full"
                    src="{{ strpos($chat->image, 'data:image/png;base64') === 0 ? $chat->image : asset(Storage::url($chat->image)) }}">
                <div id="llm_{{ $chat->id }}_toggle" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                    {{ $chat->name }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="llm_{{ $chat->id }}_chat" role="tooltip" access_code="{{ $chat->access_code }}"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ $chat->name }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
            </div>
        @endforeach
    @else
        <button
            class="block sm:hidden text-center text-white bg-gray-300 hover:bg-gray-400 dark:bg-gray-800 dark:hover:bg-gray-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-5 dark:text-white hover:dark:text-gray-300 focus:outline-none dark:focus:ring-blue-800"
            type="button" data-drawer-target="chatlist_drawer" data-drawer-show="chatlist_drawer"
            aria-controls="chatlist_drawer">
            <i class="fas fa-bars"></i>
        </button>
    @endif
    <p class="mr-auto min-h-6 max-h-12 mx-2 truncate-text overflow-ellipsis overflow-hidden">
        @if (session('llms'))
            {{ __('New Chatroom') }}
        @else
            {{ App\Models\ChatRoom::findOrFail(request()->route('room_id'))->name }}
        @endif
    </p>

    <div class="flex overflow-x-hidden max-w-[144px] min-w-[52px]">
        <div class="flex items-center mr-1 overflow-x-auto overflow-y-hidden scrollbar scrollbar-3">
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
                @foreach ($llms as $chat)
                    <div
                        class="mx-1 flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
                        <img data-tooltip-target="llm_{{ $chat->id }}_toggle" data-tooltip-placement="top"
                            class="h-full w-full"
                            src="{{ strpos($chat->image, 'data:image/png;base64') === 0 ? $chat->image : asset(Storage::url($chat->image)) }}">
                        <div id="llm_{{ $chat->id }}_toggle" role="tooltip"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                            {{ $chat->name }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                        <div id="llm_{{ $chat->id }}_chat" role="tooltip" access_code="{{ $chat->access_code }}"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                            {{ $chat->name }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                @endforeach
                <div id="react_copy" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('Copy message') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="react_like" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('Like the message') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="react_dislike" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('Dislike the message') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="react_translate" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('Translate by the model') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="react_safetyGuard" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('Safety Guard Check') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="react_quote" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('Quote this message') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div id="ref-tooltip" role="tooltip"
                    class="absolute z-10 invisible whitespace-pre-wrap max-w-[600px] inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                    {{ __('Reference') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
            @endif
        </div>
    </div>
    @if (!$readonly)
        <nav x-data="{ open: false }">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="p-3 text-white hover:text-gray-300"><svg width="24" height="24"
                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-md">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3 12C3 10.8954 3.89543 10 5 10C6.10457 10 7 10.8954 7 12C7 13.1046 6.10457 14 5 14C3.89543 14 3 13.1046 3 12ZM10 12C10 10.8954 10.8954 10 12 10C13.1046 10 14 10.8954 14 12C14 13.1046 13.1046 14 12 14C10.8954 14 10 13.1046 10 12ZM17 12C17 10.8954 17.8954 10 19 10C20.1046 10 21 10.8954 21 12C21 13.1046 20.1046 14 19 14C17.8954 14 17 13.1046 17 12Z"
                                fill="currentColor"></path>
                        </svg></button>
                </x-slot>

                <x-slot name="content">
                    @if (!session('llms') && request()->user()->hasPerm('Room_read_export_chat'))
                        <x-dropdown-link onclick="event.preventDefault();export_chat()" href="#"
                            data-modal-target="exportModal" data-modal-toggle="exportModal">
                            {{ __('Export Chat') }}
                        </x-dropdown-link>
                    @endif
                    @if (request()->user()->hasPerm('Room_update_import_chat'))
                        <x-dropdown-link href="#" onclick="event.preventDefault();"
                            data-modal-target="importModal" data-modal-toggle="importModal">
                            {{ __('Import Chat') }}
                        </x-dropdown-link>
                    @endif
                    @if (!session('llms'))
                        <x-dropdown-link id="edit_chat_name_btn" href="#"
                            onclick="event.preventDefault();editChat()">
                            {{ __('Edit Chat Name') }}
                        </x-dropdown-link>
                        <x-dropdown-link class="!text-green-500 hover:!text-green-600"
                            href="{{ route('room.share', request()->route('room_id')) }}" target="_blank">
                            {{ __('Share link') }}
                        </x-dropdown-link>
                        @if (request()->user()->hasPerm('Room_delete_chatroom'))
                            <x-dropdown-link href="#"
                                onclick="event.preventDefault();$('#deleteChat input[name=id]').val({{ App\Models\ChatRoom::findOrFail(request()->route('room_id'))->id }});$('#deleteChat h3 span:eq(1)').text('{{ App\Models\ChatRoom::findOrFail(request()->route('room_id'))->name }}');"
                                class="!text-red-500 hover:!text-red-600" data-modal-target="delete_chat_modal"
                                data-modal-toggle="delete_chat_modal">
                                {{ __('Delete') }}
                            </x-dropdown-link>
                        @endif
                    @endif
                </x-slot>
            </x-dropdown>
        </nav>
    @endif
</div>

@if (!$readonly)
    <script>
        function editChat() {
            $("#edit_chat_name_btn").addClass('hidden');
            name = $("#chatHeader >p:eq(0)").text().trim();
            $("#chatHeader >p").addClass("flex justify-end items-center")
            $("#chatHeader >p:eq(0)").html(
                `<input type='text' class='form-input rounded-md bg-gray-200 dark:bg-gray-700 border-gray-300 border w-full mr-2 pr-10' value='${name}' old='${name}'/><div class="fixed mr-2 cursor-pointer py-2 px-3 text-white hover:text-gray-200" onclick="saveChat()"><i class="fas fa-check"></i></div>`
            )
            $("#chatHeader >p:eq(0)").addClass("w-full")

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
            $("#edit_chat_name_btn").removeClass('hidden');
            $("#chatHeader >p:eq(0)").removeClass("w-full")
            $("#chatHeader >p").text(input.val())
            $("#chatHeader >p").removeClass("flex justify-end items-center")
        }

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
                var msgText = histories[historyId].replaceAll("\n","\\n").replaceAll("\t","\\t");
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

<x-app-layout>
    @php
        $result = DB::table(function ($query) {
            $query
                ->select(DB::raw('substring(name, 7) as model_id'), 'perm_id')
                ->from('group_permissions')
                ->join('permissions', 'perm_id', '=', 'permissions.id')
                ->where('group_id', Auth()->user()->group_id)
                ->where('name', 'like', 'model_%')
                ->get();
        }, 'tmp')
            ->join('llms', 'llms.id', '=', DB::raw('CAST(tmp.model_id AS BIGINT)'))
            ->select('tmp.*', 'llms.*')
            ->where('llms.enabled', true)
            ->orderby('llms.order')
            ->orderby('llms.created_at')
            ->get();
    @endphp

    <div class="flex h-full max-w-7xl mx-auto py-2">
        <div class="bg-white dark:bg-gray-800 text-white w-64 flex-shrink-0 relative rounded-l-lg overflow-hidden">
            <div class="p-3 {{ $result->count() == 1 ? 'flex' : '' }} h-full overflow-y-auto scrollbar">
                @if ($result->count() == 0)
                    <div
                        class="flex-1 h-full flex flex-col w-full text-center rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                        No available LLM to chat with<br>
                        Please come back later!
                    </div>
                @else
                    @foreach ($result as $LLM)
                        <div
                            class="{{ $result->count() == 1 ? 'flex flex-1 flex-col' : 'mb-2' }} border border-black dark:border-white border-1 rounded-lg">
                            <div class="border-b border-black dark:border-white">
                                @if ($LLM->link)
                                    <a href="{{ $LLM->link }}" target="_blank"
                                        class="inline-block menu-btn my-2 w-auto ml-4 mr-auto h-6 transition duration-300 text-blue-800 dark:text-cyan-200">{{ $LLM->name }}</a>
                                @else
                                    <span
                                        class="inline-block menu-btn my-2 w-auto ml-4 mr-auto h-6 transition duration-300 text-blue-800 dark:text-cyan-200">{{ $LLM->name }}</a>
                                @endif
                            </div>
                            <div class="{{ $result->count() == 1 ? '' : 'max-h-[182px]' }} overflow-y-auto scrollbar">
                                <div
                                    class="m-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                                    <a class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('llm_id') == $LLM->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                                        href="{{ route('chat.new', $LLM->id) }}">
                                        <p class="flex-1 text-center text-gray-700 dark:text-white">{{ __('New Chat') }}
                                        </p>
                                    </a>
                                </div>
                                @foreach (App\Models\Chats::where('user_id', Auth::user()->id)->where('llm_id', $LLM->id)->whereNull('dcID')->orderby('name')->get() as $chat)
                                    <div
                                        class="m-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                                        <a style="word-break:break-all"
                                            class="flex menu-btn flex text-gray-700 dark:text-white w-full h-12 overflow-y-auto overflow-x-hidden scrollbar dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('chat_id') == $chat->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                                            href="{{ route('chat.chat', $chat->id) }}">
                                            <p
                                                class="flex-1 flex items-center my-auto justify-center text-center leading-none self-baseline">
                                                {{ $chat->name }}</p>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        @if (!request()->route('chat_id') && !request()->route('llm_id'))
            <div id="histories_hint"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                Select a chatroom to begin with
            </div>
        @else
            <div id="histories"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden">
                @if (request()->route('chat_id'))
                    <form id="deleteChat" action="{{ route('chat.delete') }}" method="post" class="hidden">
                        @csrf
                        @method('delete')
                        <input name="id"
                            value="{{ App\Models\Chats::findOrFail(request()->route('chat_id'))->id }}" />
                    </form>

                    <form id="editChat" action="{{ route('chat.edit') }}" method="post" class="hidden">
                        @csrf
                        <input name="id"
                            value="{{ App\Models\Chats::findOrFail(request()->route('chat_id'))->id }}" />
                        <input name="new_name" />
                    </form>
                @endif
                <div id="chatHeader" class="bg-gray-300 dark:bg-gray-700 p-4 h-20 text-gray-700 dark:text-white flex">
                    @if (request()->route('llm_id'))
                        <p class="flex items-center">{{ __('New Chat with') }}
                            {{ App\Models\LLMs::findOrFail(request()->route('llm_id'))->name }}</p>
                    @elseif(request()->route('chat_id'))
                        <p class="flex-1 flex flex-wrap items-center mr-3 overflow-y-auto overflow-x-hidden scrollbar"
                            style='word-break:break-word'>
                            {{ App\Models\Chats::findOrFail(request()->route('chat_id'))->name }}</p>

                        <div class="flex">
                            <button onclick="saveChat()"
                                class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center hidden">
                                <i class="fas fa-save"></i>
                            </button>
                            <button onclick="editChat()"
                                class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button onclick="deleteChat()"
                                class="bg-red-500 ml-3 hover:bg-red-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    @endif
                </div>
                <div id="chatroom" class="flex-1 p-4 overflow-y-auto flex flex-col-reverse scrollbar">
                    @if (request()->route('chat_id'))
                        @php
                            $botimgurl = asset(Storage::url(App\Models\LLMs::findOrFail(App\Models\Chats::findOrFail(request()->route('chat_id'))->llm_id)->image));
                            $tasks = \Illuminate\Support\Facades\Redis::lrange('usertask_' . Auth::user()->id, 0, -1);
                        @endphp
                        @foreach (App\Models\Histories::where('chat_id', request()->route('chat_id'))->orderby('created_at', 'desc')->orderby('id')->get() as $history)
                            @if (in_array($history->id, $tasks))
                                <div class="flex w-full mt-2 space-x-3">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        <img src="{{ $botimgurl }}">
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm whitespace-pre-line break-words"
                                                id="task_{{ $history->id }}">{{ __($history->msg) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div id="history_{{ $history->id }}"
                                    class="flex w-full mt-2 space-x-3 {{ $history->isbot ? '' : 'ml-auto justify-end' }}">
                                    @if ($history->isbot)
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                            <img src="{{ $botimgurl }}">
                                        </div>
                                    @endif
                                    <div class="overflow-hidden">
                                        <div
                                            class="p-3 {{ $history->isbot ? 'bg-gray-300 rounded-r-lg rounded-bl-lg' : 'bg-blue-600 text-white rounded-l-lg rounded-br-lg' }}">
                                            <p class="text-sm whitespace-pre-line break-words">{{ $history->msg }}</p>
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
                        @endforeach
                    @elseif(request()->route('llm_id') && App\Models\LLMs::find(request()->route('llm_id'))->access_code == 'doc_qa')
                        <p class="m-auto text-white">{!! __('A document is required in order to use this LLM, <br>Please upload a file first.') !!}</p>
                    @endif
                </div>
                <div
                    class="bg-gray-300 dark:bg-gray-500 p-4 flex flex-col overflow-y-hidden {{ request()->route('llm_id') && App\Models\LLMs::find(request()->route('llm_id'))->access_code == 'doc_qa' ? 'overflow-x-hidden' : '' }}">
                    @if (request()->route('llm_id') && App\Models\LLMs::find(request()->route('llm_id'))->access_code == 'doc_qa')
                        <form method="post" action="{{ route('chat.upload') }}" class="m-auto"
                            enctype="multipart/form-data">
                            @csrf
                            <input name='llm_id' style='display:none;' value='{{ request()->route('llm_id') }}'>
                            <input id="upload" type="file" name="file" style="display: none;"
                                onchange='uploadcheck()'>
                            <label for="upload" id="upload_btn"
                                class="bg-green-500 hover:bg-green-600 px-3 py-2 rounded cursor-pointer text-white">{{ __('Upload File') }}</label>
                        </form>
                    @elseif (request()->route('llm_id'))
                        <form method="post" action="{{ route('chat.create') }}" id="prompt_area">
                            <div class="flex items-end justify-end">
                                @csrf
                                <input name="llm_id" value="{{ request()->route('llm_id') }}" style="display:none;">
                                <textarea tabindex="0" data-id="root" placeholder="{{ __('Send a message') }}" rows="1" max-rows="5"
                                    oninput="adjustTextareaRows()" id="chat_input" name="input"
                                    class="w-full pl-4 pr-12 py-2 rounded text-black scrollbar dark:text-white placeholder-black dark:placeholder-white bg-gray-200 dark:bg-gray-600 border border-gray-300 focus:outline-none shadow-none border-none focus:ring-0 focus:border-transparent rounded-l-md resize-none"></textarea>
                                <button type="submit"
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
                    @elseif(request()->route('chat_id'))
                        <form method="post" action="{{ route('chat.request') }}" id="prompt_area">
                            <div class="flex items-end justify-end">
                                @csrf
                                <input name="chat_id" value="{{ request()->route('chat_id') }}"
                                    style="display:none;">
                                <input id="chained" style="display:none;"
                                    {{ \Session::get('chained') ? '' : 'disabled' }}>
                                @if (!in_array(App\Models\LLMs::find(App\Models\Chats::find(request()->route('chat_id'))->llm_id)->access_code , ["doc_qa","web_qa"]))
                                    <button type="button" onclick="chain_toggle()" id="chain_btn"
                                        class="whitespace-nowrap my-auto text-white mr-3 {{ \Session::get('chained') ? 'bg-green-500 hover:bg-green-600' : 'bg-red-600 hover:bg-red-700' }} px-3 py-2 rounded">{{ \Session::get('chained') ? __('Chained') : __('Unchain') }}</button>
                                @endif
                                <textarea tabindex="0" data-id="root" placeholder="{{ __('Send a message') }}" rows="1" max-rows="5"
                                    oninput="adjustTextareaRows()" id="chat_input" name="input" readonly
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
                    @endif
                </div>
            </div>
            @if (request()->route('chat_id'))
                <script>
                    function chain_toggle() {
                        $.get("{{ route('chat.chain') }}", {
                            switch: $('#chained').prop('disabled')
                        }, function() {
                            $('#chained').prop('disabled', !$('#chained').prop('disabled'));
                            $('#chain_btn').toggleClass('bg-green-500 hover:bg-green-600 bg-red-600 hover:bg-red-700');
                            $('#chain_btn').text($('#chained').prop('disabled') ? '{{ __('Unchain') }}' :
                                '{{ __('Chained') }}')
                        })
                    }

                    function deleteChat() {
                        $("#deleteChat").submit();
                    }

                    function editChat() {
                        $("#chatHeader button").find('.fa-pen').parent().addClass('hidden');
                        $("#chatHeader button").find('.fa-save').parent().removeClass('hidden');
                        name = $("#chatHeader >p:eq(0)").text().trim();
                        $("#chatHeader >p:eq(0)").html(
                            `<input type='text' class='form-input rounded-md w-full bg-gray-200 dark:bg-gray-700 border-gray-300 border' value='${name}' old='${name}'/>`
                        )

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

                    $("#chat_input").val("訊息處理中...請稍後...")
                    $chattable = false
                    $("#prompt_area").submit(function(event) {
                        event.preventDefault();
                        if ($chattable) {
                            this.submit();
                            $chattable = false
                        }
                        $("#submit_msg").hide()
                        $("#chat_input").val("訊息處理中...請稍後...")
                        $("#chat_input").prop("readonly", true)
                    })
                    task = new EventSource("{{ route('chat.sse') }}", {
                        withCredentials: false
                    });
                    task.addEventListener('error', error => {
                        task.close();
                    });
                    task.addEventListener('message', event => {
                        if (event.data == "finished") {
                            $chattable = true
                            $("#submit_msg").show()
                            $("#chat_input").val("")
                            $("#chat_input").prop("readonly", false)
                        } else {
                            data = JSON.parse(event.data)
                            number = data["history_id"];
                            msg = data["msg"];
                            msg = msg.replace("[Oops, the LLM returned empty message, please try again later or report to admins!]","{{__('[Oops, the LLM returned empty message, please try again later or report to admins!]')}}")
                            msg = msg.replace("[Sorry, something is broken, please try again later!]","{{__('[Sorry, something is broken, please try again later!]')}}")
                            $('#task_' + number).text(msg);
                        }
                    });
                </script>
            @endif
            <script>
                function uploadcheck(){
                    if ($(this)[0].files[0].size <= 10*1024*1024){
                        $(this).parent().submit();
                    }
                    $("#upload_btn").val('').text('{{__("File Too Large")}}').toggleClass("bg-green-500 hover:bg-green-600 bg-red-400 hover:bg-red-500")
                
                    setTimeout(function () {
                        $(this).text('{{__("Upload file")}}').toggleClass("bg-green-500 hover:bg-green-600 bg-red-400 hover:bg-red-500")
                    }, 3000);
                }

                if ($("#chat_input")) {
                    $("#chat_input").focus();

                    function adjustTextareaRows() {
                        if ($('#chat_input').length) {
                            const textarea = $('#chat_input');
                            const maxRows = parseInt(textarea.attr('max-rows')) || 5;
                            const lineHeight = parseInt(textarea.css('line-height'));

                            textarea.attr('rows', 1);

                            const contentHeight = textarea[0].scrollHeight;
                            const rowsToDisplay = Math.floor(contentHeight / lineHeight);

                            textarea.attr('rows', Math.min(maxRows, rowsToDisplay));
                        }
                    }
                    $("#chat_input").on("keydown", function(event) {
                        if (event.key === "Enter" && !event.shiftKey) {
                            event.preventDefault();
                            $("#prompt_area").submit();
                        } else if (event.key === "Enter" && event.shiftKey) {
                            event.preventDefault();
                            var cursorPosition = this.selectionStart;
                            $(this).val($(this).val().substring(0, cursorPosition) + "\n" + $(this).val().substring(
                                cursorPosition));
                            this.selectionStart = this.selectionEnd = cursorPosition + 1;
                        }
                        adjustTextareaRows();
                    });
                    adjustTextareaRows();
                }
            </script>
        @endif
    </div>
</x-app-layout>

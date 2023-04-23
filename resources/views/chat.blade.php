<x-app-layout>
    <div class="flex h-full max-w-7xl mx-auto py-2">
        <div class="bg-white dark:bg-gray-800 text-white w-64 flex-shrink-0 relative rounded-l-lg overflow-hidden">
            <div class="p-3 h-full overflow-y-auto scrollbar">
                @if (App\Models\LLMs::where('enabled', true)->count() == 0)
                    <div
                        class="flex-1 h-full flex flex-col w-full text-center rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                        No available LLM to chat with<br>
                        Please come back later!
                    </div>
                @else
                    @foreach (App\Models\LLMs::where('enabled', true)->orderby('order')->orderby('created_at')->get() as $LLM)
                        <div class="mb-2 border border-black dark:border-white border-1 rounded-lg">
                            <a href="{{ $LLM->link }}" target="_blank"
                                class="inline-block menu-btn mt-2 w-auto ml-4 mr-auto h-6 transition duration-300 text-blue-800 dark:text-cyan-200">{{ $LLM->name }}</a>
                            <div class="m-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                                <a class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('llm_id') == $LLM->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                                    href="{{ route('new_chat', $LLM->id) }}">
                                    <p class="flex-1 text-center text-gray-700 dark:text-white">New Chat</p>
                                </a>
                            </div>
                            @foreach (App\Models\Chats::where('user_id', Auth::user()->id)->where('llm_id', $LLM->id)->orderby('name')->get() as $chat)
                                <div
                                    class="m-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                                    <a class="flex menu-btn flex text-gray-700 dark:text-white items-center justify-center overflow-y-auto scrollbar w-full h-12 dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('chat_id') == $chat->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                                        href="{{ route('chats', $chat->id) }}">
                                        <p class="flex-1 text-center">{{ $chat->name }}</p>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        @if (!request()->route('chat_id') && !request()->route('llm_id'))
            <div id="histories_hint"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                Select a chat to begin with
            </div>
        @else
            <div id="histories"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden">
                @if (request()->route('chat_id'))
                    <form id="deleteChat" action="{{ route('chat_delete_chat') }}" method="post" class="hidden">
                        @csrf
                        @method('delete')
                        <input name="id"
                            value="{{ App\Models\Chats::findOrFail(request()->route('chat_id'))->id }}" />
                    </form>

                    <form id="editChat" action="{{ route('chat_edit_chat') }}" method="post" class="hidden">
                        @csrf
                        <input name="id"
                            value="{{ App\Models\Chats::findOrFail(request()->route('chat_id'))->id }}" />
                        <input name="new_name" />
                    </form>
                @endif
                <div id="chatHeader" class="bg-gray-300 dark:bg-gray-700 p-4 h-20 text-gray-700 dark:text-white flex">
                    @if (request()->route('llm_id'))
                        <p class="flex items-center">New Chat with
                            {{ App\Models\LLMs::findOrFail(request()->route('llm_id'))->name }}</p>
                    @elseif(request()->route('chat_id'))
                        <p class="flex-1 flex flex-wrap items-center mr-3 overflow-y-auto scrollbar">
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
                            $index = 0;
                        @endphp
                        @foreach (App\Models\Histories::where('chat_id', request()->route('chat_id'))->orderby('created_at', 'desc')->get() as $history)
                            @if (count($tasks) > $index && $tasks[$index] == $history->id)
                                @php
                                    $index += 1;
                                @endphp
                                <div class="flex w-full mt-2 space-x-3">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        <img src="{{ $botimgurl }}">
                                    </div>
                                    <div>
                                        <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm whitespace-pre-line break-all"
                                                id="task_{{ $history->id }}"></p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div id="history_{{ $history->id }}"
                                class="flex w-full mt-2 space-x-3 {{ $history->isbot ? '' : 'ml-auto justify-end' }}">
                                @if ($history->isbot)
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        <img src="{{ $botimgurl }}">
                                    </div>
                                @endif
                                <div>
                                    <div
                                        class="p-3 {{ $history->isbot ? 'bg-gray-300 rounded-r-lg rounded-bl-lg' : 'bg-blue-600 text-white rounded-l-lg rounded-br-lg' }}">
                                        <p class="text-sm whitespace-pre-line break-all">{{ $history->msg }}</p>
                                    </div>
                                </div>
                                @if (!$history->isbot)
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        User
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="bg-gray-300 dark:bg-gray-500 p-4 h-20">
                    @if (request()->route('llm_id'))
                        <form method="post" action="{{ route('chat_create_chat') }}" id="create_chat">
                            <div class="flex">
                                @csrf
                                <input name="llm_id" value="{{ request()->route('llm_id') }}" style="display:none;">
                                <input type="text" placeholder="Enter your text here" name="input"
                                    autocomplete="off"
                                    class="w-full px-4 py-2 text-black dark:text-white placeholder-black dark:placeholder-white bg-gray-200 dark:bg-gray-600 border border-gray-300 focus:outline-none shadow-none border-none focus:ring-0 focus:border-transparent rounded-l-md">
                                <button type="submit"
                                    class="inline-flex items-center justify-center w-12 h-12 bg-blue-400 dark:bg-blue-500 rounded-r-md hover:bg-blue-500 dark:hover:bg-blue-700">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M2.5 9.5L17.5 2.5V17.5L2.5 10.5V9.5Z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    @elseif(request()->route('chat_id'))
                        <form method="post" action="{{ route('chat_request_chat') }}" id="create_chat">
                            <div class="flex">
                                @csrf
                                <input name="chat_id" value="{{ request()->route('chat_id') }}" style="display:none;">
                                <input type="text" placeholder="Enter your text here" name="input"
                                    autocomplete="off"
                                    class="w-full px-4 py-2 text-black dark:text-white placeholder-black dark:placeholder-white bg-gray-200 dark:bg-gray-600 border border-gray-300 focus:outline-none shadow-none border-none focus:ring-0 focus:border-transparent rounded-l-md">
                                <button type="submit"
                                    class="inline-flex items-center justify-center w-12 h-12 bg-blue-400 dark:bg-blue-500 rounded-r-md hover:bg-blue-500 dark:hover:bg-blue-700">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M2.5 9.5L17.5 2.5V17.5L2.5 10.5V9.5Z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            @if (request()->route('chat_id'))
                <script>
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

                    task = new EventSource("{{ route('chat_sse') }}", {
                        withCredentials: false
                    });
                    task.addEventListener('error', error => {
                        task.close();
                    });
                    task.addEventListener('message', event => {
                        data = event.data;
                        const commaIndex = data.indexOf(",");
                        const number = data.slice(0, commaIndex);
                        const msg = data.slice(commaIndex + 1);

                        $('#task_' + number).text($('#task_' + number).text() + (msg ==
                            "" ? "\n" : msg))
                    });
                </script>
            @endif
        @endif
    </div>
</x-app-layout>

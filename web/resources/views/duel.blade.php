<x-app-layout>
    <div id="crypto-modal" data-modal-backdropClasses="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-40"
        tabindex="-1" aria-hidden="true"
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button"
                    class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                    data-modal-hide="crypto-modal">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <!-- Modal header -->
                <div class="px-6 py-4 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-base font-semibold text-gray-900 lg:text-xl dark:text-white">
                        Create Duel Chat
                    </h3>
                </div>
                <!-- Modal body -->
                <form method="post" action="{{ route('duel.create') }}" class="p-6" id="create_duel"
                    onsubmit="return checkForm()">
                    @csrf
                    <input type="hidden" name="limit" value="{{ request()->input('limit') > 0 ? request()->input('limit') : '0' }}">
                    <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Select the LLMs you want to use at
                        the same time.</p>
                    <ul class="my-4 space-y-3">
                        @foreach (App\Models\LLMs::where('enabled', true)->orderby('order')->orderby('created_at')->get() as $LLM)
                            <li>
                                <input type="checkbox" name="llm[]" id="{{ $LLM->access_code }}"
                                    value="{{ $LLM->access_code }}" class="hidden peer">
                                <label for="{{ $LLM->access_code }}"
                                    class="inline-flex items-center justify-between w-full p-2 text-gray-300 bg-white border-2 border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 peer-checked:border-blue-600 hover:text-gray-600 dark:peer-checked:text-gray-300 peer-checked:text-gray-600 hover:bg-gray-50 dark:text-white dark:bg-gray-600 dark:hover:bg-gray-500">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-gray-300 flex items-center justify-center overflow-hidden">
                                            <img src="{{ asset(Storage::url($LLM->image)) }}">
                                        </div>
                                        <div class="pl-2">
                                            <div class="w-full text-lg font-semibold leading-none">{{ $LLM->name }}
                                            </div>
                                            <div class="w-full text-sm leading-none">{{ $LLM->description ? $LLM->description : "This LLM is currently available!" }}</div>
                                        </div>
                                    </div>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                    <div>
                        <div class="border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                            <button type="submit"
                                class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-500 hover:bg-gray-400 transition duration-300">
                                <p class="flex-1 text-center text-gray-700 dark:text-white">Create Chat</p>
                            </button>
                        </div>
                    </div>
                    <span id="create_error" class="font-medium text-sm text-red-800 rounded-lg dark:text-red-400 hidden"
                        role="alert">You must select at least 2 LLMs</span>
                </form>
            </div>
        </div>
    </div>

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
                    <div class="mb-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden"
                        data-modal-target="crypto-modal" data-modal-toggle="crypto-modal">
                        <button
                            class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('llm_id') == 3 ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300">
                            <p class="flex-1 text-center text-gray-700 dark:text-white">Create Chat</p>
                        </button>
                    </div>
                    @foreach (App\Models\DuelChat::leftJoin('chats', 'duelchat.id', '=', 'chats.dcID')->where('chats.user_id', Auth::user()->id)->orderby('counts', 'desc')->select('duelchat.*', DB::raw('array_agg(chats.llm_id ORDER BY chats.id) as identifier'), DB::raw('count(chats.id) as counts'))->groupBy('duelchat.id')->get()->groupBy('identifier') as $DC)
                        @if (App\Models\Chats::join('llms', 'llms.id', '=', 'llm_id')->where('dcID', $DC->first()->id)->get()->where('enabled', false)->count() == 0)
                            <div class="mb-2 border border-black dark:border-white border-1 rounded-lg">
                                <div class="flex px-2 py-3 border-b border-black dark:border-white">
                                    @foreach (App\Models\Chats::join('llms', 'llms.id', '=', 'llm_id')->where('user_id', Auth::user()->id)->where('dcID', $DC->first()->id)->orderby('llm_id')->get() as $chat)
                                        <div
                                            class="mx-1 flex-shrink-0 h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-gray-300 flex items-center justify-center overflow-hidden">
                                            <a href="{{ $chat->link }}" target="_blank"><img
                                                    data-tooltip-target="llm_{{ $chat->llm_id }}"
                                                    data-tooltip-placement="top"
                                                    src="{{ asset(Storage::url($chat->image)) }}"></a>
                                            <div id="llm_{{ $chat->llm_id }}" role="tooltip"
                                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                                                {{ $chat->name }}
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="max-h-[182px] overflow-y-auto scrollbar">
                                @foreach ($DC as $dc)
                                    <div
                                        class="m-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                                        <a class="flex menu-btn flex text-gray-700 dark:text-white w-full h-12 overflow-y-auto scrollbar dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('duel_id') == $dc->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                                            href="{{ route('duel.chat', $dc->id) . (request()->input('limit') > 0 ? '?limit=' . request()->input('limit') : '') }}">
                                            <p class="flex-1 flex items-center my-auto justify-center text-center leading-none self-baseline">
                                                {{ $dc->name }}</p>
                                        </a>
                                    </div>
                                @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
        @if (!request()->route('duel_id'))
            <div id="histories_hint"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                Select a chatroom to begin with
            </div>
        @else
            <div id="histories"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden">
                <form id="deleteChat" action="{{ route('duel.delete') }}" method="post" class="hidden">
                    @csrf
                    @method('delete')
                    <input name="id" value="{{ App\Models\DuelChat::findOrFail(request()->route('duel_id'))->id }}" />
                    <input type="hidden" name="limit" value="{{ request()->input('limit') > 0 ? request()->input('limit') : '0' }}">
                </form>

                <form id="editChat" action="{{ route('duel.edit') }}" method="post" class="hidden">
                    @csrf
                    <input name="id"
                        value="{{ App\Models\DuelChat::findOrFail(request()->route('duel_id'))->id }}" />
                    <input name="new_name" />
                    <input type="hidden" name="limit" value="{{ request()->input('limit') > 0 ? request()->input('limit') : '0' }}">
                </form>
                <div id="chatHeader" class="bg-gray-300 dark:bg-gray-700 p-4 h-20 text-gray-700 dark:text-white flex">
                    <p class="flex-1 flex flex-wrap items-center mr-3 overflow-x-hidden overflow-y-auto scrollbar">
                        {{ App\Models\DuelChat::findOrFail(request()->route('duel_id'))->name }}</p>

                    <div class="flex">
                        <div
                            class="flex items-center mr-1 max-w-[144px] min-w-[] overflow-x-auto overflow-y-hidden scrollbar scrollbar-3">
                            @foreach (App\Models\Chats::join('llms', 'llms.id', '=', 'llm_id')->where('user_id', Auth::user()->id)->where('dcID', request()->route('duel_id'))->orderby('llm_id')->get() as $chat)
                                <div
                                    class="mx-1 flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                    <a><img data-tooltip-target="llm_{{ $chat->llm_id }}_toggle"
                                            data-tooltip-placement="top"
                                            src="{{ asset(Storage::url($chat->image)) }}"></a>
                                    <div id="llm_{{ $chat->llm_id }}_toggle" role="tooltip"
                                        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                                        {{ $chat->name }}
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                    <div id="llm_{{ $chat->llm_id }}_chat" role="tooltip"
                                        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                                        {{ $chat->name }}
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
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
                </div>
                <div id="chatroom" class="flex-1 p-4 overflow-y-auto flex flex-col-reverse scrollbar">
                    @php
                        $tasks = \Illuminate\Support\Facades\Redis::lrange('usertask_' . Auth::user()->id, 0, -1);
                    @endphp
                    @foreach (App\Models\Chats::join('histories', 'chats.id', '=', 'histories.chat_id')->join('llms', 'llms.id', '=', 'chats.llm_id')->where('isbot', true)->whereIn('chats.id', App\Models\Chats::where('dcID', request()->route('duel_id'))->pluck('id'))->select('chats.id as chat_id', 'histories.id as history_id', 'chats.llm_id as llm_id', 'histories.created_at as created_at', 'histories.msg as msg', 'histories.isbot as isbot', 'llms.image as image', 'llms.name as name')->union(
            App\Models\Chats::join('histories', 'chats.id', '=', 'histories.chat_id')->join('llms', 'llms.id', '=', 'chats.llm_id')->where('isbot', false)->where(
                    'chats.id',
                    App\Models\Chats::where('dcID', request()->route('duel_id'))->get()->first()->id,
                )->select('chats.id as chat_id', 'histories.id as history_id', 'chats.llm_id as llm_id', 'histories.created_at as created_at', 'histories.msg as msg', 'histories.isbot as isbot', 'llms.image as image', 'llms.name as name'),
        )->get()->sortByDesc(function ($chat) {
            return [$chat->created_at, $chat->llm_id, -$chat->history_id];
        }) as $history)
                        @if (in_array($history->history_id, $tasks))
                            <div class="flex w-full mt-2 space-x-3">
                                <div data-tooltip-target="llm_{{ $history->llm_id }}_chat"
                                    data-tooltip-placement="top"
                                    class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                    <img src="{{ asset(Storage::url($history->image)) }}">
                                </div>
                                <div>
                                    <div {{ request()->input('limit') > 0 ? 'style=max-height:' . 0.75 + 0.75 + 0.875 * 1.25 * request()->input('limit') . 'rem' : '' }}
                                        class="flex flex-col-reverse scrollbar overflow-y-auto p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                        <p class="text-sm whitespace-pre-line break-all"
                                            id="task_{{ $history->history_id }}">{{ $history->msg }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div id="history_{{ $history->history_id }}"
                                class="flex w-full mt-2 space-x-3 {{ $history->isbot ? '' : 'ml-auto justify-end' }}">
                                @if ($history->isbot)
                                    <div data-tooltip-target="llm_{{ $history->llm_id }}_chat"
                                        data-tooltip-placement="top"
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
                                        <img src="{{ asset(Storage::url($history->image)) }}">
                                    </div>
                                @endif
                                <div>
                                    <div {{ request()->input('limit') > 0 ? 'style=max-height:' . 0.75 + 0.75 + 0.875 * 1.25 * request()->input('limit') . 'rem' : '' }}
                                        class="scrollbar overflow-y-auto p-3 {{ $history->isbot ? 'bg-gray-300 rounded-r-lg rounded-bl-lg' : 'bg-blue-600 text-white rounded-l-lg rounded-br-lg' }}">
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
                        @endif
                    @endforeach
                </div>
                <div class="bg-gray-300 dark:bg-gray-500 p-4 h-20">
                    <form method="post"
                        action="{{ route('duel.request') . (request()->input('limit') > 0 ? '' : '?limit=' . request()->input('limit')) }}"
                        id="create_chat">
                        <div class="flex">
                            @csrf
                            <input name="duel_id" value="{{ request()->route('duel_id') }}" style="display:none;">
                            <input type="text" placeholder="Enter your text here" name="input" id="chat_input"
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
                        <input type="hidden" name="limit" value="{{ request()->input('limit') > 0 ? request()->input('limit') : '0' }}">
                    </form>
                </div>
            </div>
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

                task = new EventSource("{{ route('chat.sse') }}", {
                    withCredentials: false
                });
                task.addEventListener('error', error => {
                    task.close();
                });
                task.addEventListener('message', event => {
                    data = event.data.replace(/\[NEWLINEPLACEHOLDERUWU\]/g, "\n");
                    console.log(data);
                    const commaIndex = data.indexOf(",");
                    const number = data.slice(0, commaIndex);
                    const msg = data.slice(commaIndex + 1);
                    $('#task_' + number).text(msg);
                });
                $("#chat_input").focus();
            </script>
        @endif
        <script>
            function checkForm() {
                if ($("#create_duel input[name='llm[]']:checked").length > 1) {
                    return true;
                } else {
                    $("#create_error").show().delay(3000).fadeOut();
                    return false;
                }
            }
        </script>
    </div>
</x-app-layout>

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

    @if (request()->route('llm_id'))
        <div id="importModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
            class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ __('Import Chat') }}
                        </h3>
                        <button type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="importModal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>

                    <!-- Modal body -->
                    <div class="p-6 flex flex-col justify-center">
                        <label for="import_file_input"
                            class="mx-auto bg-green-500 hover:bg-green-600 px-3 py-2 rounded cursor-pointer text-white">{{ __('Import from file') }}</label>
                        <hr class="my-4 border-black dark:border-gray-600" />
                        <form method="post" action="{{route('chat.import')}}">
                            @csrf
                            <input name="llm_id" value="{{request()->route('llm_id')}}" style="display:none;">
                            <textarea name="history" id="import_json" rows="5" max-rows="15" oninput="adjustTextareaRows(this)"
                            placeholder="{{ __('You may drop your file here as well...') }}"
                            class="w-full pl-4 pr-12 py-2 rounded text-black scrollbar dark:text-white placeholder-black dark:placeholder-white bg-gray-200 dark:bg-gray-600 border border-gray-300 focus:outline-none shadow-none border-none focus:ring-0 focus:border-transparent rounded-l-md resize-none"></textarea>
                        </form>

                        <input id="import_file_input" type='file' hidden>
                    </div>
                    <script>
                        $(document).ready(function() {
                            // Handle the file input change event
                            $('#import_file_input').on('change', function() {
                                loadFile($(this)[0], '#import_json')
                            });
                            $('#import_json').on('drop', function(e) {
                                e.preventDefault();
                                e.stopPropagation();

                                loadFile(e.originalEvent.dataTransfer, '#import_json');
                            });

                        });

                        function loadFile(fileInput, input) {
                            const file = fileInput.files[0];
                            if (file) {
                                if (file.type === 'text/plain' || file.type === 'application/json') {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        $(input).val(e.target.result);
                                        adjustTextareaRows(input);
                                    };
                                    reader.readAsText(file);
                                } else {
                                    alert('Only .txt or ..json files are accepted.');
                                }
                            }
                        }
                    </script>
                    <!-- Modal footer -->
                    <div
                        class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b dark:border-gray-600">
                        <button data-modal-hide="importModal" type="button" onclick="$(this).parent().parent().find('form').submit()"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">{{ __('Import') }}</button>
                        <button data-modal-hide="importModal" type="button"
                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">{{ __('Cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @elseif (request()->route('chat_id'))
        <div id="exportModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
            class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ __('Export Chat') }}
                        </h3>
                        <button type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="exportModal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>

                    <!-- Modal body -->
                    <div class="p-6 space-y-6">
                        <textarea id="import_json" rows="15" readonly
                            class="w-full pl-4 pr-12 py-2 rounded text-black scrollbar dark:text-white placeholder-black dark:placeholder-white bg-gray-200 dark:bg-gray-600 border border-gray-300 focus:outline-none shadow-none border-none focus:ring-0 focus:border-transparent rounded-l-md resize-none">{{ json_encode(App\Models\Histories::where('chat_id', request()->route('chat_id'))->orderby('created_at')->orderby('id', 'desc')->select('msg', 'isbot', 'chained')->get()->toArray(),JSON_PRETTY_PRINT) }}</textarea>
                        <a id="download_holder" style="display:none;"
                            download="{{ App\Models\Chats::find(request()->route('chat_id'))->name . '.json' }}"></a>
                    </div>
                    <!-- Modal footer -->
                    <div
                        class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b dark:border-gray-600">
                        <button
                            onclick='$("#download_holder").attr("href",window.URL.createObjectURL(new Blob([$("#import_json").val()], { type: "text/plain" }))); $("#download_holder")[0].click();'
                            class="bg-green-500 hover:bg-green-600 px-3 py-2 rounded cursor-pointer text-white">{{ __('Download') }}</button>

                    </div>
                </div>
            </div>
        </div>
    @endif
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
                                        <p class="flex-1 text-center text-gray-700 dark:text-white">
                                            {{ __('New Chat') }}
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
                        <p class="flex-1 flex flex-wrap items-center mr-3 overflow-y-auto overflow-x-hidden scrollbar">
                            {{ __('New Chat with') }}
                            {{ App\Models\LLMs::findOrFail(request()->route('llm_id'))->name }}</p>
                        <div class="flex">
                            <button onclick="import_chat()" data-modal-target="importModal"
                                data-modal-toggle="importModal"
                                class="bg-green-500 ml-3 hover:bg-green-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                                <i class="fas fa-file-import"></i>
                            </button>
                        </div>
                    @elseif(request()->route('chat_id'))
                        <p class="flex-1 flex flex-wrap items-center mr-3 overflow-y-auto overflow-x-hidden scrollbar"
                            style='word-break:break-word'>
                            {{ App\Models\Chats::findOrFail(request()->route('chat_id'))->name }}</p>

                        <div class="flex">
                            <button onclick="export_chat()" data-modal-target="exportModal"
                                data-modal-toggle="exportModal"
                                class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                                <i class="fas fa-share-alt"></i>
                            </button>
                            <button onclick="saveChat()"
                                class="bg-green-500 ml-3 hover:bg-green-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center hidden">
                                <i class="fas fa-save"></i>
                            </button>
                            <button onclick="editChat()"
                                class="bg-orange-500 ml-3 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
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
                            $img = App\Models\LLMs::findOrFail(App\Models\Chats::findOrFail(request()->route('chat_id'))->llm_id)->image;
                            $botimgurl = strpos($img, 'data:image/png;base64') === 0 ? $img : asset(Storage::url($img));
                            $tasks = \Illuminate\Support\Facades\Redis::lrange('usertask_' . Auth::user()->id, 0, -1);
                        @endphp
                        @foreach (App\Models\Histories::where('chat_id', request()->route('chat_id'))->leftjoin('feedback', 'history_id', '=', 'histories.id')->select(['histories.*', 'feedback.nice', 'feedback.detail', 'feedback.flags'])->orderby('histories.created_at', 'desc')->orderby('histories.id')->get() as $history)
                            @if (in_array($history->id, $tasks))
                                <div class="flex w-full mt-2 space-x-3">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
                                        <img src="{{ $botimgurl }}">
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm whitespace-pre-line break-words"
                                                id="task_{{ $history->id }}">{{ __($history->msg) }}</p>
                                            <div class="flex space-x-1 show-on-finished" style="display:none;">
                                                <button class="flex text-black hover:bg-gray-400 p-2 rounded-lg"
                                                    onclick="copytext($(this).parent().parent().children()[0])">
                                                    <svg stroke="currentColor" fill="none" stroke-width="2"
                                                        viewBox="0 0 24 24" stroke-linecap="round"
                                                        stroke-linejoin="round" class="icon-sm" height="1em"
                                                        width="1em" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2">
                                                        </path>
                                                        <rect x="8" y="2" width="8" height="4"
                                                            rx="1" ry="1">
                                                        </rect>
                                                    </svg>
                                                    <svg stroke="currentColor" fill="none" stroke-width="2"
                                                        viewBox="0 0 24 24" stroke-linecap="round"
                                                        stroke-linejoin="round" class="icon-sm" style="display:none;"
                                                        height="1em" width="1em"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                </button>
                                                <button
                                                    class="flex text-black hover:bg-gray-400 p-2 rounded-lg {{ $history->nice === true ? 'text-green-600' : 'text-black' }}"
                                                    data-modal-target="feedback" data-modal-toggle="feedback"
                                                    onclick="feedback({{ $history->id }},1,this,{!! htmlspecialchars(
                                                        json_encode(['detail' => $history->detail, 'flags' => $history->flags, 'nice' => $history->nice]),
                                                    ) !!});">
                                                    <svg stroke="currentColor" fill="none" stroke-width="2"
                                                        viewBox="0 0 24 24" stroke-linecap="round"
                                                        stroke-linejoin="round" class="icon-sm" height="1em"
                                                        width="1em" xmlns="http://www.w3.org/2000/svg">
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
                                                    <svg stroke="currentColor" fill="none" stroke-width="2"
                                                        viewBox="0 0 24 24" stroke-linecap="round"
                                                        stroke-linejoin="round" class="icon-sm" height="1em"
                                                        width="1em" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div id="history_{{ $history->id }}"
                                    class="flex w-full mt-2 space-x-3 {{ $history->isbot ? '' : 'ml-auto justify-end' }}">
                                    @if ($history->isbot)
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
                                            <img src="{{ $botimgurl }}">
                                        </div>
                                    @endif
                                    <div class="overflow-hidden">
                                        <div
                                            class="p-3 {{ $history->isbot ? 'bg-gray-300 rounded-r-lg rounded-bl-lg' : 'bg-blue-600 text-white rounded-l-lg rounded-br-lg' }}">
                                            <p class="text-sm whitespace-pre-line break-words">{{ __($history->msg) }}
                                            </p>
                                            @if ($history->isbot)
                                                <div class="flex space-x-1">
                                                    <button class="flex text-black hover:bg-gray-400 p-2 rounded-lg"
                                                        onclick="copytext($(this).parent().parent().children()[0])">
                                                        <svg stroke="currentColor" fill="none" stroke-width="2"
                                                            viewBox="0 0 24 24" stroke-linecap="round"
                                                            stroke-linejoin="round" class="icon-sm" height="1em"
                                                            width="1em" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2">
                                                            </path>
                                                            <rect x="8" y="2" width="8" height="4"
                                                                rx="1" ry="1">
                                                            </rect>
                                                        </svg>
                                                        <svg stroke="currentColor" fill="none" stroke-width="2"
                                                            viewBox="0 0 24 24" stroke-linecap="round"
                                                            stroke-linejoin="round" class="icon-sm"
                                                            style="display:none;" height="1em" width="1em"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                    </button>
                                                    <button
                                                        class="flex hover:bg-gray-400 p-2 rounded-lg {{ $history->nice === true ? 'text-green-600' : 'text-black' }}"
                                                        data-modal-target="feedback" data-modal-toggle="feedback"
                                                        onclick="feedback({{ $history->id }},1,this,{!! htmlspecialchars(
                                                            json_encode(['detail' => $history->detail, 'flags' => $history->flags, 'nice' => $history->nice]),
                                                        ) !!});">
                                                        <svg stroke="currentColor" fill="none" stroke-width="2"
                                                            viewBox="0 0 24 24" stroke-linecap="round"
                                                            stroke-linejoin="round" class="icon-sm" height="1em"
                                                            width="1em" xmlns="http://www.w3.org/2000/svg">
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
                                                        <svg stroke="currentColor" fill="none" stroke-width="2"
                                                            viewBox="0 0 24 24" stroke-linecap="round"
                                                            stroke-linejoin="round" class="icon-sm" height="1em"
                                                            width="1em" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>
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
                        @endforeach
                    @elseif(request()->route('llm_id') &&
                            in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['doc_qa', 'doc_qa_b5']))
                        <p class="m-auto text-white">{!! __('A document is required in order to use this LLM, <br>Please upload a file first.') !!}</p>
                    @elseif(request()->route('llm_id') &&
                            in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['web_qa', 'web_qa_b5']))
                        <div style="display:none;"
                            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                            id="url_only_alert" role="alert">
                            <span
                                class="block sm:inline">{{ __('The first message for this LLM allows URL only!') }}</span>
                        </div>
                    @endif
                </div>
                <div
                    class="bg-gray-300 dark:bg-gray-500 p-4 flex flex-col overflow-y-hidden {{ request()->route('llm_id') && in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['doc_qa', 'doc_qa_b5']) ? 'overflow-x-hidden' : '' }}">
                    @if (request()->route('llm_id') &&
                            in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['doc_qa', 'doc_qa_b5']))
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
                                <input name="llm_id" value="{{ request()->route('llm_id') }}"
                                    style="display:none;">
                                <textarea tabindex="0" data-id="root"
                                    placeholder="{{ request()->route('llm_id') && in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['web_qa', 'web_qa_b5']) ? __('An URL is required to create a chatroom') : __('Send a message') }}"
                                    rows="1" max-rows="5" oninput="adjustTextareaRows(this)" id="chat_input" name="input"
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
                                <button type="button" onclick="chain_toggle()" id="chain_btn"
                                    class="whitespace-nowrap my-auto text-white mr-3 {{ \Session::get('chained') ? 'bg-green-500 hover:bg-green-600' : 'bg-red-600 hover:bg-red-700' }} px-3 py-2 rounded">{{ \Session::get('chained') ? __('Chained') : __('Unchain') }}</button>
                                <textarea tabindex="0" data-id="root" placeholder="{{ __('Send a message') }}" rows="1" max-rows="5"
                                    oninput="adjustTextareaRows(this)" id="chat_input" name="input" readonly
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
                <div id="feedback" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
                    class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                    <div class="relative w-full max-w-2xl max-h-full">
                        <!-- Modal content -->
                        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                            <!-- Modal header -->
                            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                                <div style="display:none;"
                                    class="mr-4 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:h-10 sm:w-10 bg-green-100">
                                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round" class="icon-lg text-green-700"
                                        aria-hidden="true" height="1em" width="1em"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
                                        </path>
                                    </svg>
                                </div>
                                <div style="display:none;"
                                    class="mr-4 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:h-10 sm:w-10 bg-red-100">
                                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round" class="icon-lg text-red-600"
                                        aria-hidden="true" height="1em" width="1em"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="text-xl my-auto font-semibold text-gray-900 dark:text-white">
                                    {{ __('Provide feedback') }}
                                </h3>
                                <button type="button"
                                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                    data-modal-hide="feedback">
                                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 14 14">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                    </svg>
                                    <span class="sr-only">Close modal</span>
                                </button>
                            </div>
                            <!-- Modal body -->
                            <div class="p-6">
                                <form id="feedback_form" action="{{ route('chat.feedback') }}" method="post">
                                    @csrf
                                    <input name="history_id" style="display:none;">
                                    <input name="type" style="display:none;">

                                    <textarea rows="1" maxlength="4096" max-rows="5" name="feedbacks" id="feedbacks"
                                        class="w-full resize-none" oninput="adjustTextareaRows(this)"></textarea>
                                    <div>
                                        <input name="feedback[]" id="feedback_1" type="checkbox" value="unsafe">
                                        <label for="feedback_1"
                                            class="ml-2 text-sm font-medium text-gray-400 dark:text-gray-300">{{ __('Unsafe') }}</label>
                                    </div>
                                    <div>
                                        <input name="feedback[]" id="feedback_2" type="checkbox" value="incorrect">
                                        <label for="feedback_2"
                                            class="ml-2 text-sm font-medium text-gray-400 dark:text-gray-300">{{ __('Incorrect') }}</label>
                                    </div>
                                    <div>
                                        <input name="feedback[]" id="feedback_3" type="checkbox" value="inrelvent">
                                        <label for="feedback_3"
                                            class="ml-2 text-sm font-medium text-gray-400 dark:text-gray-300">{{ __('Inrelvent') }}</label>
                                    </div>
                                    <div>
                                        <input name="feedback[]" id="feedback_4" type="checkbox" value="language">
                                        <label for="feedback_4"
                                            class="ml-2 text-sm font-medium text-gray-400 dark:text-gray-300">{{ __('In Wrong Language') }}</label>
                                    </div>
                                    <div class="flex justify-end">
                                        <button data-modal-hide="feedback" type="submit"
                                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">{{ __('Submit feedback') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
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

                    function copytext(node) {
                        var range = document.createRange();
                        range.selectNode(node);
                        window.getSelection().removeAllRanges();
                        window.getSelection().addRange(range);
                        try {
                            // Attempt to copy the selected text to the clipboard
                            document.execCommand("copy");
                        } catch (err) {
                            console.log("Copy not supported!")
                        }
                        window.getSelection().removeAllRanges();

                        $(node).parent().children().eq(1).children().eq(0).children().eq(0).hide();
                        $(node).parent().children().eq(1).children().eq(0).children().eq(1).show();
                        setTimeout(function() {
                            $(node).parent().children().eq(1).children().eq(0).children().eq(0).show();
                            $(node).parent().children().eq(1).children().eq(0).children().eq(1).hide();
                        }, 3000);

                    }

                    function feedback(id, type, obj, data) {
                        $(obj).parent().find("button:not(:first)").removeClass("bg-gray-400")
                        adjustTextareaRows($("#feedbacks"));
                        // clear form
                        $("#feedback_form input:not(:first), #feedback_form textarea").each(function() {
                            if ($(this).is(":checkbox")) {
                                $(this).prop("checked", false);
                            } else {
                                $(this).val("");
                            }
                        });
                        $("#feedback_form input:eq(1)").val(id) //History id
                        $("#feedback_form input:eq(2)").val(type) //feedback type
                        $("#feedback svg").eq(type - 1).parent().show();
                        $(obj).parent().find(">button:not(:first)").removeClass("text-green-600 text-red-600").addClass("text-black")
                        $(obj).toggleClass("text-black " + (type == 1 ? "text-green-600" : "text-red-600"))
                        $("#feedback svg").eq(type % 2).parent().hide();
                        if (type == 1) {
                            //Good
                            $("#feedback_form >div:not(:last)").hide()
                            $("#feedback_form textarea").attr("placeholder", "{{ __('What do you like about the response?') }}")
                        } else if (type == 2) {
                            //Bad
                            $("#feedback_form >div").show()
                            $("#feedback_form textarea").attr("placeholder",
                                "{{ __('What was the problem with this response? How can it be improved?') }}")
                        }
                        if (data) {
                            if (data['nice'] === true && type == 1) {
                                if (data["detail"]) {
                                    $("#feedback_form textarea").val(data["detail"]);
                                }
                            } else if (data['nice'] === false && type == 2) {
                                if (data["detail"]) {
                                    $("#feedback_form textarea").val(data["detail"]);
                                }
                                if (type == 2 && data["flags"]) {
                                    data["flags"] = JSON.parse(data["flags"])
                                    data["flags"] = data["flags"].map(f => {
                                        return ["unsafe", "incorrect", "inrelvent", "language"].indexOf(f) + 1
                                    })
                                    data["flags"].forEach(i => {
                                        $("#feedback_" + i).click();
                                    })
                                }
                            } else {
                                $.post("{{ route('chat.feedback') }}", {
                                    type: type,
                                    history_id: id,
                                    init: true,
                                    _token: $("input[name='_token']").val()
                                })
                            }

                        }
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

                    function export_chat() {

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
                            adjustTextareaRows($("#chat_input"))
                            $(".show-on-finished").attr("style", "")
                            $("#import_json").val($("#import_json").val().replace('* ...thinking... *', stringToUnicode($(
                                "#chatroom p:eq(0)").text().trim())))
                        } else {
                            data = JSON.parse(event.data)
                            number = parseInt(data["history_id"]);
                            msg = data["msg"];
                            msg = msg.replace(
                                "[Oops, the LLM returned empty message, please try again later or report to admins!]",
                                "{{ __('[Oops, the LLM returned empty message, please try again later or report to admins!]') }}"
                            )
                            msg = msg.replace("[Sorry, something is broken, please try again later!]",
                                "{{ __('[Sorry, something is broken, please try again later!]') }}")
                            msg = msg.replace(
                                "[Sorry, There're no machine to process this LLM right now! Please report to Admin or retry later!]",
                                "{{ __('[Sorry, There\'re no machine to process this LLM right now! Please report to Admin or retry later!]') }}"
                            )
                            $('#task_' + number).text(msg);
                        }
                    });
                </script>
            @endif
            <script>
                function isValidURL(url) {
                    // Regular expression for a simple URL pattern (you can make it more complex if needed)
                    var urlPattern = /^(https?|ftp):\/\/(-\.)?([^\s/?\.#-]+\.?)+([^\s]*)$/;
                    return urlPattern.test(url);
                }

                function adjustTextareaRows(obj) {
                    obj = $(obj)
                    if (obj.length) {
                        const textarea = obj;
                        const maxRows = parseInt(textarea.attr('max-rows')) || 5;
                        const lineHeight = parseInt(textarea.css('line-height'));

                        textarea.attr('rows', 1);

                        const contentHeight = textarea[0].scrollHeight;
                        const rowsToDisplay = Math.floor(contentHeight / lineHeight);

                        textarea.attr('rows', Math.min(maxRows, rowsToDisplay));
                    }
                }

                function uploadcheck() {
                    if ($("#upload")[0].files && $("#upload")[0].files.length > 0 && $("#upload")[0].files[0].size <= 10 * 1024 *
                        1024) {
                        $("#upload").parent().submit();
                    } else {
                        $("#upload_btn").text('{{ __('File Too Large') }}')
                        $("#upload_btn").toggleClass("bg-green-500 hover:bg-green-600 bg-red-600 hover:bg-red-700")
                        $("#upload").val("");


                        setTimeout(function() {
                            $("#upload_btn").text('{{ __('Upload file') }}')
                            $("#upload_btn").toggleClass("bg-green-500 hover:bg-green-600 bg-red-600 hover:bg-red-700")
                        }, 3000);
                    }
                }

                if ($("#chat_input")) {
                    $("#chat_input").focus();

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
                        adjustTextareaRows($("#chat_input"));
                    });
                    adjustTextareaRows($("#chat_input"));
                }

                function stringToUnicode(inputString) {
                    return inputString.replace(/./g, function(char) {
                        return '\\u' + char.charCodeAt(0).toString(16).padStart(4, '0');
                    });
                }
                @if (request()->route('llm_id'))
                    @if (in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['web_qa', 'web_qa_b5']))
                        if ($("#prompt_area")) {
                            $("#prompt_area").on("submit", function(event) {
                                event.preventDefault();
                                if (isValidURL($("#chat_input").val().trim())) {
                                    $("#prompt_area")[0].submit()
                                } else {
                                    $("#url_only_alert").fadeIn();
                                    setTimeout(function() {
                                        $("#url_only_alert").fadeOut();
                                    }, 3000);
                                }
                            })
                        }
                    @endif
                    function import_chat() {

                    }
                @endif
            </script>
        @endif
    </div>
</x-app-layout>

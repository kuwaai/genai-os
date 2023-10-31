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

    @if (!request()->route('duel_id'))
        <div id="create-model-modal"
            data-modal-backdropClasses="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-40" tabindex="-1"
            aria-hidden="true"
            class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-md max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                    <button type="button"
                        class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                        data-modal-hide="create-model-modal">
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
                            {{ __('Create Duel Chat') }}
                        </h3>
                    </div>
                    <!-- Modal body -->
                    <form method="post" action="{{ route('duel.create') }}" class="p-6" id="create_duel"
                        onsubmit="return checkForm()">
                        @csrf
                        <input type="hidden" name="limit"
                            value="{{ request()->input('limit') > 0 ? request()->input('limit') : '0' }}">
                        <p class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            {{ __('Select the LLMs you want to use at the same time.') }}</p>
                        <ul class="my-4 space-y-3">
                            @foreach ($result as $LLM)
                                <li>
                                    <input type="checkbox" name="llm[]" id="{{ $LLM->access_code }}"
                                        value="{{ $LLM->access_code }}" class="hidden peer">
                                    <label for="{{ $LLM->access_code }}"
                                        class="inline-flex items-center justify-between w-full p-2 text-gray-400 bg-white border-2 border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 peer-checked:border-blue-600 hover:text-gray-600 dark:peer-checked:text-gray-300 peer-checked:text-gray-600 hover:bg-gray-50 dark:text-white dark:bg-gray-600 dark:hover:bg-gray-500">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-black flex items-center justify-center overflow-hidden">
                                                <img
                                                    src="{{ strpos($LLM->image, 'data:image/png;base64') === 0 ? $LLM->image : asset(Storage::url($LLM->image)) }}">
                                            </div>
                                            <div class="pl-2">
                                                <div class="w-full text-lg font-semibold leading-none">
                                                    {{ $LLM->name }}
                                                </div>
                                                <div class="w-full text-sm leading-none">
                                                    {{ $LLM->description ? $LLM->description : __('This LLM is currently available!') }}
                                                </div>
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
                                    <p class="flex-1 text-center text-gray-700 dark:text-white">{{ __('Create Chat') }}
                                    </p>
                                </button>
                            </div>
                        </div>
                        <span id="create_error"
                            class="font-medium text-sm text-red-800 rounded-lg dark:text-red-400 hidden"
                            role="alert">{{ __('You must select at least 2 LLMs') }}</span>
                    </form>
                </div>
            </div>
        </div>
    @endif
    <div class="flex h-full max-w-7xl mx-auto py-2">
        <div class="bg-white dark:bg-gray-800 text-white w-64 flex-shrink-0 relative rounded-l-lg overflow-hidden">
            <div class="p-3 {{ request()->route('duel_id') ? 'flex flex-col' : '' }} h-full overflow-y-auto scrollbar">
                @if ($result->count() == 0)
                    <div
                        class="flex-1 h-full flex flex-col w-full text-center rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                        {!! __('No available LLM to chat with<br>Please come back later!') !!}
                    </div>
                @else
                    @if (request()->route('duel_id'))
                        <a href="{{ route('duel.home') }}"
                            class="text-center cursor-pointer hover:bg-gray-200 text-black dark:text-white dark:hover:bg-gray-500 rounded p-2 mb-2">←
                            {{ __('Return to Menu') }}</a>
                    @else
                        <div class="mb-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden"
                            data-modal-target="create-model-modal" data-modal-toggle="create-model-modal">
                            <button
                                class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('llm_id') == 3 ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300">
                                <p class="flex-1 text-center text-gray-700 dark:text-white">{{ __('Create Chat') }}</p>
                            </button>
                        </div>
                    @endif
                    @if (request()->route('duel_id'))
                        @php
                            $DC = App\Models\DuelChat::leftJoin('chats', 'duelchat.id', '=', 'chats.dcID')
                                ->where('chats.user_id', Auth::user()->id)
                                ->orderby('counts', 'desc')
                                ->select('duelchat.*', DB::raw('array_agg(chats.llm_id ORDER BY chats.id) as identifier'), DB::raw('count(chats.id) as counts'))
                                ->groupBy('duelchat.id')
                                ->get()
                                ->groupBy('identifier');
                            $DC =
                                $DC[
                                    collect(Illuminate\Support\Arr::flatten($DC->toarray(), 1))
                                        ->where('id', '=', request()->route('duel_id'))
                                        ->first()['identifier']
                                ];
                        @endphp
                        @if (array_diff(explode(',', trim($DC->first()->identifier, '{}')), $result->pluck('model_id')->toArray()) == [])
                            <div class="mb-2 border border-black dark:border-white border-1 rounded-lg">
                                <div
                                    class="flex px-2 scrollbar scrollbar-3 overflow-x-auto py-3 border-b border-black dark:border-white">
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
                                    <!--<div class="m-2 border border-green-400 border-1 rounded-lg overflow-hidden">
                                        <a class="flex menu-btn flex text-green-400 w-full h-12 overflow-y-auto scrollbar dark:hover:bg-gray-700 hover:bg-gray-200 transition duration-300"
                                            href="">
                                            <p
                                                class="flex-1 flex items-center my-auto justify-center text-center leading-none self-baseline">
                                                新聊天室</p>
                                        </a>
                                    </div>-->
                                    @foreach ($DC as $dc)
                                        <div
                                            class="m-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                                            <a class="flex menu-btn flex text-gray-700 dark:text-white w-full h-12 overflow-y-auto scrollbar dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('duel_id') == $dc->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                                                href="{{ route('duel.chat', $dc->id) . (request()->input('limit') > 0 ? '?limit=' . request()->input('limit') : '') }}">
                                                <p
                                                    class="flex-1 flex items-center my-auto justify-center text-center leading-none self-baseline">
                                                    {{ $dc->name }}</p>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        @foreach (App\Models\DuelChat::leftJoin('chats', 'duelchat.id', '=', 'chats.dcID')->where('chats.user_id', Auth::user()->id)->orderby('counts', 'desc')->select('duelchat.*', DB::raw('array_agg(chats.llm_id ORDER BY chats.id) as identifier'), DB::raw('count(chats.id) as counts'))->groupBy('duelchat.id')->get()->groupBy('identifier') as $DC)
                            @if (array_diff(explode(',', trim($DC->first()->identifier, '{}')), $result->pluck('model_id')->toArray()) == [])
                                <div class="mb-2 border border-black dark:border-white border-1 rounded-lg">
                                    <div
                                        class="flex px-2 scrollbar scrollbar-3 overflow-x-auto py-3 border-b border-black dark:border-white">
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
                                                    <p
                                                        class="flex-1 flex items-center my-auto justify-center text-center leading-none self-baseline">
                                                        {{ $dc->name }}</p>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                    @endif
                @endif
            </div>
        </div>
        @if (!request()->route('duel_id'))
            <div id="histories_hint"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                {{ __('Select a chatroom to begin with') }}
            </div>
        @else<div id="feedback" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
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
            <div id="histories"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden">
                <form id="deleteChat" action="{{ route('duel.delete') }}" method="post" class="hidden">
                    @csrf
                    @method('delete')
                    <input name="id"
                        value="{{ App\Models\DuelChat::findOrFail(request()->route('duel_id'))->id }}" />
                    <input type="hidden" name="limit"
                        value="{{ request()->input('limit') > 0 ? request()->input('limit') : '0' }}">
                </form>

                <form id="editChat" action="{{ route('duel.edit') }}" method="post" class="hidden">
                    @csrf
                    <input name="id"
                        value="{{ App\Models\DuelChat::findOrFail(request()->route('duel_id'))->id }}" />
                    <input name="new_name" />
                    <input type="hidden" name="limit"
                        value="{{ request()->input('limit') > 0 ? request()->input('limit') : '0' }}">
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
                    <div>
                        @php
                            $tasks = \Illuminate\Support\Facades\Redis::lrange('usertask_' . Auth::user()->id, 0, -1);
                        @endphp
                        @foreach (App\Models\Chats::join('histories', 'chats.id', '=', 'histories.chat_id')->leftjoin('feedback', 'history_id', '=', 'histories.id')->join('llms', 'llms.id', '=', 'chats.llm_id')->where('isbot', true)->whereIn('chats.id', App\Models\Chats::where('dcID', request()->route('duel_id'))->pluck('id'))->select('chats.id as chat_id', 'histories.id as history_id', 'chats.llm_id as llm_id', 'histories.created_at as created_at', 'histories.msg as msg', 'histories.isbot as isbot', 'llms.image as image', 'llms.name as name', 'feedback.nice', 'feedback.detail', 'feedback.flags')->union(
            App\Models\Chats::join('histories', 'chats.id', '=', 'histories.chat_id')->join('llms', 'llms.id', '=', 'chats.llm_id')->where('isbot', false)->where(
                    'chats.id',
                    App\Models\Chats::where('dcID', request()->route('duel_id'))->get()->first()->id,
                )->leftjoin('feedback', 'history_id', '=', 'histories.id')->select('chats.id as chat_id', 'histories.id as history_id', 'chats.llm_id as llm_id', 'histories.created_at as created_at', 'histories.msg as msg', 'histories.isbot as isbot', 'llms.image as image', 'llms.name as name', 'feedback.nice', 'feedback.detail', 'feedback.flags'),
        )->get()->sortBy(function ($chat) {
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
                                            class="flex flex-col scrollbar overflow-y-auto p-3 bg-gray-300 rounded-r-lg rounded-bl-lg">
                                            <p class="text-sm whitespace-pre-line break-all"
                                                id="task_{{ $history->history_id }}">{{ $history->msg }}</p>
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
                                                    onclick="feedback({{ $history->history_id }},1,this,{!! htmlspecialchars(
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
                                                    onclick="feedback({{ $history->history_id }},2,this,{!! htmlspecialchars(
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
                                            {{-- blade-formatter-disable --}}
                                                <p class="text-sm whitespace-pre-line break-words">{{ __($history->msg) }}</p>
                                                {{-- blade-formatter-enable --}}
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
                                                        onclick="feedback({{ $history->history_id }},1,this,{!! htmlspecialchars(
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
                                                        onclick="feedback({{ $history->history_id }},2,this,{!! htmlspecialchars(
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
                    </div>
                </div>
                <div class="bg-gray-300 dark:bg-gray-500 p-4 flex flex-col overflow-y-hidden">
                    <form method="post"
                        action="{{ route('duel.request') . (request()->input('limit') > 0 ? '' : '?limit=' . request()->input('limit')) }}"
                        id="prompt_area">
                        <div class="flex items-end justify-end">
                            @csrf
                            <input name="duel_id" value="{{ request()->route('duel_id') }}" style="display:none;">
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
                        <input type="hidden" name="limit"
                            value="{{ request()->input('limit') > 0 ? request()->input('limit') : '0' }}">
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
            var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
            if ($("#chat_input")) {
                $("#chat_input").focus();

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
                $("#chat_input").on("keydown", function(event) {
                    if (!isMac && event.key === "Enter" && !event.shiftKey) {
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

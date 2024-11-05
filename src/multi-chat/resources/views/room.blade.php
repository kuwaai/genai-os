<x-app-layout>
    @php
        $bots = App\Models\Bots::getSortedBots();
        $sorting_methods = App\Models\Bots::getBotSortingMethods();
        $result = App\Models\LLMs::getLLMs(Auth()->user()->group_id);
    @endphp
    @env('arena')
    @php
        $bots = $bots->where('access_code', '!=', 'feedback');
    @endphp
    @endenv
    <x-chat.functions />
    <x-store.modal.bot-detail :result="$result" />
    @if (request()->user()->hasPerm('Room_delete_chatroom'))
        <x-room.modal.delete_confirm />
    @endif
    @if (request()->user()->hasPerm('Room_update_new_chat'))
        <x-room.modal.group-chat :result="$bots" :$sorting_methods />
    @endif
    @if (!(request()->route('room_id') || session('llms')))
        <x-room.rooms.drawer :result="$bots" />
    @else
        @if (request()->route('room_id'))
            @if (request()->user()->hasPerm('Room_update_feedback'))
                <x-chat.modals.feedback />
            @endif
            @if (request()->user()->hasPerm('Room_read_export_chat'))
                <x-chat.modals.export_history :name="App\Models\ChatRoom::find(request()->route('room_id'))->name" />
            @endif
        @endif
        @php
            $DC = App\Models\ChatRoom::getChatRoomsWithIdentifiers(Auth::user()->id);

            try {
                if (!session('llms')) {
                    $identifier = collect(Illuminate\Support\Arr::flatten($DC->toarray(), 1))
                        ->where('id', '=', request()->route('room_id'))
                        ->first()['identifier'];
                    $DC = $DC[$identifier];
                    $llms = App\Models\Bots::getBotsFromIds(explode(',', $identifier));
                } else {
                    $llms = App\Models\Bots::getBotsFromIds(session('llms'));
                    $DC = $DC[implode(',', $llms->pluck('id')->toArray())];
                }
            } catch (Exception $e) {
                $llms = App\Models\Bots::getBotsFromIds(session('llms'));
                $DC = null;
            }
        @endphp
        <x-room.rooms.drawer :llms="$llms" :DC="$DC" />
    @endif
    @if (request()->user()->hasPerm('Room_update_import_chat'))
        <x-chat.modals.import_history :llms="$llms ?? []" />
    @endif
    <div class="flex h-full mx-auto py-2">
        <div
            class="bg-white dark:bg-gray-800 text-white w-64 hidden sm:flex flex-shrink-0 relative rounded-l-lg overflow-hidden">
            <div class="p-3 flex flex-1 flex-col h-full overflow-y-auto scrollbar">
                @if ($bots->count() == 0)
                    <div
                        class="flex-1 h-full flex flex-col w-full text-center rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                        {!! __('chat.placeholder.no_llms') !!}
                    </div>
                @else
                    <div class="mb-2">
                        <div class="border border-black dark:border-white border-1 rounded-lg flex overflow-hidden">
                            <a href="{{ route('room.home') }}"
                                class="flex justify-center transition items-center px-4 cursor-pointer hover:bg-gray-200 text-black dark:text-white dark:hover:bg-gray-500 rounded-l-lg duration-300">←</a>
                            @if (request()->user()->hasPerm('Room_update_new_chat'))
                                <button data-modal-target="create-model-modal" data-modal-toggle="create-model-modal"
                                    class="flex w-full border-x border-1 border-black dark:border-white menu-btn flex items-center justify-center h-12 dark:hover:bg-gray-700 hover:bg-gray-200 transition duration-300">

                                    <p class="flex-1 text-center text-gray-700 dark:text-white">
                                        {{ __('room.button.create_room') }}
                                    </p>
                                </button>
                            @endif
                            @if (request()->user()->hasPerm('Room_update_import_chat'))
                                <button data-modal-target="importModal" data-modal-toggle="importModal"
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 {{ request()->user()->hasPerm('Room_update_new_chat') ? 'rounded-r-lg ' : 'rounded-lg w-full' }} flex items-center justify-center transition duration-300">
                                    {{ request()->user()->hasPerm('Room_update_new_chat') ? '' : '匯入對話　' }}
                                    <i class="fas fa-file-import"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="mb-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden inline">
                        <div class="flex">
                            <div class="w-full">
                                <input type="search" oninput="search_chat($(this).val(), $(this).parent().parent().parent().next())"
                                    class="p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-r-lg border-l-gray-50 border-l-2 border border-gray-300 dark:bg-gray-700 dark:border-l-gray-700  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:border-blue-500"
                                    placeholder="{{__('room.label.search_chat')}}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <x-room.rooms.list :llms="$llms" :DC="$DC" />
                @endif
            </div>
        </div>
        @if (!request()->route('room_id') && !session('llms'))
            <div id="histories_hint"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden">
                <button
                    class="absolute sm:hidden text-center text-black hover:text-black dark:text-white hover:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-700 focus:ring-4 focus:ring-blue-300 font-medium text-sm px-5 py-5 focus:outline-none dark:focus:ring-blue-800"
                    type="button" data-drawer-target="chatlist_drawer" data-drawer-show="chatlist_drawer"
                    aria-controls="chatlist_drawer">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="flex justify-end">
                    <x-sorted-list.control-menu :$sorting_methods />
                </div>

                <div
                    class="mb-4 grid grid-cols-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 xl:grid-cols-10 2xl:grid-cols-12 mb-auto overflow-y-auto scrollbar">
                    @foreach ($bots as $bot)
                        <x-sorted-list.item html_tag="form" :$sorting_methods :record="$bot" method="post"
                            class="text-black dark:text-white h-[135px] p-2 hover:bg-gray-200 dark:hover:bg-gray-500 transition"
                            action="{{ route('room.new') }}">
                            @csrf
                            <button class="h-full w-full flex flex-col items-center justify-start">
                                <img class="rounded-full mx-auto bg-black" width="50px" height="50px"
                                    src="{{ $bot->image ? asset(Storage::url($bot->image)) : '/' . config('app.LLM_DEFAULT_IMG') }}">
                                <p class="text-sm line-clamp-2">{{ $bot->name }}</p>
                                @if ($bot->description)
                                    <p class="text-gray-500 dark:text-gray-300 line-clamp-1 max-w-full text-xs">
                                        {{ $bot->description }}</p>
                                @endif
                                <input name="llm[]" value="{{ $bot->id }}" style="display:none;">
                            </button>
                        </x-sorted-list.item>
                    @endforeach
                </div>
            </div>
        @else
            <div id="histories"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden">
                <x-room.header :llms="$llms" />

                <div id="chatroom" class="flex-1 p-4 overflow-y-auto flex flex-col-reverse scrollbar">
                    <div style="display:none;"
                        class="bg-red-100 border border-red-400 mt-2 text-red-700 px-4 py-3 rounded relative"
                        id="error_alert" role="alert">
                        <span class="block sm:inline"></span>
                    </div>
                    @if (!session('llms'))
                        @php
                            $tasks = \Illuminate\Support\Facades\Redis::lrange('usertask_' . Auth::user()->id, 0, -1);
                            $mergedChats = \App\Models\Chatroom::getMergedChats(request()->route('room_id'));
                            $refers = $mergedChats->where('isbot', true);
                        @endphp

                        @env('arena')
                        @php
                            $output = collect();
                            $bufferedBotMessages = [];
                            foreach ($mergedChats as $history) {
                                if ($history->isbot) {
                                    // If the current element is a bot message, buffer it
                                    $bufferedBotMessages[] = $history;
                                } else {
                                    // If the current element is not a bot message, check if there are buffered bot messages
                                    if (!empty($bufferedBotMessages)) {
                                        shuffle($bufferedBotMessages);
                                        // If there are buffered bot messages, push them into the output collection
                                        $output = $output->merge($bufferedBotMessages);

                                        // Reset the buffered bot messages array
                                        $bufferedBotMessages = [];
                                    }

                                    // Push the current non-bot message into the output collection
                                    $output->push($history);
                                }
                            }
                            if (!empty($bufferedBotMessages)) {
                                shuffle($bufferedBotMessages);
                                // If there are buffered bot messages, push them into the output collection
                                $output = $output->merge($bufferedBotMessages);

                                // Reset the buffered bot messages array
                                $bufferedBotMessages = [];
                            }
                            $mergedChats = $output;
                        @endphp
                        <div>
                            @foreach ($mergedChats as $history)
                                <x-chat.message :history="$history" :tasks="$tasks" :refers="$refers"
                                    :anonymous="true" />
                            @endforeach
                        </div>
                    @else
                        <div>
                            @foreach ($mergedChats as $history)
                                <x-chat.message :history="$history" :tasks="$tasks" :refers="$refers" />
                            @endforeach
                        </div>
                        @endenv
                    @endif

                    @if (count($llms) == 1)
                        <div class="text-black dark:text-white p-2 mb-auto">
                            <img id="llm_img" class="rounded-full mx-auto bg-black" width="100px" height="100px"
                                src="{{ $llms[0]->image ?? $llms[0]->base_image ? asset(Storage::url($llms[0]->image ?? $llms[0]->base_image)) : '/' . config('app.LLM_DEFAULT_IMG') }}">
                            <p class="text-center text-sm line-clamp-4 py-1">{{ $llms[0]->name }}</p>
                            @if ($llms[0]->description)
                                <p
                                    class="text-gray-500 dark:text-gray-300 line-clamp-4 max-w-full text-xs text-center py-1">
                                    {{ $llms[0]->description }}</p>
                            @endif
                        </div>
                    @endif
                </div>
                @if (
                    (request()->user()->hasPerm('Room_update_new_chat') && session('llms')) ||
                        (request()->user()->hasPerm('Room_update_send_message') && !session('llms')))
                    <div class="bg-gray-300 dark:bg-gray-500 p-4 flex flex-col overflow-y-hidden">
                        @if (request()->user()->hasPerm('Room_update_new_chat') && session('llms'))
                            <x-room.prompt-area.create :llms="$llms" :tasks="$tasks ?? null" />
                        @elseif (request()->user()->hasPerm('Room_update_send_message') && !session('llms'))
                            <x-room.prompt-area.request :llms="$llms" :tasks="$tasks ?? null" />
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>

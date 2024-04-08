<x-app-layout>
    @php
        $result = App\Models\Bots::Join('llms', function ($join) {
            $join->on('llms.id', '=', 'bots.model_id');
        })
            ->select(
                'llms.*',
                'bots.*',
                DB::raw('COALESCE(bots.description, llms.description) as description'),
                DB::raw('COALESCE(bots.config, llms.config) as config'),
                DB::raw('COALESCE(bots.image, llms.image) as image'),
            )
            ->orderby('llms.order')
            ->orderby('bots.created_at')
            ->get();
    @endphp
    @env('arena')
    @php
        $result = $result->where('access_code', '!=', 'feedback');
    @endphp
    @endenv
    <x-chat.functions />

    @if (request()->user()->hasPerm('Room_delete_chatroom'))
        <x-room.modal.delete_confirm />
    @endif
    @if (!(request()->route('room_id') || session('llms')))
        @if (request()->user()->hasPerm('Room_update_new_chat'))
            <x-room.modal.group-chat :result="$result" />
        @endif
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
            $DC = App\Models\ChatRoom::leftJoin('chats', 'chatrooms.id', '=', 'chats.roomID')
                ->where('chats.user_id', Auth::user()->id)
                ->select('chatrooms.*', DB::raw('count(chats.id) as counts'))
                ->groupBy('chatrooms.id');

            // Fetch the ordered identifiers based on `llm_id` for each database
            $DC = $DC->selectSub(function ($query) {
                if (config('database.default') == 'sqlite') {
                    $query
                        ->from('chats')
                        ->selectRaw("group_concat(bot_id, ',') as identifier")
                        ->whereColumn('roomID', 'chatrooms.id')
                        ->orderBy('bot_id');
                } elseif (config('database.default') == 'mysql') {
                    $query
                        ->from('chats')
                        ->selectRaw('group_concat(bot_id separator \',\') as identifier')
                        ->whereColumn('roomID', 'chatrooms.id');
                } elseif (config('database.default') == 'pgsql') {
                    $query
                        ->from('chats')
                        ->selectRaw('string_agg(bot_id::text, \',\') as identifier')
                        ->whereColumn('roomID', 'chatrooms.id');
                }
            }, 'identifier');

            // Get the final result and group by the ordered identifiers
            $DC = $DC->get()->groupBy('identifier');

            try {
                if (!session('llms')) {
                    $identifier = collect(Illuminate\Support\Arr::flatten($DC->toarray(), 1))
                        ->where('id', '=', request()->route('room_id'))
                        ->first()['identifier'];
                    $DC = $DC[$identifier];
                    $llms = App\Models\Bots::whereIn(
                        'bots.id',
                        array_map('intval', explode(',', trim($identifier, '{}'))),
                    )
                        ->join('llms', function ($join) {
                            $join->on('llms.id', '=', 'bots.model_id');
                        })
                        ->select(
                            'llms.*',
                            'bots.*',
                            DB::raw('COALESCE(bots.description, llms.description) as description'),
                            DB::raw('COALESCE(bots.config, llms.config) as config'),
                            DB::raw('COALESCE(bots.image, llms.image) as image'),
                        )
                        ->orderby('bots.id')
                        ->get();
                } else {
                    $llms = App\Models\Bots::whereIn('bots.id', session('llms'))
                        ->Join('llms', function ($join) {
                            $join->on('llms.id', '=', 'bots.model_id');
                        })
                        ->select(
                            'llms.*',
                            'bots.*',
                            DB::raw('COALESCE(bots.description, llms.description) as description'),
                            DB::raw('COALESCE(bots.config, llms.config) as config'),
                            DB::raw('COALESCE(bots.image, llms.image) as image'),
                        )
                        ->orderby('bots.id')
                        ->get();
                    $DC = $DC[implode(',', array_reverse($llms->pluck('id')->toArray()))];
                }
            } catch (Exception $e) {
                $llms = App\Models\Bots::whereIn('bots.id', session('llms'))
                    ->Join('llms', function ($join) {
                        $join->on('llms.id', '=', 'bots.model_id');
                    })
                    ->select(
                        'llms.*',
                        'bots.*',
                        DB::raw('COALESCE(bots.description, llms.description) as description'),
                        DB::raw('COALESCE(bots.config, llms.config) as config'),
                        DB::raw('COALESCE(bots.image, llms.image) as image'),
                    )
                    ->orderby('bots.id')
                    ->get();
                $DC = null;
            }
        @endphp
        <x-room.rooms.drawer :llms="$llms" :DC="$DC" />
    @endif
    @if (request()->user()->hasPerm('Room_update_import_chat'))
        <x-chat.modals.import_history :llms="$llms ?? []" />
    @endif
    <div class="flex h-full max-w-7xl mx-auto py-2">
        <div
            class="bg-white dark:bg-gray-800 text-white {{ !request()->route('room_id') && !session('llms') ? 'sm:w-64 w-full flex' : 'w-64 hidden sm:flex' }} flex-shrink-0 relative rounded-l-lg overflow-hidden">
            <div class="p-3 flex flex-1 flex-col h-full overflow-y-auto scrollbar">
                @if ($result->count() == 0)
                    <div
                        class="flex-1 h-full flex flex-col w-full text-center rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                        {!! __('chat.hint.no_llms') !!}
                    </div>
                @else
                    @if (request()->route('room_id') || session('llms'))
                        <a href="{{ route('room.home') }}"
                            class="text-center cursor-pointer hover:bg-gray-200 text-black dark:text-white dark:hover:bg-gray-500 rounded p-2 mb-2">←
                            {{ __('chat.return_to_menu') }}</a>
                        <x-room.rooms.list :llms="$llms" :DC="$DC" />
                    @else
                        <h2 class="block sm:hidden text-xl text-center text-black dark:text-white">{{ __('room.route') }}
                        </h2>
                        <p class="block sm:hidden text-center text-black dark:text-white">
                            {{ __('chat.hint.select_a_chatroom') }}</p>
                        <div class="mb-2">
                            <div class="flex">
                                @if (request()->user()->hasPerm('Room_update_new_chat'))
                                    <button data-modal-target="create-model-modal"
                                        data-modal-toggle="create-model-modal"
                                        class="flex rounded-{{ request()->user()->hasPerm('Room_update_import_chat') ? 'l-' : '' }}lg border border-black dark:border-white border-1 w-full menu-btn flex items-center justify-center h-12 dark:hover:bg-gray-700 hover:bg-gray-200 transition duration-300">

                                        <p class="flex-1 text-center text-gray-700 dark:text-white">
                                            {{ __('room.button.create_room') }}
                                        </p>
                                    </button>
                                @endif
                                @if (request()->user()->hasPerm('Room_update_import_chat'))
                                    <button data-modal-target="importModal" data-modal-toggle="importModal"
                                        class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 {{ request()->user()->hasPerm('Room_update_new_chat') ? 'rounded-r-lg ' : 'rounded-lg w-full' }} flex items-center justify-center">
                                        {{ request()->user()->hasPerm('Room_update_new_chat') ? '' : '匯入對話　' }}
                                        <i class="fas fa-file-import"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <x-room.llm :result="$result" />
                    @endif
                @endif
            </div>
        </div>
        @if (!request()->route('room_id') && !session('llms'))
            <div id="histories_hint"
                class="flex-1 h-full hidden sm:flex flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                {{ __('chat.hint.select_a_chatroom') }}
            </div>
        @else
            <div id="histories"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden">
                <x-room.header :llms="$llms" />

                <div id="chatroom" class="flex-1 p-4 overflow-y-auto flex flex-col-reverse scrollbar">
                    <div>
                        @if (!session('llms'))
                            @php
                                $tasks = \Illuminate\Support\Facades\Redis::lrange(
                                    'usertask_' . Auth::user()->id,
                                    0,
                                    -1,
                                );

                                $roomId = request()->route('room_id');

                                $roomId = Illuminate\Support\Facades\Request::route('room_id');

                                $botChats = App\Models\Chats::join('histories', 'chats.id', '=', 'histories.chat_id')
                                    ->leftJoin('feedback', 'history_id', '=', 'histories.id')
                                    ->join('bots', 'bots.id', '=', 'chats.bot_id')
                                    ->Join('llms', function ($join) {
                                        $join->on('llms.id', '=', 'bots.model_id');
                                    })
                                    ->where('isbot', true)
                                    ->whereIn('chats.id', App\Models\Chats::where('roomID', $roomId)->pluck('id'))
                                    ->select(
                                        'histories.chained as chained',
                                        'chats.id as chat_id',
                                        'histories.id as id',
                                        'chats.bot_id as bot_id',
                                        'histories.created_at as created_at',
                                        'histories.msg as msg',
                                        'histories.isbot as isbot',
                                        DB::raw('COALESCE(bots.description, llms.description) as description'),
                                        DB::raw('COALESCE(bots.config, llms.config) as config'),
                                        DB::raw('COALESCE(bots.image, llms.image) as image'),
                                        'feedback.nice',
                                        'feedback.detail',
                                        'feedback.flags',
                                    );

                                $nonBotChats = App\Models\Chats::join('histories', 'chats.id', '=', 'histories.chat_id')
                                    ->leftjoin('bots', 'bots.id', '=', 'chats.bot_id')
                                    ->Join('llms', function ($join) {
                                        $join->on('llms.id', '=', 'bots.model_id');
                                    })
                                    ->where('isbot', false)
                                    ->whereIn('chats.id', App\Models\Chats::where('roomID', $roomId)->pluck('id'))
                                    ->select(
                                        'histories.chained as chained',
                                        'chats.id as chat_id',
                                        'histories.id as id',
                                        'chats.bot_id as bot_id',
                                        'histories.created_at as created_at',
                                        'histories.msg as msg',
                                        'histories.isbot as isbot',
                                        DB::raw('COALESCE(bots.description, llms.description) as description'),
                                        DB::raw('COALESCE(bots.config, llms.config) as config'),
                                        DB::raw('COALESCE(bots.image, llms.image) as image'),
                                        DB::raw('NULL as nice'),
                                        DB::raw('NULL as detail'),
                                        DB::raw('NULL as flags'),
                                    );

                                $mergedChats = $botChats
                                    ->union($nonBotChats)
                                    ->get()
                                    ->sortBy(function ($chat) {
                                        return [$chat->created_at, $chat->id, $chat->bot_id, -$chat->history_id];
                                    });
                                $mergedMessages = [];
                                // Filter and merge the chats based on the condition
                                $filteredChats = $mergedChats->filter(function ($chat) use (&$mergedMessages) {
                                    if (!$chat->isbot && !in_array($chat->msg, $mergedMessages)) {
                                        // Add the message to the merged messages array
                                        $mergedMessages[] = $chat->msg;
                                        return true; // Keep this chat in the final result
                                    } elseif ($chat->isbot) {
                                        $mergedMessages = [];
                                        return true; // Keep bot chats in the final result
                                    }
                                    return false; // Exclude duplicate non-bot chats
                                });

                                // Sort the filtered chats
                                $mergedChats = $filteredChats->sortBy(function ($chat) {
                                    return [$chat->created_at, $chat->bot_id, -$chat->id];
                                });
                                $refers = $mergedChats->where('isbot', '=', true);
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
                            @foreach ($mergedChats as $history)
                                <x-chat.message :history="$history" :tasks="$tasks" :refers="$refers"
                                    :anonymous="true" />
                            @endforeach
                        @else
                            @foreach ($mergedChats as $history)
                                <x-chat.message :history="$history" :tasks="$tasks" :refers="$refers" />
                            @endforeach
                            @endenv
                        @endif
                        <div style="display:none;"
                            class="bg-red-100 border border-red-400 mt-2 text-red-700 px-4 py-3 rounded relative"
                            id="error_alert" role="alert">
                            <span class="block sm:inline"></span>
                        </div>
                    </div>
                </div>
                @if (
                    (request()->user()->hasPerm('Room_update_new_chat') && session('llms')) ||
                        (request()->user()->hasPerm('Room_update_send_message') && !session('llms')))
                    <div class="bg-gray-300 dark:bg-gray-500 p-4 flex flex-col overflow-y-hidden">
                        @if (request()->user()->hasPerm('Room_update_new_chat') && session('llms'))
                            <x-room.prompt-area.create :llms="$llms" />
                        @elseif (request()->user()->hasPerm('Room_update_send_message') && !session('llms'))
                            <x-room.prompt-area.request :llms="$llms" />
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>

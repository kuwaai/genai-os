<x-app-layout>
    @php
        function sortBotsByDate($bots)
        {
            /*
             * Sort the bots according to the creation date (most recent first).
             */
            $bots = $bots->sortByDesc('created_at'); // Assuming 'created_at' is the timestamp field
            return $bots;
        }
        function sortBotsByModel($bots)
        {
            /*
             * Sort the bots according to the model order specified in the
             * database, prioritizing the creation date (most recent first) for
             * bots with the same model order.
             */
            $bots = $bots
                ->sortBy('order')
                ->groupBy('order')
                ->map(function ($subSet) {
                    return sortBotsByDate($subSet);
                })->collapse();
            return $bots;
        }
        function sortBotsByName($bots)
        {
            /*
             * Sort the bots according to their name (a-z).
             */
            $bots = $bots->sortBy('name');
            return $bots;
        }
        function sortBotsByNameDesc($bots)
        {
            /*
             * Sort the bots according to their name (z-a).
             */
            $bots = $bots->sortByDesc('name');
            return $bots;
        }
        function sortUserBots($bots, $sortingFunc = 'sortBotsByModel')
        {
            /*
             * Prioritize sorting the user's bot over other bots.
             */

            $userId = request()->user()->id;
            // Filter and sort the bots owned by the current user
            $userBots = $bots
                ->filter(function ($bot) use ($userId) {
                    return $bot->owner_id == $userId;
                });
            $userBots = $sortingFunc($userBots);

            // Filter the remaining bots and sorting them
            $otherBots = $bots
                ->filter(function ($bot) use ($userId) {
                    return $bot->owner_id != $userId;
                });
            $otherBots = $sortingFunc($otherBots);

            // Merge the sorted user bots with the randomized other bots
            return $userBots->merge($otherBots)->values();
        }
        function addIndexProperty($arr_of_objs, $key) {
            /*
             * Add the index as a property to each item in the array of objects.
             */
            $prop_name = "{$key}-order-index";
            $result = $arr_of_objs->map(function (object $item, int $index) use ($prop_name) {
                $item->$prop_name = $index;
                return $item;
            });
            return $result;
        }

        $result = App\Models\Bots::Join('llms', function ($join) {
            $join->on('llms.id', '=', 'bots.model_id');
        })
            ->leftjoin('users', 'users.id', '=', 'bots.owner_id')
            ->where('llms.enabled', '=', true)
            ->wherein(
                'bots.model_id',
                DB::table('group_permissions')
                    ->join('permissions', 'group_permissions.perm_id', '=', 'permissions.id')
                    ->select(DB::raw('substring(permissions.name, 7) as model_id'), 'perm_id')
                    ->where('group_permissions.group_id', Auth::user()->group_id)
                    ->where('permissions.name', 'like', 'model_%')
                    ->get()
                    ->pluck('model_id'),
            )
            ->where(function ($query) {
                $query
                    ->where('bots.visibility', '=', 0)->orwhere('bots.visibility', '=', 1)
                    ->orWhere(function ($query) {
                        $query->where('bots.visibility', '=', 3)->where('bots.owner_id', '=', request()->user()->id);
                    })
                    ->orWhere(function ($query) {
                        $query
                            ->where('bots.visibility', '=', 2)
                            ->where('users.group_id', '=', request()->user()->group_id);
                    });
            })
            ->select(
                'llms.*',
                'bots.*',
                DB::raw('COALESCE(bots.description, llms.description) as description'),
                DB::raw('COALESCE(bots.config, llms.config) as config'),
                DB::raw('COALESCE(bots.image, llms.image) as image'),
                'llms.name as llm_name',
            )
            ->orderby('llms.order')
            ->orderby('bots.created_at')
            ->get();

        $sorting_methods = [
            // The default sorting method
            [
                "index_key" => "model",
                "sorting_method" => "sortBotsByModel",
                "name" => "room.sort_by.model"
            ],
            
            // Other sorting method
            [
                "index_key" => "date",
                "sorting_method" => "sortBotsByDate",
                "name" => "room.sort_by.date"
            ],
            [
                "index_key" => "name",
                "sorting_method" => "sortBotsByName",
                "name" => "room.sort_by.name"
            ],
            [
                "index_key" => "name-desc",
                "sorting_method" => "sortBotsByNameDesc",
                "name" => "room.sort_by.name_desc"
            ],
        ];

        foreach ($sorting_methods as $method) {
            $result = sortUserBots($result, $method["sorting_method"]);
            $result = addIndexProperty($result, $method["index_key"]);
        }
        $result = sortUserBots($result, $sorting_methods[0]["sorting_method"]);
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
    @if (request()->user()->hasPerm('Room_update_new_chat'))
        <x-room.modal.group-chat :$result :$sorting_methods />
    @endif
    <x-room.rooms.drawer :result="$result" />
    @if (request()->user()->hasPerm('Room_update_import_chat'))
        <x-chat.modals.import_history :llms="$llms ?? []" />
    @endif
    <div class="flex h-full max-w-7xl mx-auto py-2">
        <div
            class="bg-white dark:bg-gray-800 text-white w-64 hidden sm:flex flex-shrink-0 relative rounded-l-lg overflow-hidden">
            <div class="p-3 flex flex-1 flex-col h-full overflow-y-auto scrollbar">
                @if ($result->count() == 0)
                    <div
                        class="flex-1 h-full flex flex-col w-full text-center rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                        {!! __('chat.hint.no_llms') !!}
                    </div>
                @else
                    <h2 class="block sm:hidden text-xl text-center text-black dark:text-white">
                        {{ __('room.route') }}
                    </h2>
                    <p class="block sm:hidden text-center text-black dark:text-white">
                        {{ __('chat.hint.select_a_chatroom') }}</p>
                    <div class="mb-2">
                        <div class="border border-black dark:border-white border-1 rounded-lg flex overflow-hidden">
                            @if (request()->user()->hasPerm('Room_update_new_chat'))
                                <button data-modal-target="create-model-modal"
                                    data-modal-toggle="create-model-modal"
                                    class="flex w-full border-r border-1 border-black dark:border-white menu-btn flex items-center justify-center h-12 dark:hover:bg-gray-700 hover:bg-gray-200 transition duration-300">

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
                    <x-room.llm :result="$result" />
                @endif
            </div>
        </div>
        <div id="histories_hint"
            class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden">
            <button
                class="absolute sm:hidden text-center text-black hover:text-black dark:text-white hover:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-700 focus:ring-4 focus:ring-blue-300 font-medium text-sm px-5 py-5 focus:outline-none dark:focus:ring-blue-800"
                type="button" data-drawer-target="chatlist_drawer" data-drawer-show="chatlist_drawer"
                aria-controls="chatlist_drawer">
                <i class="fas fa-bars"></i>
            </button>
            <div class="flex justify-end">
                <x-sorted-list.control-menu :$sorting_methods
                 btn_class="text-sm leading-4 px-5 py-5 font-medium rounded-md text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100"
                />
            </div>
            
            <div class="mb-4 grid grid-cols-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 mb-auto overflow-y-auto scrollbar"> 
                @foreach($result as $bot)
                    <x-sorted-list.item html_tag="form" :$sorting_methods :record="$bot"
                        method="post"
                        class="text-black dark:text-white h-[135px] p-2 hover:bg-gray-200 dark:hover:bg-gray-500 transition"
                        action="{{ route('room.new') }}"
                    >
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
    </div>
</x-app-layout>
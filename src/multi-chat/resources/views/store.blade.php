<x-app-layout>
    <div class="flex h-full max-w-7xl mx-auto py-2">
        <div
            class="flex flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-lg overflow-y-auto scrollbar text-gray-700 dark:text-white">

            @php
                $result = App\Models\Bots::getBots(Auth()->user()->group_id);
                $bots = App\Models\Bots::Join('llms', function ($join) {
                    $join->on('llms.id', '=', 'bots.model_id');
                })
                    ->leftjoin('users', 'users.id', '=', 'bots.owner_id')
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
                    ->where('llms.enabled', '=', true)
                    ->select(
                        'llms.*',
                        'bots.*',
                        DB::raw('COALESCE(bots.description, llms.description) as description'),
                        DB::raw('COALESCE(bots.config, llms.config) as config'),
                        'bots.image as image',
                        'llms.image as base_image',
                        'llms.name as llm_name',
                        'users.group_id',
                    )
                    ->get();
                if (!request()->user()->hasPerm('Store_read_any_modelfile')) {
                    $bots = $bots->map(function ($item) {
                        if ($item->owner_id != Auth::user()->id) {
                            $item->config = '';
                        }
                        return $item;
                    });
                }
            @endphp
            @if (request()->user()->hasPerm(['tab_Manage', 'Store_create_community_bot', 'Store_create_group_bot', 'Store_create_private_bot']))
                <x-store.modal.create-bot :result="$result" />
            @endif
            <x-store.modal.bot-detail :result="$result" />
            @if (request()->user()->hasPerm(['tab_Manage', 'Store_create_community_bot', 'Store_create_group_bot', 'Store_create_private_bot']))
                <div class="create-bot-btn pt-4 my-2 mx-auto w-[150px] h-[50px] flex" data-modal-target="create-bot-modal"
                    data-modal-toggle="create-bot-modal">
                    <button
                        class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-700 border border-green-500 border-1 hover:bg-gray-300 transition duration-300 rounded-l-lg overflow-hidden">
                        <p class="flex-1 text-center text-green-500">{{ __('store.button.create') }}</p>
                    </button>
                    <label for="upload_bot_modelfile"
                        class="bg-green-500 hover:bg-green-600 h-12 text-white font-bold py-3 px-4 rounded-r-lg  flex items-center justify-center transition duration-300">
                        <i class="fas fa-file-import"></i>
                    </label>
                    <input type="file" accept=".bot" class="hidden" id="upload_bot_modelfile"
                        onchange="importBot($(this)[0].files)" />
                </div>
            @endif
            @php
                function sortBots($bots)
                {
                    $userId = request()->user()->id;
                    // Filter and sort the bots owned by the current user
                    $userBots = $bots
                        ->filter(function ($bot) use ($userId) {
                            return $bot->owner_id == $userId;
                        })
                        ->sortBy('order')
                        ->groupBy('order')
                        ->map(function ($subSet) {
                            return $subSet->sortByDesc('created_at'); // Assuming 'created_at' is the timestamp field
                        })
                        ->collapse();

                    // Filter the remaining bots and randomize them
                    $otherBots = $bots
                        ->filter(function ($bot) use ($userId) {
                            return $bot->owner_id != $userId;
                        })
                        ->sortBy('order')
                        ->groupBy('order')
                        ->map(function ($subSet) {
                            return $subSet->sortByDesc('created_at');
                        })
                        ->collapse();

                    // Merge the sorted user bots with the randomized other bots
                    return $userBots->merge($otherBots)->values();
                }
                $system_bots = sortBots($bots->where('visibility', '=', 0));
                $community_bots = sortBots($bots->where('visibility', '=', 1));
                $group_bots = $bots->where('visibility', '=', 2);
                if (!request()->user()->hasPerm('tab_Manage')) {
                    $group_bots = $group_bots->where('group_id', '=', request()->user()->group_id);
                }
                $group_bots = sortBots($group_bots);
                $private_bots = $bots->where('visibility', '=', 3)->where('owner_id', '=', request()->user()->id);
                $private_bots = sortBots($private_bots);
            @endphp

            <div class="flex-1 flex overflow-hidden flex-col rounded">
                <div class="w-full">
                    <ul class="flex w-full text-sm font-medium text-center" data-tabs-toggle="#BotContents"
                        role="tablist">
                        <li class="flex-1" role="presentation">
                            <button class="w-full p-4 text-gray-700 dark:text-gray-200 bg-transparent" id="system-tab"
                                data-tabs-target="#system" type="button" role="tab" aria-controls="system"
                                aria-selected="{{ session('last_bot_tab') ? 'false' : 'true' }}">
                                {{ __('store.label.system_bots') }}
                            </button>
                        </li>
                        <li class="flex-1" role="presentation">
                            <button class="w-full p-4 text-gray-700 dark:text-gray-200 bg-transparent" id="private-tab"
                                data-tabs-target="#private" type="button" role="tab" aria-controls="private"
                                aria-selected="{{ session('last_bot_tab') == 'private' ? 'true' : 'false' }}">
                                {{ __('store.label.private') }}
                            </button>
                        </li>
                        <li class="flex-1" role="presentation">
                            <button class="w-full p-4 text-gray-700 dark:text-gray-200 bg-transparent" id="group-tab"
                                data-tabs-target="#group" type="button" role="tab" aria-controls="group"
                                aria-selected="{{ session('last_bot_tab') == 'group' ? 'true' : 'false' }}">
                                {{ __('store.label.groups_bots') }}
                            </button>
                        </li>
                        <li class="flex-1" role="presentation">
                            <button class="w-full p-4 text-gray-700 dark:text-gray-200 bg-transparent"
                                id="community-tab" data-tabs-target="#community" type="button" role="tab"
                                aria-controls="community"
                                aria-selected="{{ session('last_bot_tab') == 'community' ? 'true' : 'false' }}">
                                {{ __('store.label.community_bots') }}
                            </button>
                        </li>
                    </ul>
                </div>

                <div id="BotContents" class="flex flex-1 overflow-hidden px-4 mb-2">
                    <!-- System Bots Tab Content -->
                    @if (request()->user()->hasPerm('Store_read_discover_system_bots') && $system_bots->count() > 0)
                        <div class="{{ session('last_bot_tab') ? 'hidden' : '' }} bg-gray-100 dark:bg-gray-600 flex flex-1"
                            id="system" role="tabpanel" aria-labelledby="system-tab">
                            <x-store.bot-showcase :bots="$system_bots" :extra="'official_bots-'" />
                        </div>
                    @endif

                    <!-- Private Bots Tab Content -->
                    @if (request()->user()->hasPerm('Store_read_discover_private_bots') && $private_bots->count() > 0)
                        <div class="{{ session('last_bot_tab') == 'private' ? '' : 'hidden' }} bg-gray-100 dark:bg-gray-600 flex flex-1"
                            id="private" role="tabpanel" aria-labelledby="private-tab">
                            <x-store.bot-showcase :bots="$private_bots" :extra="'my_bots-'" />
                        </div>
                    @endif

                    <!-- Group Bots Tab Content -->
                    @if (request()->user()->hasPerm('Store_read_discover_group_bots') && $group_bots->count() > 0)
                        <div class="{{ session('last_bot_tab') == 'group' ? '' : 'hidden' }} bg-gray-100 dark:bg-gray-600 flex flex-1"
                            id="group" role="tabpanel" aria-labelledby="group-tab">
                            <x-store.bot-showcase :bots="$group_bots" :extra="'group_bots-'" />
                        </div>
                    @endif

                    <!-- Community Bots Tab Content -->
                    @if (request()->user()->hasPerm('Store_read_discover_community_bots') && $community_bots->count() > 0)
                        <div class="{{ session('last_bot_tab') == 'community' ? '' : 'hidden' }} bg-gray-100 dark:bg-gray-600 flex flex-1"
                            id="community" role="tabpanel" aria-labelledby="community-tab">
                            <x-store.bot-showcase :bots="$community_bots" :extra="'community_bots-'" />
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

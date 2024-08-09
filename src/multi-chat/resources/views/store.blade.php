<x-app-layout>
    <div class="flex h-full max-w-7xl mx-auto py-2">
        <div
            class="flex flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-lg overflow-y-auto scrollbar text-gray-700 dark:text-white">

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
            <x-store.modal.bot-detail />
            @if (request()->user()->hasPerm(['tab_Manage', 'Store_create_community_bot', 'Store_create_group_bot', 'Store_create_private_bot']))
                <div class="create-bot-btn pt-4 my-2 mx-auto w-[150px] h-[50px] flex" data-modal-target="create-bot-modal"
                    data-modal-toggle="create-bot-modal">
                    <button
                        class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-700 border border-green-500 border-1 hover:bg-gray-300 transition duration-300 rounded-l-lg overflow-hidden">
                        <p class="flex-1 text-center text-green-500">{{ __('store.button.create') }}</p>
                    </button>
                    <label for="upload_bot_modelfile" class="bg-green-500 hover:bg-green-600 h-12 text-white font-bold py-3 px-4 rounded-r-lg  flex items-center justify-center transition duration-300">
                        <i class="fas fa-file-import"></i>
                    </label>
                    <input type="file" accept=".bot" class="hidden" id="upload_bot_modelfile" onchange="importBot($(this)[0].files)"/>
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
                        })->collapse();

                    // Filter the remaining bots and randomize them
                    $otherBots = $bots
                        ->filter(function ($bot) use ($userId) {
                            return $bot->owner_id != $userId;
                        })
                        ->sortBy('order')
                        ->groupBy('order')
                        ->map(function ($subSet) {
                            return $subSet->sortByDesc('created_at'); 
                        })->collapse();

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

            @if (request()->user()->hasPerm('Store_read_discover_system_bots') && $system_bots->count() > 0)
                <div class="w-full p-4">
                    <p class="mb-2">{{ __('store.label.system_bots') }}</p>
                    <x-store.bot-showcase :bots="$system_bots" :extra="'offical_bots-'" />
                </div>
            @endif
            @if (request()->user()->hasPerm('Store_read_discover_private_bots') && $private_bots->count() > 0)
                <div class="w-full p-4">
                    <p class="mb-2">{{ __('store.label.private') }}</p>
                    <x-store.bot-showcase :bots="$private_bots" :extra="'my_bots-'" />
                </div>
            @endif
            @if (request()->user()->hasPerm('Store_read_discover_group_bots') && $group_bots->count() > 0)
                <div class="w-full p-4">
                    <p class="mb-2">{{ __('store.label.groups_bots') }}</p>
                    <x-store.bot-showcase :bots="$group_bots" :extra="'group_bots-'" />
                </div>
            @endif
            @if (request()->user()->hasPerm('Store_read_discover_community_bots') && $community_bots->count() > 0)
                <div class="w-full p-4">
                    <p class="mb-2">{{ __('store.label.community_bots') }}</p>
                    <x-store.bot-showcase :bots="$community_bots" :extra="'community_bots-'" />
                </div>
            @endif
        </div>
    </div>
    <script>
        function change_bot_image(bot_image_elem, user_upload_elem, new_base_bot_name) {
            /**
             * Dynamically updates the bot's displayed image based on user interaction.
             *
             * Image selection priority:
             * 1. User-uploaded image (highest)
             * 2. Base bot image (if the bot image hasn't been changed)
             * 3. Original image (lowest)
             */
            const [user_uploaded_image] = $(user_upload_elem)[0].files;
            const follow_base_bot = $(bot_image_elem).data("follow-base-bot") ?? true;
            let bot_image_uri = $(bot_image_elem).attr("src");
            if (user_uploaded_image) {
                bot_image_uri = URL.createObjectURL(user_uploaded_image);
            } else if (follow_base_bot && new_base_bot_name) {
                const fallback_image_uri = "{{ asset('/' . config('app.LLM_DEFAULT_IMG')) }}";
                bot_image_uri = $(`#llm-list option[value="${new_base_bot_name}"]`).attr("src") ?? fallback_image_uri;
            }
            $(bot_image_elem).attr("src", bot_image_uri);
        }
 
        $(document).ready(function() {
            var div = $('.bot-showcase')[0];
            if (div) {
                $(div).prev().toggle(div.scrollLeft > 0);
                $(div).next().toggle(div.scrollLeft + $(div).width() < div.scrollWidth);
            }
        });
    </script>
</x-app-layout>

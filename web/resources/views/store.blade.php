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
        $bots = App\Models\Bots::join('llms', 'llms.id', '=', 'bots.model_id')->select('llms.image as llm_image', 'bots.image as image', 'bots.id as id', 'llms.name as llm_name', 'bots.name as name', 'bots.description as description', 'llms.description as llm_description')->get();
    @endphp
    <x-store.modal.new-chat :result="$result" />
    <div class="flex h-full max-w-7xl mx-auto py-2">
        <div
            class="sm:w-64 w-full flex bg-white dark:bg-gray-800 text-black dark:text-white flex-shrink-0 relative rounded-l-lg overflow-hidden">
            <div class="p-3 flex flex-1 flex-col overflow-y-auto scrollbar">
                <div class="mb-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden"
                    data-modal-target="create-app-modal" data-modal-toggle="create-app-modal">
                    <button onclick="scrollContainer.scrollBy({left: -scrollAmount, behavior: 'smooth'});"
                        class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-700 hover:bg-gray-200 transition duration-300">
                        <p class="flex-1 text-center text-gray-700 dark:text-white">{{ __('New Application') }}</p>
                    </button>
                </div>
            </div>
        </div>
        <div
            class="hidden sm:flex flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
            @if ($bots->where('visibility', '=', 0)->count() > 0)
                <div class="w-full p-4">
                    <p class="mb-2">{{ __('Offical Bots') }}</p>
                    <x-store.bot-showcase :bots="$bots->where('visibility', '=', 0)" />
                </div>
            @endif
            @if ($bots->where('owner_id', '=', Auth::user()->id)->count() > 0)
                <div class="w-full p-4">
                    <p class="mb-2">{{ __('Your Bots') }}</p>
                    <x-store.bot-showcase :bots="$bots->where('owner_id', '=', Auth::user()->id)" />
                </div>
            @endif
            @if ($bots->where('visibility', '=', 1)->count() > 0)
                <div class="w-full p-4">
                    <p class="mb-2">{{ __('Community Bots') }}</p>
                    <x-store.bot-showcase :bots="$bots->where('visibility', '=', 1)" />
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

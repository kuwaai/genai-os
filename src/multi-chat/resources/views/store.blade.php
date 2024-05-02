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
                    ->select(
                        'llms.*',
                        'bots.*',
                        DB::raw('COALESCE(bots.description, llms.description) as description'),
                        DB::raw('COALESCE(bots.config, llms.config) as config'),
                        DB::raw('COALESCE(bots.image, llms.image) as image'),
                        'llms.name as llm_name',
                    )
                    ->get();
            @endphp
            <x-store.modal.create-bot :result="$result" />
            <x-store.modal.bot-detail :result="$result" />
            <div class="my-8">
                <x-logo />
            </div>
            <div class="mb-2 mx-auto w-[150px] h-[50px]" data-modal-target="create-bot-modal"
                data-modal-toggle="create-bot-modal">
                <button
                    class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-700 border border-green-500 border-1 hover:bg-gray-200 transition duration-300 rounded-lg overflow-hidden">
                    <p class="flex-1 text-center text-green-500">{{ __('store.button.create') }}</p>
                </button>
            </div>
            @if ($bots->where('visibility', '=', 0)->count() > 0)
                <div class="w-full p-4">
                    <p class="mb-2">{{ __('store.label.offical_bots') }}</p>
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
                    <p class="mb-2">{{ __('store.label.community_bots') }}</p>
                    <x-store.bot-showcase :bots="$bots->where('visibility', '=', 1)" />
                </div>
            @endif
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var div = $('.bot-showcase')[0];
            if (div) {
                $(div).prev().toggle(div.scrollLeft > 0);
                $(div).next().toggle(div.scrollLeft + $(div).width() < div.scrollWidth);
            }
        });
    </script>
</x-app-layout>

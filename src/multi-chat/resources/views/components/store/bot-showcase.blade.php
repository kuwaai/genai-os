@props(['bots', 'extra' => ''])

<div class="flex overflow-hidden auto-rows-min w-full bot-showcase gap-2 pr-2 h-full grid grid-cols-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 xl:grid-cols-10 2xl:grid-cols-12 mb-auto overflow-y-auto scrollbar"
    id="{{ $extra }}bot-showcase">
    @foreach ($bots as $bot)
        @php
            $bot_image_name = $bot->image ?? $bot->base_image;
            $bot_image_uri = asset(
                $bot_image_name ? Storage::url($bot_image_name) : '/' . config('app.LLM_DEFAULT_IMG'),
            );
            $bot_arr = $bot->toArray();
            unset($bot_arr['base_image']);
            $bot_arr = array_merge($bot_arr, [
                'image' => $bot_image_uri,
                'follow_base_bot_image' => is_null($bot->image),
            ]);
            $bot_json = json_encode($bot_arr);
            $readonly =
                request()->user()->id == $bot->owner_id || request()->user()->hasPerm('tab_Manage') ? 'false' : 'true';
        @endphp
        <div onclick="detail_update({{ $bot_json }}, {{ $readonly }})" data-modal-target="detail-modal"
            data-modal-toggle="detail-modal"
            class="{{ $bot->owner_id == request()->user()->id ? 'bg-neutral-300 hover:bg-neutral-400 dark:bg-neutral-500 dark:hover:bg-neutral-700' : ' hover:bg-gray-300 dark:hover:bg-gray-700' }} text-center overflow-hidden flex flex-col justify-center border border-1 rounded-lg cursor-pointer border-gray-500 w-full h-[150px] p-2">
            <img class="rounded-full mx-auto bg-black" width="50px" height="50px" src="{{ $bot_image_uri }}">
            <p class="line-clamp-2 text-sm mb-auto">{{ $bot->name }}</p>
            @if ($bot->description)
                <p class="text-gray-500 dark:text-gray-300 text-xs line-clamp-4 max-w-full flex-1"
                    style="word-wrap:break-word">
                    {{ $bot->description }}</p>
            @endif
        </div>
    @endforeach
</div>

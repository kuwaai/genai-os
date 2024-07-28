@props(['bots', 'extra' => ''])

<div class="flex h-[148px]">
    <div class="relative" style="display:none;">
        <div
            class="absolute left-0 top-0 bottom-0 w-[40px] flex justify-center items-center bg-gradient-to-l to-gray-200 from-gray-200/50 dark:to-gray-600 dark:from-gray-600/50">
            <button
                onclick="$(this).parent().parent().next().animate({scrollLeft: '-=' + $(this).parent().parent().next().width()}, 500);"
                class="rounded-full bg-gray-300 hover:bg-gray-400 dark:bg-gray-800 w-[30px] h-[30px] flex justify-center items-center dark:hover:bg-gray-900">
                < </button>
        </div>
    </div>
    <div class="flex overflow-hidden space-x-2 w-full bot-showcase" id="{{ $extra }}bot-showcase"
        onscroll="handleScrollOrResize(this)">
        @foreach ($bots as $bot)
            @php
                $bot_image_name = $bot->image ?? $bot->base_image;
                $bot_image_uri = asset($bot_image_name ? Storage::url($bot_image_name) : '/' . config('app.LLM_DEFAULT_IMG'));
                $bot_arr = $bot->toArray();
                unset($bot_arr['base_image']);
                $bot_arr = array_merge(
                    $bot_arr,
                    [
                        'image' => $bot_image_uri,
                        'follow_base_bot_image' => is_null($bot->image)
                    ]
                );
                $bot_json = json_encode($bot_arr);
                $readonly = (request()->user()->id == $bot->owner_id || request()->user()->hasPerm('tab_Manage')) ? 'false' : 'true';
            @endphp
            <div onclick="detail_update({{ $bot_json }}, {{ $readonly }})"
                data-modal-target="detail-modal" data-modal-toggle="detail-modal"
                class="{{ $bot->owner_id == request()->user()->id ? 'bg-neutral-300 hover:bg-neutral-400 dark:bg-neutral-500 dark:hover:bg-neutral-700' : ' hover:bg-gray-300 dark:hover:bg-gray-700' }} text-center overflow-hidden flex flex-col justify-center border border-1 rounded-lg cursor-pointer border-gray-500 min-w-[150px] max-w-[150px] w-[150px] p-2">
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
    <div class="relative" style="display:none;">
        <div
            class="absolute right-0 top-0 bottom-0 w-[40px] flex justify-center items-center bg-gradient-to-l from-gray-200 to-gray-200/50 dark:from-gray-600 dark:to-gray-600/50">
            <button
                onclick="$(this).parent().parent().prev().animate({scrollLeft: '+=' + $(this).parent().parent().prev().width()}, 500);"
                class="rounded-full bg-gray-300 hover:bg-gray-400 dark:bg-gray-800 w-[30px] h-[30px] flex justify-center items-center dark:hover:bg-gray-900">
                >
            </button>
        </div>
    </div>
</div>

<script>
    @once

    function handleScrollOrResize(showcase) {
        if (showcase.scrollLeft == 0) {
            $(showcase).prev().fadeOut();
        } else {
            $(showcase).prev().fadeIn();
        }
        if (Math.round(showcase.scrollLeft + $(showcase).width()) >= showcase.scrollWidth) {
            $(showcase).next().fadeOut();
        } else {
            $(showcase).next().fadeIn();
        }
    }
    @endonce
    handleScrollOrResize($('#{{ $extra }}bot-showcase')[0]);
    $(window).on("resize", () => {
        handleScrollOrResize($('#{{ $extra }}bot-showcase')[0])
    })
</script>

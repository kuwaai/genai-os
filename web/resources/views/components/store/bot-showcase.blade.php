@props(['bots'])
<div class="flex h-[150px]">
    <div class="relative" style="display:none;">
        <div
            class="absolute left-0 top-0 bottom-0 w-[40px] flex justify-center items-center bg-gradient-to-l to-gray-600 from-gray-600/50">
            <button
                onclick="$(this).parent().parent().next().animate({scrollLeft: '-=' + $(this).parent().parent().next().width()}, 500);"
                class="rounded-full bg-gray-800 w-[30px] h-[30px] flex justify-center items-center hover:bg-gray-900">
                < </button>
        </div>
    </div>
    <div class="flex overflow-hidden space-x-2"
        onScroll="(function(container){container.scrollLeft==0?$(container).prev().hide():$(container).prev().show(),container.scrollLeft+$(container).width()>=container.scrollWidth?$(container).next().hide():$(container).next().show()})(this)">
        @foreach ($bots as $bot)
            <a href=""
                class="border border-1 rounded-lg border-gray-700 hover:bg-gray-700 min-w-[150px] max-w-[150px] w-[150px] overflow-hidden p-2">
                <img id="llm_img" class="rounded-full m-auto bg-black" width="50px" height="50px"
                    src="{{ $bot->image ? (strpos($bot->image, 'data:image/png;base64') === 0 ? $bot->image : asset(Storage::url($bot->image))) : (strpos($bot->llm_image, 'data:image/png;base64') === 0 ? $bot->llm_image : asset(Storage::url($bot->llm_image))) }}">
                <p>{{ $bot->name }}</p>
                @if ($bot->description || $bot->llm_description)
                    <p>{{ $bot->description ?? $bot->llm_description }}</p>
                @endif
            </a>
        @endforeach
    </div>
    <div class="relative">
        <div
            class="absolute right-0 top-0 bottom-0 w-[40px] flex justify-center items-center bg-gradient-to-l from-gray-600 to-gray-600/50">
            <button
                onclick="$(this).parent().parent().prev().animate({scrollLeft: '+=' + $(this).parent().parent().prev().width()}, 500);"
                class="rounded-full bg-gray-800 w-[30px] h-[30px] flex justify-center items-center hover:bg-gray-900">
                >
            </button>
        </div>
    </div>
</div>

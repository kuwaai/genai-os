@props(['bots'])
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
    <div class="flex overflow-hidden space-x-2 w-full bot-showcase"
        onScroll="this.scrollLeft==0?$(this).prev().fadeOut():$(this).prev().fadeIn(),this.scrollLeft+$(this).width()>=this.scrollWidth?$(this).next().fadeOut():$(this).next().fadeIn()">
        @foreach ($bots as $bot)
            <div onclick="detail_update({{json_encode(array_merge($bot->toArray(), ['image' => $bot->image ? asset(Storage::url($bot->image)) : '/'. config('app.LLM_DEFAULT_IMG')]))}}, {{request()->user()->id == ($bot->owner_id) ? 'false' : 'true'}})" data-modal-target="detail-modal" data-modal-toggle="detail-modal"
                class="overflow-hidden flex flex-col border border-1 rounded-lg cursor-pointer border-gray-700 hover:bg-gray-300 dark:hover:bg-gray-700 min-w-[150px] max-w-[150px] w-[150px] p-2">
                <img id="llm_img" class="rounded-full mx-auto bg-black" width="50px" height="50px"
                    src="{{ $bot->image ? asset(Storage::url($bot->image)) : '/'. config('app.LLM_DEFAULT_IMG') }}">
                <p class="line-clamp-2 text-sm mb-auto">{{ $bot->name }}</p>
                @if ($bot->description)
                    <p class="text-gray-500 dark:text-gray-300 text-xs line-clamp-4 max-w-full flex-1" style="word-wrap:break-word">
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
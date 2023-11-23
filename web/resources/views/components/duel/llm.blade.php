@props(['result'])

@foreach (App\Models\DuelChat::leftJoin('chats', 'duelchat.id', '=', 'chats.dcID')->where('chats.user_id', Auth::user()->id)->orderby('counts', 'desc')->select('duelchat.*', DB::raw('array_agg(chats.llm_id ORDER BY chats.llm_id) as identifier'), DB::raw('count(chats.id) as counts'))->groupBy('duelchat.id')->get()->groupBy('identifier') as $DC)
    @if (array_diff(explode(',', trim($DC->first()->identifier, '{}')), $result->pluck('model_id')->toArray()) == [])
        <div class="mb-2 border border-black dark:border-white border-1 rounded-lg">
            <div class="flex px-2 scrollbar scrollbar-3 overflow-x-auto py-3 border-b border-black dark:border-white">
                @foreach (App\Models\Chats::join('llms', 'llms.id', '=', 'llm_id')->where('user_id', Auth::user()->id)->where('dcID', $DC->first()->id)->orderby('llm_id')->get() as $chat)
                    <div
                        class="mx-1 flex-shrink-0 h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-black flex items-center justify-center overflow-hidden">
                        <a href="{{ $chat->link }}" target="_blank" class="h-full w-full"><img
                                data-tooltip-target="llm_{{ $chat->llm_id }}" data-tooltip-placement="top"
                                class="h-full w-full"
                                src="{{ strpos($chat->image, 'data:image/png;base64') === 0 ? $chat->image : asset(Storage::url($chat->image)) }}"></a>
                        <div id="llm_{{ $chat->llm_id }}" role="tooltip"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                            {{ $chat->name }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="max-h-[182px] overflow-y-auto scrollbar">
                @foreach ($DC as $dc)
                    <div class="m-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                        <a class="flex menu-btn flex text-gray-700 dark:text-white w-full h-12 overflow-y-auto scrollbar dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('duel_id') == $dc->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                            href="{{ route('duel.chat', $dc->id) . (request()->input('limit') > 0 ? '?limit=' . request()->input('limit') : '') }}">
                            <p
                                class="flex-1 flex items-center my-auto justify-center text-center leading-none self-baseline">
                                {{ $dc->name }}</p>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endforeach

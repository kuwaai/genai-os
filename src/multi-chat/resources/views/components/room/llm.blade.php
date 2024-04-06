@props(['result'])
@php
    $DCs = App\Models\ChatRoom::leftJoin('chats', 'chatrooms.id', '=', 'chats.roomID')
        ->where('chats.user_id', Auth::user()->id)
        ->select('chatrooms.*', DB::raw('count(chats.id) as counts'))
        ->groupBy('chatrooms.id');

    // Fetch the ordered identifiers based on `llm_id` for both MySQL and SQLite
    $DCs = $DCs->selectSub(function ($query) {
        if (
            DB::connection()
                ->getPdo()
                ->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite'
        ) {
            $query
                ->from('chats')
                ->selectRaw('group_concat(llm_id) as identifier')
                ->whereColumn('roomID', 'chatrooms.id')
                ->groupBy('roomID')
                ->orderByDesc('llm_id');
        } elseif (
            DB::connection()
                ->getPdo()
                ->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql'
        ) {
            $query
                ->from('chats')
                ->selectRaw('json_group_array(llm_id) as identifier')
                ->whereColumn('roomID', 'chatrooms.id')
                ->orderByDesc('llm_id');
        } else {
            // Assume MySQL
            $query
                ->from('chats')
                ->selectRaw('group_concat(llm_id) as identifier')
                ->whereColumn('roomID', 'chatrooms.id')
                ->orderByDesc('llm_id');
        }
    }, 'identifier')->groupBy('chatrooms.id');

    // Get the final result and group by the ordered identifiers
    $DCs = $DCs->get()->groupBy('identifier');
@endphp
@foreach ($DCs as $DC)
    @if (array_diff(explode(',', trim($DC->first()->identifier, '{}')), $result->pluck('model_id')->toArray()) == [])
        <div class="mb-2 border border-black dark:border-white border-1 rounded-lg">
            @if (request()->user()->hasPerm('Room_update_new_chat'))
                <form method="post"
                    action="{{ route('room.new') . (request()->input('limit') > 0 ? '' : '?limit=' . request()->input('limit')) }}">
                    @csrf
                    <button
                        class="flex px-2 scrollbar rounded-t-lg w-full hover:bg-gray-300 dark:hover:bg-gray-700 scrollbar-3 overflow-x-auto py-3 border-b border-black dark:border-white">
                        @foreach (App\Models\Chats::join('llms', 'llms.id', '=', 'llm_id')->where('user_id', Auth::user()->id)->where('roomID', $DC->first()->id)->orderby('llm_id')->get() as $chat)
                            <div
                                class="mx-1 flex-shrink-0 h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-black flex items-center justify-center overflow-hidden">
                                <div class="h-full w-full"><img data-tooltip-target="llm_{{ $chat->llm_id }}"
                                        data-tooltip-placement="top" class="h-full w-full"
                                        src="{{ strpos($chat->image, 'data:image/png;base64') === 0 ? $chat->image : asset(Storage::url($chat->image)) }}">
                                </div>
                                <div id="llm_{{ $chat->llm_id }}" role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                                    {{ $chat->name }}
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                                <input name="llm[]" value="{{ $chat->llm_id }}" style="display:none;">
                            </div>
                        @endforeach
                    </button>
                </form>
            @else
                <div
                    class="flex px-2 scrollbar rounded-t-lg w-full scrollbar-3 overflow-x-auto py-3 border-b border-black dark:border-white">
                    @foreach (App\Models\Chats::join('llms', 'llms.id', '=', 'llm_id')->where('user_id', Auth::user()->id)->where('roomID', $DC->first()->id)->orderby('llm_id')->get() as $chat)
                        <div
                            class="mx-1 flex-shrink-0 h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-black flex items-center justify-center overflow-hidden">
                            <div class="h-full w-full"><img data-tooltip-target="llm_{{ $chat->llm_id }}"
                                    data-tooltip-placement="top" class="h-full w-full"
                                    src="{{ strpos($chat->image, 'data:image/png;base64') === 0 ? $chat->image : asset(Storage::url($chat->image)) }}">
                            </div>
                            <div id="llm_{{ $chat->llm_id }}" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                                {{ $chat->name }}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                            <input name="llm[]" value="{{ $chat->llm_id }}" style="display:none;">
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="max-h-[182px] overflow-y-auto scrollbar">
                @foreach ($DC as $dc)
                    <div class="m-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                        <a class="flex menu-btn flex text-gray-700 dark:text-white w-full h-12 overflow-y-auto scrollbar dark:hover:bg-gray-700 hover:bg-gray-200 {{ request()->route('room_id') == $dc->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                            href="{{ route('room.chat', $dc->id) . (request()->input('limit') > 0 ? '?limit=' . request()->input('limit') : '') }}">
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

@props(['llms', 'DC', 'result'])
@if (session('llms'))
    @if (
        $DC == null ||
            array_diff(explode(',', trim($DC->first()->identifier, '{}')), $result->pluck('model_id')->toArray()) == []
    )
        <div class="flex flex-1 flex-col border border-black dark:border-white border-1 rounded-lg">
            <div class="flex px-2 scrollbar scrollbar-3 overflow-x-auto py-3 border-b border-black dark:border-white">
                @foreach ($llms as $llm)
                    <div
                        class="mx-1 flex-shrink-0 h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-black flex items-center justify-center overflow-hidden">
                        <div class="h-full w-full"><img data-tooltip-target="llm_{{ $llm->id }}"
                                data-tooltip-placement="top" class="h-full w-full"
                                src="{{ $llm->image ? asset(Storage::url($llm->image)) : '/images/kuwa.png' }}">
                        </div>
                        <div id="llm_{{ $llm->id }}" role="tooltip"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                            {{ $llm->name }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="overflow-y-auto scrollbar">
                @if (request()->user()->hasPerm('Room_update_new_chat'))
                <div class="m-2 border border-green-400 border-1 rounded-lg overflow-hidden">
                    <form method="post"
                        action="{{ route('room.new') . (request()->input('limit') > 0 ? '' : '?limit=' . request()->input('limit')) }}">
                        <div class="flex items-end justify-end">
                            @csrf
                            @foreach ($llms as $llm)
                                <input name="llm[]" value="{{ $llm->id }}" style="display:none;">
                            @endforeach
                            <button type='submit'
                                class="flex menu-btn flex text-green-400 w-full h-12 overflow-y-auto scrollbar dark:hover:bg-gray-700 hover:bg-gray-200 {{ session('llms') ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300">
                                <p
                                    class="flex-1 flex items-center my-auto justify-center text-center leading-none self-baseline">
                                    {{ __('room.button.create_room') }}</p>
                            </button>
                        </div>
                    </form>
                </div>
                @endif
                @if ($DC)
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
                @endif
            </div>
        </div>

    @endif
@else
    @if (array_diff(explode(',', trim($DC->first()->identifier, '{}')), $result->pluck('model_id')->toArray()) == [])
        <div class="flex flex-1 flex-col border border-black dark:border-white border-1 rounded-lg">
            <div class="flex px-2 scrollbar scrollbar-3 overflow-x-auto py-3 border-b border-black dark:border-white">
                @foreach (App\Models\Chats::join('llms', 'llms.id', '=', 'llm_id')->where('user_id', Auth::user()->id)->where('roomID', $DC->first()->id)->orderby('llm_id')->get() as $chat)
                    <div
                        class="mx-1 flex-shrink-0 h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-black flex items-center justify-center overflow-hidden">
                        <div class="h-full w-full"><img data-tooltip-target="llm_{{ $chat->llm_id }}"
                                data-tooltip-placement="top" class="h-full w-full"
                                src="{{ $chat->image ? asset(Storage::url($chat->image)) : '/images/kuwa.png' }}">
                        </div>
                        <div id="llm_{{ $chat->llm_id }}" role="tooltip"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-600">
                            {{ $chat->name }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="overflow-y-auto scrollbar">
                @if (request()->user()->hasPerm('Room_update_new_chat'))
                <div class="m-2 border border-green-400 border-1 rounded-lg overflow-hidden">
                    <form method="post"
                        action="{{ route('room.new') . (request()->input('limit') > 0 ? '' : '?limit=' . request()->input('limit')) }}">
                        <div class="flex items-end justify-end">
                            @csrf
                            @foreach ($llms as $llm)
                                <input name="llm[]" value="{{ $llm->id }}" style="display:none;">
                            @endforeach
                            <button type='submit'
                                class="flex menu-btn flex text-green-400 w-full h-12 overflow-y-auto scrollbar dark:hover:bg-gray-700 hover:bg-gray-200 {{ session('llms') ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300">
                                <p
                                    class="flex-1 flex items-center my-auto justify-center text-center leading-none self-baseline">
                                    {{ __('room.button.create_room') }}</p>
                            </button>
                        </div>
                    </form>
                </div>
                @endif
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
@endif

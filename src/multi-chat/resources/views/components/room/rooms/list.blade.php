@props(['llms' => null, 'DC' => null, 'result' => null, 'channel' => 0, 'extra' => ''])
@if ($result)
    <x-room.llm :result="$result" :extra="$extra" />
@elseif (session('llms'))
    <div class="flex flex-1 flex-col border border-black dark:border-white border-1 rounded-lg overflow-hidden">
        <div
            class="flex px-2 items-center scrollbar scrollbar-3 overflow-x-auto overflow-y-hidden py-3 border-b border-black dark:border-white">
            @foreach ($llms as $llm)
                <div
                    class="mx-1 flex-shrink-0 h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-black flex items-center justify-center overflow-hidden">
                    <div class="h-full w-full">
                        <img data-tooltip-target="{{ $extra }}llm_{{ $llm->id }}_list"
                            data-tooltip-placement="top" class="h-full w-full"
                            src="{{ $llm->image ? asset(Storage::url($llm->image)) : '/' . config('app.LLM_DEFAULT_IMG') }}">
                    </div>
                    <div id="{{ $extra }}llm_{{ $llm->id }}_list" role="tooltip"
                        access_code="{{ $llm->access_code }}"
                        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                        {{ $llm->name }}
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </div>
            @endforeach

            @if (count($llms) == 1)
                <span class="text-center w-full line-clamp-1 text-black dark:text-white">{{ $llms[0]->name }}</span>
            @endif
        </div>
        @if (request()->user()->hasPerm('Room_update_new_chat'))
            <div class="m-2 border border-green-400 border-1 rounded-lg h-12 overflow-hidden">
                <form method="post" action="{{ route('room.new') }}">
                    <div class="flex items-end justify-end">
                        @csrf
                        @foreach ($llms as $llm)
                            <input name="llm[]" value="{{ $llm->id }}" style="display:none;">
                        @endforeach
                        <button type='submit'
                            class="flex menu-btn flex text-green-400 w-full overflow-y-auto scrollbar dark:hover:bg-gray-700 hover:bg-gray-200 {{ session('llms') ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300">
                            <p
                                class="flex-1 flex items-center h-12 my-auto justify-center text-center leading-none self-baseline">
                                {{ __('chat.button.new_chat') }}</p>
                        </button>
                    </div>
                </form>
            </div>
            <hr class="mx-2 mb-1 border-black dark:border-white">
        @endif
        <div class="overflow-y-auto scrollbar flex-1">
            @if ($DC)
                @foreach ($DC->sortbydesc('created_at') as $dc)
                    <div
                        class="m-2 overflow-hidden rounded-lg flex dark:hover:bg-gray-700 hover:bg-gray-200">
                        <a class="menu-btn text-gray-700 dark:text-white w-full flex justify-center items-center overflow-hidden {{ request()->route('room_id') == $dc->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                            href="{{ route('room.chat', $dc->id) }}">
                            <p
                                class="px-4 m-auto text-center leading-none truncate-text overflow-ellipsis overflow-hidden max-h-4">
                                {{ $dc->name }}</p>
                        </a>
                        <button
                            data-dropdown-toggle="{{ $extra }}chat_{{ $channel }}_dropdown_{{ $dc->id }}"
                            class="{{ request()->route('room_id') == $dc->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} p-3 text-black hover:text-black dark:text-white dark:hover:text-gray-300"><svg
                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="icon-md">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3 12C3 10.8954 3.89543 10 5 10C6.10457 10 7 10.8954 7 12C7 13.1046 6.10457 14 5 14C3.89543 14 3 13.1046 3 12ZM10 12C10 10.8954 10.8954 10 12 10C13.1046 10 14 10.8954 14 12C14 13.1046 13.1046 14 12 14C10.8954 14 10 13.1046 10 12ZM17 12C17 10.8954 17.8954 10 19 10C20.1046 10 21 10.8954 21 12C21 13.1046 20.1046 14 19 14C17.8954 14 17 13.1046 17 12Z"
                                    fill="currentColor"></path>
                            </svg></button>
                        <div id="{{ $extra }}chat_{{ $channel }}_dropdown_{{ $dc->id }}"
                            class="z-10 hidden bg-gray-200 border border-1 dark:border-white border-black divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownDefaultButton">
                                <li>
                                    <a href="{{ route('room.share', $dc->id) }}" target="_blank"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white !text-green-500 hover:!text-green-600">{{ __('room.button.share_link') }}</a>
                                </li>
                                @if (request()->user()->hasPerm('Room_delete_chatroom'))
                                    <li>
                                        <a href="#" data-modal-target="delete_chat_modal"
                                            data-modal-toggle="delete_chat_modal"
                                            onclick="event.preventDefault();$('#deleteChat input[name=id]').val({{ $dc->id }});$('#deleteChat h3 span:eq(1)').text('<{{ $dc->name }}>');"
                                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white !text-red-500 hover:!text-red-600">{{ __('chat.button.delete') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@else
    @if ($llms && $DC)
        <div class="flex flex-1 flex-col border border-black dark:border-white border-1 rounded-lg overflow-hidden">
            <div
                class="flex items-center px-2 scrollbar scrollbar-3 overflow-x-auto overflow-y-hidden py-3 border-b border-black dark:border-white">

                @foreach (App\Models\Chats::join('bots', 'bots.id', '=', 'bot_id')->Join('llms', function ($join) {
            $join->on('llms.id', '=', 'bots.model_id');
        })->where('user_id', Auth::user()->id)->where('roomID', $DC->first()->id)->orderby('bot_id')->select('chats.*', 'llms.*', 'bots.*', DB::raw('COALESCE(bots.description, llms.description) as description'), DB::raw('COALESCE(bots.config, llms.config) as config'), DB::raw('COALESCE(bots.image, llms.image) as image'))->get() as $chat)
                    <div
                        class="mx-1 flex-shrink-0 h-5 w-5 rounded-full border border-gray-400 dark:border-gray-900 bg-black flex items-center justify-center overflow-hidden">
                        <div class="h-full w-full"><img
                                data-tooltip-target="{{ $extra }}llm_{{ $chat->bot_id }}_list"
                                data-tooltip-placement="top" class="h-full w-full"
                                src="{{ $chat->image ? asset(Storage::url($chat->image)) : '/' . config('app.LLM_DEFAULT_IMG') }}">
                        </div>
                        <div id="{{ $extra }}llm_{{ $chat->id }}_list" role="tooltip"
                            access_code="{{ $chat->access_code }}"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-500">
                            {{ $chat->name }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                @endforeach

                @if (count($llms) == 1)
                    <span class="text-center w-full line-clamp-1 text-black dark:text-white">{{ $llms[0]->name }}</span>
                @endif
            </div>
            @if (request()->user()->hasPerm('Room_update_new_chat'))
                <div class="m-2 border border-green-400 border-1 rounded-lg h-12 overflow-hidden">
                    <form method="post" action="{{ route('room.new') }}">
                        <div class="flex items-end justify-end">
                            @csrf
                            @foreach ($llms as $llm)
                                <input name="llm[]" value="{{ $llm->id }}" style="display:none;">
                            @endforeach
                            <button type='submit'
                                class="flex menu-btn flex text-green-400 w-full overflow-y-auto scrollbar dark:hover:bg-gray-700 hover:bg-gray-200 {{ session('llms') ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300">
                                <p
                                    class="flex-1 flex items-center h-12 my-auto justify-center text-center leading-none self-baseline">
                                    {{ __('chat.button.new_chat') }}</p>
                            </button>
                        </div>
                    </form>
                </div>
                <hr class="mx-2 mb-1 border-black dark:border-white">
            @endif
            <div class="overflow-y-auto scrollbar flex-1">
                @foreach ($DC->sortbydesc('created_at')  as $dc)
                    <div
                        class="m-2 overflow-hidden border border-black dark:border-white border-1 rounded-lg flex dark:hover:bg-gray-700 hover:bg-gray-200">
                        <a class="menu-btn text-gray-700 dark:text-white w-full flex justify-center items-center overflow-hidden {{ request()->route('room_id') == $dc->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                            href="{{ route('room.chat', $dc->id) }}">
                            <p
                                class="px-4 m-auto text-center leading-none truncate-text overflow-ellipsis overflow-hidden max-h-4">
                                {{ $dc->name }}</p>
                        </a>
                        <button
                            data-dropdown-toggle="{{ $extra }}chat_{{ $channel }}_dropdown_{{ $dc->id }}"
                            class="{{ request()->route('room_id') == $dc->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} p-3 text-black hover:text-black dark:text-white dark:hover:text-gray-300"><svg
                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="icon-md">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3 12C3 10.8954 3.89543 10 5 10C6.10457 10 7 10.8954 7 12C7 13.1046 6.10457 14 5 14C3.89543 14 3 13.1046 3 12ZM10 12C10 10.8954 10.8954 10 12 10C13.1046 10 14 10.8954 14 12C14 13.1046 13.1046 14 12 14C10.8954 14 10 13.1046 10 12ZM17 12C17 10.8954 17.8954 10 19 10C20.1046 10 21 10.8954 21 12C21 13.1046 20.1046 14 19 14C17.8954 14 17 13.1046 17 12Z"
                                    fill="currentColor"></path>
                            </svg></button>
                        <div id="{{ $extra }}chat_{{ $channel }}_dropdown_{{ $dc->id }}"
                            class="z-10 hidden bg-gray-200 border border-1 dark:border-white border-black divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownDefaultButton">
                                <li>
                                    <a href="{{ route('room.share', $dc->id) }}" target="_blank"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white !text-green-500 hover:!text-green-600">{{ __('room.button.share_link') }}</a>
                                </li>
                                @if (request()->user()->hasPerm('Room_delete_chatroom'))
                                    <li>
                                        <a href="#" data-modal-target="delete_chat_modal"
                                            data-modal-toggle="delete_chat_modal"
                                            onclick="event.preventDefault();$('#deleteChat input[name=id]').val({{ $dc->id }});$('#deleteChat h3 span:eq(1)').text('<{{ $dc->name }}>');"
                                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white !text-red-500 hover:!text-red-600">{{ __('chat.button.delete') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif

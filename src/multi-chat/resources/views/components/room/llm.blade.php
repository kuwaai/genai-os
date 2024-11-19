@props(['result', 'extra' => ''])
<div class="flex flex-col overflow-y-auto scrollbar pr-2 flex-1">
    @php
        // Fetch the grouped chatrooms
        $groupedChatRooms = App\Models\ChatRoom::getChatRoomGroup(Auth::user()->id);
    @endphp

    @foreach ($groupedChatRooms as $group => $chatRooms)
        @if (is_array($chatRooms) && count($chatRooms) > 0)
            <!-- Display the group heading -->
            <h3 class="my-2 font-bold text-xs text-gray-800 dark:text-white text-center">{{ $group }}</h3>

            @foreach ($chatRooms as $dc)
                <div class="rounded-lg">
                    <div class="max-h-[182px] overflow-y-auto scrollbar">
                        <div class="overflow-hidden rounded mb-1 flex dark:hover:bg-gray-700 hover:bg-gray-200">
                            <a class="menu-btn flex-1 text-gray-700 px-2 dark:text-white w-full flex justify-start items-center overflow-hidden {{ request()->route('room_id') == $dc->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} transition duration-300"
                                href="{{ route('room.chat', $dc->id) }}">
                                <p class="my-auto leading-none truncate-text overflow-ellipsis overflow-hidden max-h-4">
                                    {{ $dc->name }}
                                </p>
                            </a>

                            <button data-dropdown-toggle="{{ $extra }}chat_dropdown_{{ $dc->id }}"
                                class="{{ request()->route('room_id') == $dc->id ? 'bg-gray-200 dark:bg-gray-700' : '' }} p-1 text-black hover:text-black dark:text-white dark:hover:text-gray-300">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" class="icon-md">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M3 12C3 10.8954 3.89543 10 5 10C6.10457 10 7 10.8954 7 12C7 13.1046 6.10457 14 5 14C3.89543 14 3 13.1046 3 12ZM10 12C10 10.8954 10.8954 10 12 10C13.1046 10 14 10.8954 14 12C14 13.1046 13.1046 14 12 14C10.8954 14 10 13.1046 10 12ZM17 12C17 10.8954 17.8954 10 19 10C20.1046 10 21 10.8954 21 12C21 13.1046 20.1046 14 19 14C17.8954 14 17 13.1046 17 12Z"
                                        fill="currentColor"></path>
                                </svg>
                            </button>
                            <div id="{{ $extra }}chat_dropdown_{{ $dc->id }}"
                                class="z-10 hidden bg-gray-200 border border-1 dark:border-white border-black divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                                <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                    aria-labelledby="dropdownDefaultButton">
                                    <li class='flex px-4 py-2'>
                                        @foreach (App\Models\Bots::getBotsFromIds(explode(',', $dc->identifier)) as $llm)
                                            <div
                                                class="mx-1 flex-shrink-0 h-5 w-5 rounded-full bg-black flex items-center justify-center overflow-hidden">
                                                <img class="h-full w-full"
                                                    src="{{ $llm->image ? asset(Storage::url($llm->image)) : '/' . config('app.LLM_DEFAULT_IMG') }}">
                                            </div>
                                        @endforeach
                                    </li>
                                    <li>
                                        <a href="{{ route('room.share', $dc->id) }}" target="_blank"
                                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white !text-green-500 hover:!text-green-600">{{ __('room.button.share_link') }}</a>
                                    </li>
                                    @if (request()->user()->hasPerm('Room_delete_chatroom'))
                                        <li>
                                            <a href="#" data-modal-target="delete_chat_modal"
                                                data-modal-toggle="delete_chat_modal"
                                                onclick="event.preventDefault();$('#deleteChat input[name=id]').val({{ $dc->id }});$('#deleteChat h3 span:eq(1)').text('<{{ str_replace('\'', '\\\'', $dc->name) }}>');"
                                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white !text-red-500 hover:!text-red-600">{{ __('chat.button.delete') }}</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endforeach
</div>

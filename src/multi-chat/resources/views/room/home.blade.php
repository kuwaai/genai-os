<x-app-layout>
    @php
        $bots = App\Models\Bots::getSortedBots();
        $sorting_methods = App\Models\Bots::getBotSortingMethods();
    @endphp
    @env('arena')
    @php
        $bots = $bots->where('access_code', '!=', 'feedback');
    @endphp
    @endenv
    <x-chat.functions />

    @if (request()->user()->hasPerm('Room_delete_chatroom'))
        <x-room.modal.delete_confirm />
    @endif
    @if (request()->user()->hasPerm('Room_update_new_chat'))
        <x-room.modal.group-chat :result="$bots" :$sorting_methods />
    @endif
    <x-room.rooms.drawer :result="$bots" />
    @if (request()->user()->hasPerm('Room_update_import_chat'))
        <x-chat.modals.import_history :llms="$llms ?? []" />
    @endif
    <div class="flex h-full max-w-7xl mx-auto py-2">
        <div
            class="bg-white dark:bg-gray-800 text-white w-64 hidden sm:flex flex-shrink-0 relative rounded-l-lg overflow-hidden">
            <div class="p-3 flex flex-1 flex-col h-full">
                @if ($bots->count() == 0)
                    <div
                        class="flex-1 h-full flex flex-col w-full text-center rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                        {!! __('chat.hint.no_llms') !!}
                    </div>
                @else
                    <h2 class="block sm:hidden text-xl text-center text-black dark:text-white">
                        {{ __('room.route') }}
                    </h2>
                    <p class="block sm:hidden text-center text-black dark:text-white">
                        {{ __('chat.hint.select_a_chatroom') }}</p>
                    <div class="mb-2">
                        <div class="border border-black dark:border-white border-1 rounded-lg flex overflow-hidden">
                            @if (request()->user()->hasPerm('Room_update_new_chat'))
                                <button data-modal-target="create-model-modal" data-modal-toggle="create-model-modal"
                                    class="flex w-full border-r border-1 border-black dark:border-white menu-btn flex items-center justify-center h-12 dark:hover:bg-gray-700 hover:bg-gray-200 transition duration-300">

                                    <p class="flex-1 text-center text-gray-700 dark:text-white">
                                        {{ __('room.button.create_room') }}
                                    </p>
                                </button>
                            @endif
                            @if (request()->user()->hasPerm('Room_update_import_chat'))
                                <button data-modal-target="importModal" data-modal-toggle="importModal"
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 {{ request()->user()->hasPerm('Room_update_new_chat') ? 'rounded-r-lg ' : 'rounded-lg w-full' }} flex items-center justify-center transition duration-300">
                                    {{ request()->user()->hasPerm('Room_update_new_chat') ? '' : '匯入對話　' }}
                                    <i class="fas fa-file-import"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="mb-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                        <div class="flex">
                            <div class="w-full">
                                <input type="search" oninput="chatroom_filter($(this).val(), $(this).parent().parent().parent().next())"
                                    class="p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-r-lg border-l-gray-50 border-l-2 border border-gray-300 dark:bg-gray-700 dark:border-l-gray-700  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:border-blue-500"
                                    placeholder="{{__('room.label.search_chat')}}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class='flex flex-col overflow-y-auto scrollbar pr-2 flex-1'>
                        <x-room.llm :result="$bots" />
                    </div>
                @endif
            </div>
        </div>
        <div id="histories_hint"
            class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden">
            <button
                class="absolute sm:hidden text-center text-black hover:text-black dark:text-white hover:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-700 focus:ring-4 focus:ring-blue-300 font-medium text-sm px-5 py-5 focus:outline-none dark:focus:ring-blue-800"
                type="button" data-drawer-target="chatlist_drawer" data-drawer-show="chatlist_drawer"
                aria-controls="chatlist_drawer">
                <i class="fas fa-bars"></i>
            </button>
            <div class="flex justify-end">
                <x-sorted-list.control-menu :$sorting_methods
                    btn_class="text-sm leading-4 px-5 py-5 font-medium rounded-md text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100" />
            </div>

            <div
                class="mb-4 grid grid-cols-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 mb-auto overflow-y-auto scrollbar">
                @foreach ($bots as $bot)
                    <x-sorted-list.item html_tag="form" :$sorting_methods :record="$bot" method="post"
                        class="text-black dark:text-white h-[135px] p-2 hover:bg-gray-200 dark:hover:bg-gray-500 transition"
                        action="{{ route('room.new') }}">
                        @csrf
                        <button class="h-full w-full flex flex-col items-center justify-start">
                            <img class="rounded-full mx-auto bg-black" width="50px" height="50px"
                                src="{{ $bot->image ? asset(Storage::url($bot->image)) : '/' . config('app.LLM_DEFAULT_IMG') }}">
                            <p class="text-sm line-clamp-2">{{ $bot->name }}</p>
                            @if ($bot->description)
                                <p class="text-gray-500 dark:text-gray-300 line-clamp-1 max-w-full text-xs">
                                    {{ $bot->description }}</p>
                            @endif
                            <input name="llm[]" value="{{ $bot->id }}" style="display:none;">
                        </button>
                    </x-sorted-list.item>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>

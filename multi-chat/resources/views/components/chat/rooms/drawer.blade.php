@props(['LLM'])

<div id="chatlist_drawer"
    class="fixed sm:hidden top-0 left-0 z-40 h-screen p-4 overflow-hidden transition-transform -translate-x-full bg-white w-80 dark:bg-gray-800"
    tabindex="-1" aria-labelledby="drawer-label">
    <div class="flex flex-col h-full">
        <a href="{{ route('chat.home') }}"
            class="text-center cursor-pointer hover:bg-gray-200 text-black dark:text-white dark:hover:bg-gray-500 rounded p-2 mb-2">←
            {{ __('chat.return_to_menu') }}</a>
        <x-chat.rooms.list :LLM="$LLM" />
    </div>
</div>

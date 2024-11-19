@props(['llms' => null, 'DC' => null, 'result' => null, 'channel' => 0, 'extra' => ''])

<div class="mb-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
    <div class="flex">
        <div class="w-full">
            <input type="search" oninput="chatroom_filter($(this).val(), $(this).parent().parent().parent().next())"
                class="p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-r-lg border-l-gray-50 border-l-2 border border-gray-300 dark:bg-gray-700 dark:border-l-gray-700  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:border-blue-500"
                placeholder="{{ __('room.label.search_chat') }}" autocomplete="off">
        </div>
    </div>
</div>
<x-room.llm :result="$result" :extra="$extra" />

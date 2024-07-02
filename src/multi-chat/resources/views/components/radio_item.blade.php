<!-- resources/views/_radio_item.blade.php -->
<label for="{{ $id }}"
    class="flex p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 font-medium text-gray-900 dark:text-gray-300 flex-1 cursor-pointer">
    <div class="flex items-center h-5">
        <input id="{{ $id }}" name="{{$name}}" type="radio" value="{{ $value }}" onchange="{{ $onchange }}"
            {{ $checked ? 'checked' : '' }}
            class="disabled:bg-gray-900 disabled:opacity-50 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
    </div>
    <div class="ms-2 text-sm flex-1 flex flex-col">
        <div>{{ $title }}</div>
        <p class="font-normal text-gray-500 dark:text-gray-300">{{ $description }}</p>
    </div>
</label>

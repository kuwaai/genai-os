<?php /*
The "sorted-list.control-menu" utilizes dropdown and dropdown-link components to
provide a user-friendly interface for managing bot lists through customizable
sorting options which are preserved across page reloads via local storage in the
browser environment.

The menus with the same id are synchronized.
Target container naming convention: kuwa-sorted-list-<id>
Structure of sorting_methods:
[
    [
        "index_key" => "The data key",
        "name" => "The name to display"
    ]
]
*/ ?>

@props(['sorting_methods' => [], 'id' => 'bots', 'btn_class' => ''])

<x-dropdown {{ $attributes->merge(['align' => 'right', 'width' => '48']) }}>
    <x-slot name="trigger">
        <button onclick="$(this).find('.fa-chevron-up').toggleClass('rotate-180')"
            class="{{ $btn_class }} inline-flex items-center focus:outline-none transition ease-in-out duration-150">
            <div>{{__('room.button.sort_by')}}</div>

            <div class="ml-1">
                <i class="fas fa-chevron-up mx-3 transform duration-500 rotate-180"
                    style="font-size:10px;"></i>
            </div>
        </button>
    </x-slot>

    <x-slot name="content">
        @foreach ($sorting_methods as $method)
            @php
            $onclick = "sortLists('" . $id . "', $(this).data('key'))";
            @endphp
            <x-dropdown-link href="javascript:;" onclick="{{ $onclick }}" class="kuwa-{{ $id }}-sorting-method" data-key="{{ $method['index_key'] }}">
                {{ __($method["name"]) }}
            </x-dropdown-link>
        @endforeach
    </x-slot>
    
</x-dropdown>

@once
<script>
    function toggleSortingOptions(id, key) {
        let sorting_options = $(`.kuwa-${id}-sorting-method`);
        sorting_options.removeClass('underline');
        sorting_options.filter(`*[data-key="${key}"]`).addClass('underline');
        localStorage.setItem(`kuwa-${id}-sort-by`, key);
    }
    function sortLists(id, key) {
        let containers = $(`.kuwa-sorted-list-item-${id}`).parent();
        let data_attr = `${key}-order-index`;
        containers.each((index, container) => {
            let bots = $(container).children();
            bots.sort((a, b) => $(a).data(data_attr) - $(b).data(data_attr))
                .appendTo(container);
        })

        toggleSortingOptions(id, key);
    }
</script>
@endonce

@if(count($sorting_methods) > 0)
<script>
    <?php //Restoring default sorting method from local storage. ?>
    $(window).on('load', function(){
        const default_sorting_method = "{{$sorting_methods[0]['index_key']}}";
        let sorting_method = localStorage.getItem("kuwa-{{ $id }}-sort-by") || default_sorting_method;
        sortLists("{{ $id }}", sorting_method);
    });
</script>
@endif
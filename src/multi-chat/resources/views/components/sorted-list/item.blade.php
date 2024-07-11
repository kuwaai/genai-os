<?php /*

*/ ?>

@props(['id' => 'bots', 'html_tag' => 'div', 'sorting_methods' => [], 'record' => null])

@php
$data_attributes = array_map(
    function($method) use ($record){
        $data_attribute = "{$method['index_key']}-order-index";
        return !empty($record) ? ["data-{$data_attribute}" => $record->$data_attribute] : [];
    },
    $sorting_methods
);
$sorting_attributes = array_merge(['class' => "kuwa-sorted-list-item-{$id}"], ...$data_attributes);
@endphp

<{{ $html_tag }} {{ $attributes->merge($sorting_attributes) }}>
    {{ $slot }}
</{{ $html_tag }}>
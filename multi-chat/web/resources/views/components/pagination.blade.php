@props(['query' => null])
@php
    $queryParams = $query ? '&' . http_build_query($query) : '';
@endphp
@if ($paginator->hasPages())
    <ul class="pagination flex justify-center" role="navigation">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                <span class="page-link inline-block py-2 px-3 text-gray-400" aria-hidden="true">&lsaquo;</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link inline-block py-2 px-3 text-blue-500 hover:text-blue-700"
                    href="{{ $paginator->previousPageUrl() . $queryParams }} " rel="prev"
                    aria-label="@lang('pagination.previous')">&lsaquo;</a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link inline-block py-2 px-3">{{ $element }}</span>
                </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active" aria-current="page">
                            <span
                                class="page-link inline-block py-2 px-3 bg-blue-500 text-white">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link inline-block py-2 px-3 text-blue-500 hover:text-blue-700"
                                href="{{ $url . $queryParams }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link inline-block py-2 px-3 text-blue-500 hover:text-blue-700"
                    href="{{ $paginator->nextPageUrl() . $queryParams }}" rel="next"
                    aria-label="@lang('pagination.next')">&rsaquo;</a>
            </li>
        @else
            <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                <span class="page-link inline-block py-2 px-3 text-gray-400" aria-hidden="true">&rsaquo;</span>
            </li>
        @endif
    </ul>
@endif

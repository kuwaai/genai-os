<!-- resources/views/components/ButtonGroupComponent.blade.php -->

@props(['history', 'showOnFinished'])

<div class="flex space-x-1{{ $showOnFinished ? ' show-on-finished' : '' }}"
    style="{{ $showOnFinished ? 'display:none;' : '' }}">
    <button class="flex text-black hover:bg-gray-400 p-2 h-[32px] w-[32px] justify-center items-center rounded-lg"
        data-tooltip-target="react_copy" data-tooltip-placement="top"
        onclick="copytext(this, histories[{{ $history->id }}])">
        <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
            stroke-linejoin="round" class="icon-sm" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2">
            </path>
            <rect x="8" y="2" width="8" height="4" rx="1" ry="1">
            </rect>
        </svg>
        <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
            stroke-linejoin="round" class="icon-sm" style="display:none;" height="1em" width="1em"
            xmlns="http://www.w3.org/2000/svg">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
    </button>
    @if (
        (request()->routeIs('chat.*') &&
            request()->user()->hasPerm('Chat_update_react_message')) ||
            (request()->routeIs('room.*') &&
                request()->user()->hasPerm('Room_update_react_message')))
        <button data-tooltip-target="react_quote" data-tooltip-placement="top"
            onclick="quote({{ $history->llm_id }}, {{ $history->id }}, this)"
            class="flex text-black hover:bg-gray-400 p-2 h-[32px] w-[32px] justify-center items-center rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" height="8" width="8" viewBox="0 0 512 512">
                <path
                    d="M464 32H336c-26.5 0-48 21.5-48 48v128c0 26.5 21.5 48 48 48h80v64c0 35.3-28.7 64-64 64h-8c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h8c88.4 0 160-71.6 160-160V80c0-26.5-21.5-48-48-48zm-288 0H48C21.5 32 0 53.5 0 80v128c0 26.5 21.5 48 48 48h80v64c0 35.3-28.7 64-64 64h-8c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h8c88.4 0 160-71.6 160-160V80c0-26.5-21.5-48-48-48z" />
            </svg>
            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
                stroke-linejoin="round" class="icon-sm" style="display:none;" height="1em" width="1em"
                xmlns="http://www.w3.org/2000/svg">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </button>
    @endif
    @if (
        (request()->routeIs('chat.*') &&
            request()->user()->hasPerm('Chat_update_feedback')) ||
            (request()->routeIs('room.*') &&
                request()->user()->hasPerm('Room_update_feedback')))
        <button data-tooltip-target="react_like" data-tooltip-placement="top"
            class="flex text-black hover:bg-gray-400 p-2 h-[32px] w-[32px] justify-center items-center rounded-lg {{ $history->nice === true ? 'text-green-600' : 'text-black' }}"
            data-modal-target="feedback" data-modal-toggle="feedback"
            onclick="feedback({{ $history->id }},1,this,{!! htmlspecialchars(
                json_encode(['detail' => $history->detail, 'flags' => $history->flags, 'nice' => $history->nice]),
            ) !!});">
            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
                stroke-linejoin="round" class="icon-sm" height="1em" width="1em"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
                </path>
            </svg>
        </button>
        <button data-tooltip-target="react_dislike" data-tooltip-placement="top"
            class="flex text-black hover:bg-gray-400 p-2 h-[32px] w-[32px] justify-center items-center rounded-lg {{ $history->nice === false ? 'text-red-600' : 'text-black' }}"
            data-modal-target="feedback" data-modal-toggle="feedback"
            onclick="feedback({{ $history->id }},2,this,{!! htmlspecialchars(
                json_encode(['detail' => $history->detail, 'flags' => $history->flags, 'nice' => $history->nice]),
            ) !!});">
            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
                stroke-linejoin="round" class="icon-sm" height="1em" width="1em"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17">
                </path>
            </svg>
        </button>
    @endif
    @if (
        (request()->routeIs('chat.*') &&
            request()->user()->hasPerm('Chat_update_react_message')) ||
            (request()->routeIs('room.*') &&
                request()->user()->hasPerm('Room_update_react_message')))
        <button data-tooltip-target="react_translate" data-tooltip-placement="top"
            onclick="translates(this, {{ $history->id }}, null)"
            class="flex text-black hover:bg-gray-400 p-2 h-[32px] w-[32px] justify-center items-center rounded-lg translates">
            <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" version="1.0">
                <path stroke="null" fill="#010202" clip-rule="evenodd" fill-rule="evenodd"
                    d="m11.30297,9.21129c-0.32992,-0.44209 -0.54766,-0.83799 -0.67963,-1.12832l1.35926,0c-0.13197,0.29033 -0.34311,0.67963 -0.67963,1.12832l0,0zm1.84094,-2.14446l-1.32627,0l0,-0.47508c0,-0.27713 -0.22434,-0.50807 -0.50807,-0.50807c-0.27713,0 -0.50807,0.22434 -0.50807,0.50807l0,0.47508l-1.32627,0c-0.27713,0 -0.50807,0.22434 -0.50807,0.50807c0,0.27713 0.22434,0.50807 0.50807,0.50807l0.06598,0c0.13197,0.37611 0.43549,1.08873 1.09533,1.91352c-0.27713,0.27713 -0.60045,0.56086 -0.98315,0.83799c-0.22434,0.16496 -0.27713,0.48168 -0.11217,0.71262c0.09898,0.13857 0.25074,0.20455 0.4091,0.20455c0.10557,0 0.21115,-0.03299 0.29693,-0.09898c0.4091,-0.29693 0.75881,-0.60705 1.06233,-0.90397c0.30352,0.30352 0.65324,0.60705 1.06233,0.90397c0.09238,0.06598 0.19795,0.09898 0.29693,0.09898c0.15836,0 0.31012,-0.07258 0.4091,-0.20455c0.16496,-0.22434 0.11877,-0.54106 -0.11217,-0.71262c-0.3827,-0.27713 -0.70602,-0.56086 -0.98315,-0.83799c0.65324,-0.82479 0.96336,-1.54401 1.09533,-1.91352l0.06598,0c0.27713,0 0.50807,-0.22434 0.50807,-0.50807c0,-0.28373 -0.23094,-0.50807 -0.50807,-0.50807l0,0zm1.24709,6.73031c0,0.48168 -0.3893,0.87098 -0.87098,0.87098l-6.20904,0c-0.05939,0 -0.11877,-0.0198 -0.17816,-0.03299l1.65618,-2.21045c0.02639,-0.03299 0.03299,-0.07258 0.05279,-0.10557c0.0132,-0.0198 0.02639,-0.03959 0.03299,-0.06598c0.0198,-0.06598 0.02639,-0.13857 0.0132,-0.20455l0,0l-1.25369,-8.51847l5.87913,0c0.48168,0 0.87098,0.3893 0.87098,0.87098l0,9.39605l0.0066,0zm-7.02724,-1.16791l-0.93037,1.24049l-0.13857,-1.24049l1.06893,0zm-6.34761,-1.88713l0,-8.84838c0,-0.48168 0.3893,-0.87098 0.87098,-0.87098l3.6027,0c0.42889,0 0.805,0.32332 0.86438,0.74561l1.43844,9.85133l-5.90552,0c-0.48168,-0.0066 -0.87098,-0.3959 -0.87098,-0.87758l0,0zm12.50387,-8.22814l-6.03089,0l-0.13197,-0.89738c-0.13197,-0.91717 -0.93697,-1.61659 -1.86733,-1.61659l-3.6027,0c-1.04254,0 -1.88713,0.84459 -1.88713,1.88713l0,8.84838c0,1.04254 0.84459,1.88713 1.88713,1.88713l3.39155,0l0.15176,1.37905c0.10557,0.95676 0.91057,1.67598 1.87393,1.67598l6.20904,0c1.04254,0 1.88713,-0.84459 1.88713,-1.88713l0,-9.38945c0.0066,-1.03594 -0.84459,-1.88713 -1.88053,-1.88713l0,0zm-10.06908,4.17675l0.36291,-1.88053c0.03299,-0.15176 0.29033,-0.15176 0.31672,0l0.36291,1.88053l-1.04254,0zm0.52127,-3.02864c-0.56086,0 -1.04914,0.4025 -1.15471,0.95676l-0.77201,4.01179c-0.05279,0.27713 0.12537,0.54106 0.4025,0.59385c0.27053,0.05279 0.54106,-0.12537 0.59385,-0.4025l0.21775,-1.11512l1.43844,0l0.21775,1.11512c0.04619,0.24414 0.25734,0.4091 0.49488,0.4091c0.03299,0 0.06598,0 0.09898,-0.0066c0.27713,-0.05279 0.45529,-0.32332 0.4025,-0.59385l-0.77201,-4.01179c-0.11877,-0.55426 -0.60705,-0.95676 -1.16791,-0.95676l0,0z"
                    class="st0" id="Fill-1" />
            </svg>
            <svg aria-hidden="true"
                class="hidden inline w-8 h-8 text-gray-200 animate-spin dark:text-gray-400 fill-blue-800 w-[16px] h-[16px]"
                viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                    fill="currentColor" />
                <path
                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                    fill="currentFill" />
            </svg>
            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
                stroke-linejoin="round" class="icon-sm hidden" height="1em" width="1em"
                xmlns="http://www.w3.org/2000/svg">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" class="hidden"
                viewBox="0 0 512 512">
                <path
                    d="M504 256c0 137-111 248-248 248S8 393 8 256C8 119.1 119 8 256 8s248 111.1 248 248zm-248 50c-25.4 0-46 20.6-46 46s20.6 46 46 46 46-20.6 46-46-20.6-46-46-46zm-43.7-165.3l7.4 136c.3 6.4 5.6 11.3 12 11.3h48.5c6.4 0 11.6-5 12-11.3l7.4-136c.4-6.9-5.1-12.7-12-12.7h-63.4c-6.9 0-12.4 5.8-12 12.7z" />
            </svg>
        </button>
        <button data-tooltip-target="react_safetyGuard" data-tooltip-placement="top"
            onclick="translates(this, {{ $history->id }}, 'safety-guard')"
            class="flex text-black hover:bg-gray-400 p-2 h-[32px] w-[32px] justify-center items-center rounded-lg translates">
            <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><path d="M256 0c4.6 0 9.2 1 13.4 2.9L457.7 82.8c22 9.3 38.4 31 38.3 57.2c-.5 99.2-41.3 280.7-213.6 363.2c-16.7 8-36.1 8-52.8 0C57.3 420.7 16.5 239.2 16 140c-.1-26.2 16.3-47.9 38.3-57.2L242.7 2.9C246.8 1 251.4 0 256 0zm0 66.8V444.8C394 378 431.1 230.1 432 141.4L256 66.8l0 0z"/></svg>
            <svg aria-hidden="true"
                class="hidden inline w-8 h-8 text-gray-200 animate-spin dark:text-gray-400 fill-blue-800 w-[16px] h-[16px]"
                viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                    fill="currentColor" />
                <path
                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                    fill="currentFill" />
            </svg>
            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
                stroke-linejoin="round" class="icon-sm hidden" height="1em" width="1em"
                xmlns="http://www.w3.org/2000/svg">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" class="hidden"
                viewBox="0 0 512 512">
                <path
                    d="M504 256c0 137-111 248-248 248S8 393 8 256C8 119.1 119 8 256 8s248 111.1 248 248zm-248 50c-25.4 0-46 20.6-46 46s20.6 46 46 46 46-20.6 46-46-20.6-46-46-46zm-43.7-165.3l7.4 136c.3 6.4 5.6 11.3 12 11.3h48.5c6.4 0 11.6-5 12-11.3l7.4-136c.4-6.9-5.1-12.7-12-12.7h-63.4c-6.9 0-12.4 5.8-12 12.7z" />
            </svg>
        </button>
    @endif
</div>

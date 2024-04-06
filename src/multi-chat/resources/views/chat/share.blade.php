<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-hidden h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{App\Models\Chats::findOrFail(request()->route("chat_id"))->name}}</title>

    <!-- Fonts -->
    <link href="{{ asset('css/fontBunny.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/font_awesome..all.min.css') }}" />
    <link href="{{ asset('css/flowbite.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/highlight_default.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/dracula.css') }}" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/socket.io.min.js') }}"></script>
    <script src="{{ asset('js/marked.min.js') }}"></script>
    <script src="{{ asset('js/highlight.min.js') }}"></script>
    <script src="{{ asset('js/purify.min.js') }}"></script>
    <style>
        @media print {
            .new-page {
                page-break-after: auto;
            }
            #chatroom {
                overflow:unset!important;
            }
        }
            #chatroom {
                overflow-y:auto;
            }
    </style>
</head>

<body class="font-sans antialiased h-full">
    @php
        $result = DB::table(function ($query) {
            $query
                ->select(DB::raw('substring(name, 7) as model_id'), 'perm_id')
                ->from('group_permissions')
                ->join('permissions', 'perm_id', '=', 'permissions.id')
                ->where('group_id', Auth()->user()->group_id)
                ->where('name', 'like', 'model_%')
                ->get();
        }, 'tmp')
            ->join('llms', 'llms.id', '=', DB::raw('CAST(tmp.model_id AS SIGNED)'))
            ->select('tmp.*', 'llms.*')
            ->where('llms.enabled', true)
            ->orderby('llms.order')
            ->orderby('llms.created_at')
            ->get();
        $LLM = $result->where('model_id', '=', App\Models\Chats::find(request()->route('chat_id'))->llm_id)->first();

    @endphp

    <x-chat.functions />
    <div class="flex h-full">
        <div id="histories"
            class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 ">
            <x-chat.header :llmId="request()->route('llm_id')" :chatId="request()->route('chat_id')" :LLM="$LLM" :readonly="true" />

            <div id="chatroom" class="flex-1 p-4 flex flex-col-reverse scrollbar bg-gray-200 dark:bg-gray-600">
                <div>
                    @php
                        $tasks = \Illuminate\Support\Facades\Redis::lrange('usertask_' . Auth::user()->id, 0, -1);
                        $refers = App\Models\Histories::where('chat_id', '=', request()->route('chat_id'))
                            ->where('isbot', '=', true)
                            ->select('msg', 'id')
                            ->get();
                    @endphp
                    @foreach (App\Models\Histories::where('chat_id', request()->route('chat_id'))->leftjoin('feedback', 'history_id', '=', 'histories.id')->leftJoin('chats', 'histories.chat_id', '=', 'chats.id')->select(['histories.*', 'chats.llm_id', 'feedback.nice', 'feedback.detail', 'feedback.flags'])->orderby('histories.created_at')->orderby('histories.id', 'desc')->get() as $history)
                        <x-chat.message :history="$history" :tasks="$tasks" :refers="$refers" :readonly="true" />
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</body>

</html>

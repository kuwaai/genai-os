<x-app-layout>
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
            ->join('llms', 'llms.id', '=', DB::raw('CAST(tmp.model_id AS BIGINT)'))
            ->select('tmp.*', 'llms.*')
            ->where('llms.enabled', true)
            ->orderby('llms.order')
            ->orderby('llms.created_at')
            ->get();

        if (request()->route('chat_id')) {
            $LLM = $result->where('model_id', '=', App\Models\Chats::find(request()->route('chat_id'))->llm_id)->first();
        } elseif (request()->route('llm_id')) {
            $LLM = $result->where('model_id', '=', request()->route('llm_id'))->first();
        }
    @endphp

    <x-chat.functions />
    @if (request()->user()->hasPerm('Chat_update_import_chat') && request()->route('llm_id'))
        <x-chat.modals.import_history />
    @elseif (request()->user()->hasPerm('Chat_read_export_chat') && request()->route('chat_id'))
        <x-chat.modals.export_history :name="App\Models\Chats::find(request()->route('chat_id'))->name" />
    @endif
    <div class="flex h-full max-w-7xl mx-auto py-2">
        @if (request()->route('chat_id') || request()->route('llm_id'))
            <x-chat.rooms.drawer :LLM="$LLM" />
            @if (request()->route('chat_id') && request()->user()->hasPerm('Chat_update_feedback'))
                <x-chat.modals.feedback />
            @endif
        @endif
        <div
            class="{{ request()->route('chat_id') || request()->route('llm_id') ? 'w-64 hidden sm:flex' : 'sm:w-64 w-full flex' }} bg-white dark:bg-gray-800 text-black dark:text-white flex-shrink-0 relative rounded-l-lg overflow-hidden">
            <div class="p-3 flex flex-1 flex-col overflow-y-auto scrollbar">
                @if (!($result->count() > 1 && (request()->route('chat_id') || request()->route('llm_id'))))
                    <h2 class="block sm:hidden text-xl text-center">{{ __('Chat') }}</h2>
                    @if ($result->count() == 0)
                        <div
                            class="text-center rounded-r-lg flex flex-1 overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                            {!! __('No available LLM to chat with<br>Please come back later!') !!}
                        </div>
                    @else
                        <p class="block sm:hidden text-center">{{ __('Select a chatroom to begin with') }}</p>
                    @endif
                @endif
                @if ($result->count() > 1 && (request()->route('chat_id') || request()->route('llm_id')))
                    <a href="{{ route('chat.home') }}"
                        class="text-center cursor-pointer hover:bg-gray-200 text-black dark:text-white dark:hover:bg-gray-500 rounded p-2 mb-2">←
                        {{ __('Return to Menu') }}</a>
                @endif
                @if (request()->route('chat_id') || request()->route('llm_id'))
                    <x-chat.rooms.list :LLM="$LLM" />
                @else
                    <x-chat.llm :result="$result" />
                @endif
            </div>
        </div>
        @if (!request()->route('chat_id') && !request()->route('llm_id'))
            <div id="histories_hint"
                class="hidden sm:flex flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                {{ __('Select a chatroom to begin with') }}
            </div>
        @else
            <div id="histories"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl rounded-r-lg overflow-hidden">

                <x-chat.header :llmId="request()->route('llm_id')" :chatId="request()->route('chat_id')" :LLM="$LLM" />

                <div id="chatroom" class="flex-1 p-4 overflow-y-auto flex flex-col-reverse scrollbar">
                    <div
                        class="{{ request()->route('llm_id') &&
                        (App\Models\LLMs::find(request()->route('llm_id'))->access_code == 'feedback' ||
                            (strpos(App\Models\LLMs::find(request()->route('llm_id'))->access_code, 'doc_qa') === 0 ||
                                strpos(App\Models\LLMs::find(request()->route('llm_id'))->access_code, 'doc-qa') === 0))
                            ? 'm-auto'
                            : '' }}">
                        @if (request()->route('chat_id'))
                            @php
                                $tasks = \Illuminate\Support\Facades\Redis::lrange('usertask_' . Auth::user()->id, 0, -1);
                                $refers = App\Models\Histories::where('chat_id', '=', request()->route('chat_id'))
                                    ->where('isbot', '=', true)
                                    ->select('msg', 'id')
                                    ->get();
                            @endphp
                            <script>
                                $refs = []
                            </script>
                            @foreach (App\Models\Histories::where('chat_id', request()->route('chat_id'))->leftjoin('feedback', 'history_id', '=', 'histories.id')->leftJoin('chats', 'histories.chat_id', '=', 'chats.id')->select(['histories.*', 'chats.llm_id', 'feedback.nice', 'feedback.detail', 'feedback.flags'])->orderby('histories.created_at')->orderby('histories.id', 'desc')->get() as $history)
                                <x-chat.message :history="$history" :tasks="$tasks" :refers="$refers" />
                            @endforeach
                        @elseif(request()->route('llm_id') &&
                                (strpos(App\Models\LLMs::find(request()->route('llm_id'))->access_code, 'doc_qa') === 0 ||
                                    strpos(App\Models\LLMs::find(request()->route('llm_id'))->access_code, 'doc-qa') === 0))
                            <p class="m-auto text-black dark:text-white">{!! __('A document is required in order to use this LLM, <br>Please upload a file first.') !!}</p>
                        @elseif(request()->route('llm_id') &&
                                in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['feedback']))
                            <p class="m-auto text-black dark:text-white">{!! __('This is for human feedback, Only import/export is allowed here.') !!}</p>
                        @elseif(request()->route('llm_id') &&
                                strpos(App\Models\LLMs::find(request()->route('llm_id'))->access_code, 'web_qa') === 0)
                        @endif
                        <div style="display:none;"
                            class="bg-red-100 border border-red-400 mt-2 text-red-700 px-4 py-3 rounded relative"
                            id="error_alert" role="alert">
                            <span class="block sm:inline"></span>
                        </div>
                    </div>
                </div>
                @if (
                    (request()->user()->hasPerm('Chat_update_send_message') &&
                        request()->route('chat_id') &&
                        App\Models\LLMs::find(App\Models\Chats::find(request()->route('chat_id'))->llm_id)->access_code !=
                            'feedback') ||
                        (request()->user()->hasPerm('Chat_update_new_chat') &&
                            request()->route('llm_id') &&
                            App\Models\LLMs::find(request()->route('llm_id'))->access_code != 'feedback'))
                    <div
                        class="bg-gray-300 dark:bg-gray-500 p-4 flex flex-col overflow-y-hidden {{ request()->route('llm_id') && (strpos(App\Models\LLMs::find(request()->route('llm_id'))->access_code, 'doc_qa') === 0 || strpos(App\Models\LLMs::find(request()->route('llm_id'))->access_code, 'doc-qa') === 0) ? 'overflow-x-hidden' : '' }}">
                        @if (request()->route('llm_id') &&
                                (strpos(App\Models\LLMs::find(request()->route('llm_id'))->access_code, 'doc_qa') === 0 ||
                                    strpos(App\Models\LLMs::find(request()->route('llm_id'))->access_code, 'doc-qa') === 0))
                            <x-chat.prompt-area.upload :llmId="request()->route('llm_id')" />
                        @elseif (request()->route('llm_id'))
                            <x-chat.prompt-area.request :llmId="request()->route('llm_id')" />
                        @elseif(request()->route('chat_id'))
                            <x-chat.prompt-area.create :chained="\Session::get('chained')" />
                        @endif
                    </div>
                @endif
                <x-chat.prompt-area.chat-script />
            </div>
        @endif
    </div>
</x-app-layout>

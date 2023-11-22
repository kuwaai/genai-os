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

    @if (request()->user()->hasPerm('Chat_update_import_chat') && request()->route('llm_id'))
        <x-chat.modals.import_history />
    @elseif (request()->user()->hasPerm('Chat_read_export_chat') && request()->route('chat_id'))
        <x-chat.modals.export_history />
    @endif
    <div class="flex h-full max-w-7xl mx-auto py-2">
        @if (request()->route('chat_id') || request()->route('llm_id'))
            <x-chat.rooms.drawer :LLM="$LLM" />
        @endif
        <div
            class="{{ request()->route('chat_id') || request()->route('llm_id') ? 'hidden sm:block' : '' }} {{ $result->count() > 1 && (request()->route('chat_id') || request()->route('llm_id')) ? 'w-64' : 'sm:w-64 w-full' }} bg-white dark:bg-gray-800 text-black dark:text-white flex-shrink-0 relative rounded-l-lg overflow-hidden">
            <div
                class="p-3 {{ $result->count() == 1 || request()->route('chat_id') || request()->route('llm_id') ? 'flex flex-col' : '' }} h-full overflow-y-auto scrollbar">
                @if (!($result->count() > 1 && (request()->route('chat_id') || request()->route('llm_id'))))
                    <h2 class="block sm:hidden text-xl text-center">{{ __('Chat') }}</h2>
                    <p class="block sm:hidden text-center">{{ __('Select a chatroom to begin with') }}</p>
                @endif
                @if ($result->count() > 1 && (request()->route('chat_id') || request()->route('llm_id')))
                    <a href="{{ route('chat.home') }}"
                        class="text-center cursor-pointer hover:bg-gray-200 text-black dark:text-white dark:hover:bg-gray-500 rounded p-2 mb-2">←
                        {{ __('Return to Menu') }}</a>
                @endif
                @if ($result->count() == 0)
                    <div
                        class="flex-1 h-full flex flex-col w-full text-center rounded-r-lg overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                        {!! __('No available LLM to chat with<br>Please come back later!') !!}
                    </div>
                @elseif(request()->route('chat_id') || request()->route('llm_id'))
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
                        in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['doc_qa', 'doc_qa_b5'])
                            ? 'm-auto'
                            : '' }}">
                        @if (request()->route('chat_id'))
                            @php
                                $img = App\Models\LLMs::findOrFail(App\Models\Chats::findOrFail(request()->route('chat_id'))->llm_id)->image;
                                $botimgurl = strpos($img, 'data:image/png;base64') === 0 ? $img : asset(Storage::url($img));
                                $tasks = \Illuminate\Support\Facades\Redis::lrange('usertask_' . Auth::user()->id, 0, -1);
                            @endphp
                            @foreach (App\Models\Histories::where('chat_id', request()->route('chat_id'))->leftjoin('feedback', 'history_id', '=', 'histories.id')->select(['histories.*', 'feedback.nice', 'feedback.detail', 'feedback.flags'])->orderby('histories.created_at')->orderby('histories.id', 'desc')->get() as $history)
                                <x-chat.message :history="$history" :tasks="$tasks" :botimgurl="$botimgurl" />
                            @endforeach
                        @elseif(request()->route('llm_id') &&
                                in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['doc_qa', 'doc_qa_b5']))
                            <p class="m-auto text-black dark:text-white">{!! __('A document is required in order to use this LLM, <br>Please upload a file first.') !!}</p>
                        @elseif(request()->route('llm_id') &&
                                in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['web_qa', 'web_qa_b5']))
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
                        request()->route('chat_id')) ||
                        (request()->user()->hasPerm('Chat_update_new_chat') &&
                            request()->route('llm_id')))
                    <div
                        class="bg-gray-300 dark:bg-gray-500 p-4 flex flex-col overflow-y-hidden {{ request()->route('llm_id') && in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['doc_qa', 'doc_qa_b5']) ? 'overflow-x-hidden' : '' }}">
                        @if (request()->route('llm_id') &&
                                in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['doc_qa', 'doc_qa_b5']))
                            <x-chat.prompt-area.upload :llmId="request()->route('llm_id')" />
                        @elseif (request()->route('llm_id'))
                            <x-chat.prompt-area.request :llmId="request()->route('llm_id')" />
                        @elseif(request()->route('chat_id'))
                            <x-chat.prompt-area.create :chained="\Session::get('chained')" />
                        @endif
                    </div>
                @endif
            </div>
            <script>
                function adjustTextareaRows(obj) {
                    obj = $(obj)
                    if (obj.length) {
                        const textarea = obj;
                        const maxRows = parseInt(textarea.attr('max-rows')) || 5;
                        const lineHeight = parseInt(textarea.css('line-height'));

                        textarea.attr('rows', 1);

                        const contentHeight = textarea[0].scrollHeight;
                        const rowsToDisplay = Math.floor(contentHeight / lineHeight);

                        textarea.attr('rows', Math.min(maxRows, rowsToDisplay));
                    }
                }
            </script>
        @endif
    </div>
</x-app-layout>

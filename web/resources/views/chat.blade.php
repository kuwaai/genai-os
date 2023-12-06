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

    <script>
        var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
    </script>
    @if (request()->user()->hasPerm('Chat_update_import_chat') && request()->route('llm_id'))
        <x-chat.modals.import_history />
    @elseif (request()->user()->hasPerm('Chat_read_export_chat') && request()->route('chat_id'))
        <x-chat.modals.export_history />
    @endif
    <div class="flex h-full max-w-7xl mx-auto py-2">
        @if (request()->route('chat_id') || request()->route('llm_id'))
            <x-chat.rooms.drawer :LLM="$LLM" />
            @if (request()->route('chat_id'))
                <x-chat.modals.feedback />

                <script>
                    var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;

                    function translates(node, history_id) {
                        $(node).parent().children("button.translates").addClass("hidden")
                        $(node).removeClass("hidden")


                        $(node).children("svg").addClass("hidden");
                        $(node).children("svg").eq(1).removeClass("hidden");
                        $(node).prop("disabled", true);
                        data = history_id > 0 ? {} : {
                            model: "nihao"
                        }
                        $.ajax({
                            url: '{{ route('chat.translate', '') }}/' + (history_id > 0 ? history_id : -history_id),
                            method: 'GET',
                            data: data,
                            success: function(response) {
                                if (response ==
                                    "[Sorry, There're no machine to process this LLM right now! Please report to Admin or retry later!]"
                                ) {
                                    $(node).children("svg").addClass("hidden");
                                    $(node).children("svg").eq(3).removeClass("hidden");
                                    $("#error_alert >span").text(
                                        "{{ __('[Sorry, There\'re no machine to process this LLM right now! Please report to Admin or retry later!]') }}"
                                        )
                                    $("#error_alert").fadeIn();
                                    setTimeout(function() {
                                        $("#error_alert").fadeOut();
                                        $(node).parent().children("button.translates").each(function() {
                                            $(this).removeClass("hidden");
                                            $(this).children("svg").addClass("hidden");
                                            $(this).children("svg").eq(0).removeClass("hidden");
                                            $(this).prop("disabled", false);
                                        });
                                    }, 3000);
                                } else {
                                    $($(node).parent().parent().children()[0]).text(response + "\n\n[此訊息經由" + (history_id >
                                        0 ?
                                        '該模型' : 'OpenCC') + "嘗試翻譯，瀏覽器重新整理後可復原]");
                                    $(node).parent().children("button.translates").each(function() {
                                        $(this).removeClass("hidden");
                                        $(this).children("svg").addClass("hidden");
                                        $(this).children("svg").eq(0).removeClass("hidden");
                                        $(this).prop("disabled", false);
                                    });
                                    $(node).prop("disabled", true);
                                    $(node).children("svg").addClass("hidden");
                                    $(node).children("svg").eq(2).removeClass("hidden");
                                    $(node).parent().children("button.translates").removeClass("hidden")
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                                $(node).children("svg").addClass("hidden");
                                $(node).children("svg").eq(3).removeClass("hidden");
                                $("#error_alert >span").text(error)
                                $("#error_alert").fadeIn();
                                setTimeout(function() {
                                    $("#error_alert").fadeOut();
                                    $(node).parent().children("button.translates").each(function() {
                                        $(this).removeClass("hidden");
                                        $(this).children("svg").addClass("hidden");
                                        $(this).children("svg").eq(0).removeClass("hidden");
                                        $(this).prop("disabled", false);
                                    });
                                }, 3000);
                            }
                        })
                    }

                    function copytext(node) {
                        var textArea = document.createElement("textarea");
                        textArea.value = node.textContent;

                        document.body.appendChild(textArea);

                        textArea.select();

                        try {
                            document.execCommand("copy");
                        } catch (err) {
                            console.log("Copy not supported or failed: ", err);
                        }

                        document.body.removeChild(textArea);

                        $(node).parent().children().eq(1).children().eq(0).children().eq(0).hide();
                        $(node).parent().children().eq(1).children().eq(0).children().eq(1).show();
                        setTimeout(function() {
                            $(node).parent().children().eq(1).children().eq(0).children().eq(0).show();
                            $(node).parent().children().eq(1).children().eq(0).children().eq(1).hide();
                        }, 3000);
                    }
                </script>
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
                        in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['doc_qa', 'doc_qa_b5'])
                            ? 'm-auto'
                            : '' }}">
                        @if (request()->route('chat_id'))
                            @php
                                $tasks = \Illuminate\Support\Facades\Redis::lrange('usertask_' . Auth::user()->id, 0, -1);
                            @endphp
                            @foreach (App\Models\Histories::where('chat_id', request()->route('chat_id'))->leftjoin('feedback', 'history_id', '=', 'histories.id')->leftJoin('chats', 'histories.chat_id', '=', 'chats.id')->select(['histories.*', 'chats.llm_id', 'feedback.nice', 'feedback.detail', 'feedback.flags'])->orderby('histories.created_at')->orderby('histories.id', 'desc')->get() as $history)
                                <x-chat.message :history="$history" :tasks="$tasks" />
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

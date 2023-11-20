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
                            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
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
            @if (request()->route('chat_id'))
                <x-chat.modals.delete_confirm />
                <x-chat.modals.feedback />
                <script>
                    var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;

                    function chain_toggle() {
                        $.get("{{ route('chat.chain') }}", {
                            switch: $('#chained').prop('disabled')
                        }, function() {
                            $('#chained').prop('disabled', !$('#chained').prop('disabled'));
                            $('#chain_btn').toggleClass('bg-green-500 hover:bg-green-600 bg-red-600 hover:bg-red-700');
                            $('#chain_btn').text($('#chained').prop('disabled') ? '{{ __('Unchain') }}' :
                                '{{ __('Chained') }}')
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

                    function feedback(id, type, obj, data) {
                        $(obj).parent().find("button:not(:first)").removeClass("bg-gray-400")
                        adjustTextareaRows($("#feedbacks"));
                        // clear form
                        $("#feedback_form input:not(:first), #feedback_form textarea").each(function() {
                            if ($(this).is(":checkbox")) {
                                $(this).prop("checked", false);
                            } else {
                                $(this).val("");
                            }
                        });
                        $("#feedback_form input:eq(1)").val(id) //History id
                        $("#feedback_form input:eq(2)").val(type) //feedback type
                        $("#feedback svg").eq(type - 1).parent().show();
                        $(obj).parent().find(">button:not(:first)").removeClass("text-green-600 text-red-600").addClass("text-black")
                        $(obj).toggleClass("text-black " + (type == 1 ? "text-green-600" : "text-red-600"))
                        $("#feedback svg").eq(type % 2).parent().hide();
                        $("#feedback_form >div:not(:last)").hide()
                        $("#feedback_form >div:not(:last) >input").prop("disabled", true)
                        $("#feedback_form >div." + ["good", "bad"][type - 1]).show()
                        $("#feedback_form >div." + ["good", "bad"][type - 1] + " >input").prop("disabled", false)
                        if (type == 1) {
                            //Good
                            $("#feedback_form textarea").attr("placeholder", "{{ __('What do you like about the response?') }}")
                        } else if (type == 2) {
                            //Bad
                            $("#feedback_form >div.bad").show()
                            $("#feedback_form textarea").attr("placeholder",
                                "{{ __('What was the problem with this response? How can it be improved?') }}")
                        }
                        if (data) {
                            if (data['nice'] === true && type == 1) {
                                if (data["detail"]) {
                                    $("#feedback_form textarea").val(data["detail"]);
                                }
                            } else if (data['nice'] === false && type == 2) {
                                if (data["detail"]) {
                                    $("#feedback_form textarea").val(data["detail"]);
                                }

                            } else {
                                $.post("{{ route('chat.feedback') }}", {
                                    type: type,
                                    history_id: id,
                                    init: true,
                                    _token: $("input[name='_token']").val()
                                })
                            }
                            if (data["flags"]) {
                                data["flags"] = JSON.parse(data["flags"]);
                                data["flags"].forEach(flagValue => {
                                    $('input[name="feedback[]"][value="' + flagValue + '"]').prop('checked', true);
                                });
                            }
                        }
                    }

                    

                    function saveChat() {
                        input = $("#chatHeader >p >input:eq(0)")
                        if (input.val() != input.attr("old")) {
                            $("#editChat input:eq(2)").val(input.val())
                            $("#editChat").submit();
                        }
                        $("#chatHeader button").find('.fa-pen').parent().removeClass('hidden');
                        $("#chatHeader button").find('.fa-save').parent().addClass('hidden');
                        $("#chatHeader >p").text(input.val())
                    }

                    $("#chat_input").val("訊息處理中...請稍後...")
                    $chattable = false
                    $("#prompt_area").submit(function(event) {
                        event.preventDefault();
                        if ($chattable) {
                            this.submit();
                            $chattable = false
                        }
                        $("#submit_msg").hide()
                        if (!isMac) $("#chat_input").val("訊息處理中...請稍後...")
                        $("#chat_input").prop("readonly", true)
                    })
                    task = new EventSource("{{ route('chat.sse') }}", {
                        withCredentials: false
                    });
                    task.addEventListener('error', error => {
                        task.close();
                    });
                    task.addEventListener('message', event => {
                        if (event.data == "finished") {
                            $chattable = true
                            $("#submit_msg").show()
                            $("#chat_input").val("")
                            $("#chat_input").prop("readonly", false)
                            adjustTextareaRows($("#chat_input"))
                            $(".show-on-finished").attr("style", "")
                        } else {
                            data = JSON.parse(event.data)
                            number = parseInt(data["history_id"]);
                            msg = data["msg"];
                            msg = msg.replace(
                                "[Oops, the LLM returned empty message, please try again later or report to admins!]",
                                "{{ __('[Oops, the LLM returned empty message, please try again later or report to admins!]') }}"
                            )
                            msg = msg.replace("[Sorry, something is broken, please try again later!]",
                                "{{ __('[Sorry, something is broken, please try again later!]') }}")
                            msg = msg.replace(
                                "[Sorry, There're no machine to process this LLM right now! Please report to Admin or retry later!]",
                                "{{ __('[Sorry, There\'re no machine to process this LLM right now! Please report to Admin or retry later!]') }}"
                            )
                            $('#task_' + number).text(msg);
                        }
                    });
                </script>
            @endif
            <script>
                var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;

                function isValidURL(url) {
                    // Regular expression for a simple URL pattern (you can make it more complex if needed)
                    var urlPattern = /^(https?|ftp):\/\/(-\.)?([^\s/?\.#-]+\.?)+([^\s]*)$/;
                    return urlPattern.test(url);
                }

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

                function uploadcheck() {
                    if ($("#upload")[0].files && $("#upload")[0].files.length > 0 && $("#upload")[0].files[0].size <= 10 * 1024 *
                        1024) {
                        $("#upload").parent().submit();
                    } else {
                        $("#upload_btn").text('{{ __('File Too Large') }}')
                        $("#upload_btn").toggleClass("bg-green-500 hover:bg-green-600 bg-red-600 hover:bg-red-700")
                        $("#upload").val("");


                        setTimeout(function() {
                            $("#upload_btn").text('{{ __('Upload file') }}')
                            $("#upload_btn").toggleClass("bg-green-500 hover:bg-green-600 bg-red-600 hover:bg-red-700")
                        }, 3000);
                    }
                }

                if ($("#chat_input")) {
                    $("#chat_input").focus();
                    $("#chat_input").on("keydown", function(event) {
                        if (!isMac && event.key === "Enter" && !event.shiftKey) {
                            event.preventDefault();

                            $("#prompt_area").submit();
                        } else if (event.key === "Enter" && event.shiftKey) {
                            event.preventDefault();
                            var cursorPosition = this.selectionStart;
                            $(this).val($(this).val().substring(0, cursorPosition) + "\n" + $(this).val().substring(
                                cursorPosition));
                            this.selectionStart = this.selectionEnd = cursorPosition + 1;
                        }
                        adjustTextareaRows($("#chat_input"));
                    });
                    adjustTextareaRows($("#chat_input"));
                }

                function stringToUnicode(inputString) {
                    return inputString.replace(/./g, function(char) {
                        return '\\u' + char.charCodeAt(0).toString(16).padStart(4, '0');
                    });
                }
                @if (request()->route('llm_id'))
                    @if (in_array(App\Models\LLMs::find(request()->route('llm_id'))->access_code, ['web_qa', 'web_qa_b5']))
                        if ($("#prompt_area")) {
                            $("#prompt_area").on("submit", function(event) {
                                event.preventDefault();
                                if (isValidURL($("#chat_input").val().trim())) {
                                    $("#prompt_area")[0].submit()
                                } else {
                                    $("#error_alert >span").text(
                                        "{{ __('The first message for this LLM allows URL only!') }}")
                                    $("#error_alert").fadeIn();
                                    setTimeout(function() {
                                        $("#error_alert").fadeOut();
                                    }, 3000);
                                }
                            })
                        }
                    @endif
                    function import_chat() {

                    }
                @endif
            </script>
        @endif
    </div>
</x-app-layout>

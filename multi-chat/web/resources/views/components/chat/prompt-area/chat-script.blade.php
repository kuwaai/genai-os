<script>
    if ($("#chat_input")) {
        $("#chat_input").prop("readonly", true)
        $("#chat_input").val("訊息處理中...請稍後...")
        $("#submit_msg").hide()
        if ($("#abort_btn")) $("#abort_btn").hide()
        $chattable = false
    }
    if ($("#prompt_area")) {
        $("#prompt_area").submit(function(event) {
            event.preventDefault();

            @if (request()->route('llm_id'))
                @if (strpos(App\Models\LLMs::find(request()->route('llm_id'))->access_code, 'web_qa') === 0)
                    if ($("#prompt_area")) {
                        $("#prompt_area").on("submit", function(event) {
                            event.preventDefault();
                            if (isValidURL($("#chat_input").val().trim())) {
                                $("#prompt_area")[0].submit()
                            } else {
                                $("#error_alert >span").text(
                                    "{{ __('chat.hint.first_url') }}")
                                $("#error_alert").fadeIn();
                                setTimeout(function() {
                                    $("#error_alert").fadeOut();
                                }, 3000);
                            }
                        })
                    }
                @endif
            @endif

            if ($chattable && $("#chat_input").val().trim() == "" && quoted.length == 1) {
                $("#chat_input").val(`"""${histories[quoted[0][1]]}"""`)
                this.submit();
                $chattable = false
                $("#submit_msg").hide()
                if (!isMac) {
                    $("#chat_input").val("訊息處理中...請稍後...")
                }
                $("#chat_input").prop("readonly", true)
            } else if ($chattable && (($("#chat_input").val().trim() != "") || quoted.length != 0)) {
                tmp = ""
                for (var i in quoted) {
                    @env('arena')
                        tmp += `"""${histories[quoted[i][1]]}"""\n`
                    @else
                        tmp +=
                            `${$("#llm_" + quoted[i][0] + "_chat").text().trim()}:"""${histories[quoted[i][1]]}"""\n`
                    @endenv
                }
                tmp = tmp.trim()
                $("#chat_input").val($("#chat_input").val().trim() + "\n" + tmp)
                this.submit();
                $chattable = false
                $("#submit_msg").hide()
                if (!isMac) {
                    $("#chat_input").val("訊息處理中...請稍後...")
                }
                $("#chat_input").prop("readonly", true)
            } else {
                if ($("#chat_input").val().trim() == "") {
                    $("#error_alert >span").text(
                        "{{ __('chat.hint.send.empty') }}")
                } else if (!$chattable) {
                    $("#error_alert >span").text(
                        "{{ __('chat.hint.send.still_processing') }}")
                } else {
                    $("#error_alert >span").text("{{ __('chat.hint.please_refresh') }}")
                }
                $("#error_alert").fadeIn();
                setTimeout(function() {
                    $("#error_alert").fadeOut();
                }, 3000);
            }
        })
    }
    task = new EventSource("{{ route('chat.sse') }}", {
        withCredentials: false
    });
    task.addEventListener('error', error => {
        task.close();
    });
    task.addEventListener('message', event => {
        if (event.data == "finished" && $("#submit_msg")) {
            $chattable = true
            $("#submit_msg").show()
            if ($("#abort_btn")) $("#abort_btn").hide();
            $("#chat_input").val("")
            $("#chat_input").prop("readonly", false)
            adjustTextareaRows($("#chat_input"))
            $(".show-on-finished").attr("style", "")
            hljs.configure({
                languages: hljs.listLanguages()
            }); //enable auto detect
            $('#chatroom div.text-sm.space-y-3.break-words pre >div').remove()
            $('#chatroom div.text-sm.space-y-3.break-words pre code').each(function() {
                $(this).text($(this).text())
                $(this)[0].dataset.highlighted = '';
                $(this)[0].classList = ""
                hljs.highlightElement($(this)[0]);
            });
            $('#chatroom div.text-sm.space-y-3.break-words pre').each(function() {
                let languageClass = '';
                $(this).children("code")[0].classList.forEach(cName => {
                    if (cName.startsWith('language-')) {
                        languageClass = cName.replace('language-', '');
                        return;
                    }
                })
                verilog = languageClass == "verilog" ?
                    `<button onclick="compileVerilog(this)" class="flex items-center hover:bg-gray-900 px-2 py-2 "><span>{{ __('chat.button.verilog_compile_test') }}</span></button>` :
                    ``
                $(this).prepend(
                    `<div class="flex items-center text-gray-200 bg-gray-800 rounded-t-lg overflow-hidden">
<span class="pl-4 py-2 mr-auto"><input class="bg-gray-900" list="language_list" oninput="switchLang(this)" value="${languageClass}"></span>
${verilog}
<button onclick="copytext(this, $(this).parent().parent().children('code').text().trim())"
class="flex items-center px-2 py-2 hover:bg-gray-900"><svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
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
</svg><span>{{ __('Copy') }}</span></button></div>`
                )
            })
        } else {
            data = JSON.parse(event.data)
            number = parseInt(data["history_id"]);
            $('#task_' + number).text(data["msg"]);
            histories[number] = $("#history_" + number + " div.text-sm.space-y-3.break-words")
                .text()
            hljs.configure({
                languages: []
            }); // disable auto detect
            chatroomFormatter($("#history_" + data["history_id"])[0]);
            if ($("#abort_btn")) $("#abort_btn").show();
        }
    });
    if ($("#chat_input")) {
        $("#chat_input").focus();
        $("#chat_input").on("keydown", function(event) {
            if (!isMac && event.key === "Enter" && !event.shiftKey) {
                event.preventDefault();
                if ($("#prompt_area")) {
                    $("#prompt_area").submit();
                }
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
</script>

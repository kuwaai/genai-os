<script>
    if ($("#chat_input")) {
        $("#chat_input").prop("readonly", true)
        $("#chat_input").val("訊息處理中...請稍後...")
        $chattable = false
    }
    if ($("#prompt_area")) {
        $("#prompt_area").submit(function(event) {
            event.preventDefault();
            if ($chattable && $("#chat_input").val().trim() == "" && quoted.length == 1) {
                $("#chat_input").val(histories[quoted[0][1]])
                this.submit();
                $chattable = false
                $("#submit_msg").hide()
                $("#chat_input").val("訊息處理中...請稍後...")
                $("#chat_input").prop("readonly", true)
            } else if ($chattable && (($("#chat_input").val().trim() != "") || quoted.length != 0)) {
                tmp = ""
                for (var i in quoted) {
                    tmp +=
                        `${$("#llm_" + quoted[i][0] + "_chat").text().trim()}:"""${histories[quoted[i][1]]}"""\n`
                }
                tmp = tmp.trim()
                $("#chat_input").val($("#chat_input").val().trim() + "\n" + tmp)
                this.submit();
                $chattable = false
                $("#submit_msg").hide()
                $("#chat_input").val("訊息處理中...請稍後...")
                $("#chat_input").prop("readonly", true)
            } else {
                if ($("#chat_input").val().trim() == "") {
                    $("#error_alert >span").text(
                        "{{ __('You cannot send a empty message!') }}")
                } else if (!$chattable) {
                    $("#error_alert >span").text(
                        "{{ __('Still processing a request, If this take too long, Please refresh.') }}")
                } else {
                    $("#error_alert >span").text("{{ __('Something went wrong! Please refresh the page.') }}")
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
            $("#chat_input").val("")
            $("#chat_input").prop("readonly", false)
            adjustTextareaRows($("#chat_input"))
            $(".show-on-finished").attr("style", "")
        } else {
            data = JSON.parse(event.data)
            number = parseInt(data["history_id"]);
            $('#task_' + number).text(data["msg"]);
            histories[number] = $("#history_" + number + " div.text-sm.space-y-3.break-words")
                .text()
            chatroomFormatter($("#history_" + data["history_id"])[0]);
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

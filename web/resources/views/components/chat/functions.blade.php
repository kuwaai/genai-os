<script>
    var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
    var histories = {}

    function chatroomFormatter(node) {
        $(node).find('div.text-sm.space-y-3.break-words').each(function() {
            if ($(this).text() == "<pending holder>") {
                $(this).html(`<svg aria-hidden="true"
class="inline w-8 h-8 text-gray-200 animate-spin dark:text-gray-400 fill-blue-800 w-[16px] h-[16px]"
viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
<path
d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
fill="currentColor" />
<path
d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
fill="currentFill" />
</svg>`);
            } else {
                $(this).html(DOMPurify.sanitize((marked.parse($(this).text()))));
            }
        });
        $(node).find('div.text-sm.space-y-3.break-words table').addClass('table-auto');
        $(node).find('div.text-sm.space-y-3.break-words table *').addClass(
            'border border-2 border-gray-500 border-solid p-1');
        $(node).find('div.text-sm.space-y-3.break-words ul').addClass('list-inside list-disc');
        $(node).find('div.text-sm.space-y-3.break-words > p').addClass('whitespace-pre-wrap');
        $(node).find('div.text-sm.space-y-3.break-words a').addClass('text-blue-600 hover:text-blue-800').prop('target',
            '_blank');
        $(node).find('div.text-sm.space-y-3.break-words pre code').each(function() {
            hljs.highlightElement($(this)[0]);
        });
        $(node).find('div.text-sm.space-y-3.break-words pre code').addClass("scrollbar scrollbar-3 rounded-b-lg")
        $(node).find('div.text-sm.space-y-3.break-words pre').each(function() {
            let languageClass = '';
            $(this).children("code")[0].classList.forEach(cName => {
                if (cName.startsWith('language-')) {
                    languageClass = cName.replace('language-', '');
                    return;
                }
            })
            $(this).prepend(
                `<div class="flex items-center text-gray-200 bg-gray-800 px-4 py-2 rounded-t-lg">
<span class="mr-auto">${languageClass}</span>
<button onclick="copytext(this, $(this).parent().parent().children('code').text().trim())"
class="flex items-center"><svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"
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
</svg><span class="ml-2">{{ __('Copy') }}</span></button></div>`
            )
        })

        $(node).find("div.text-sm.space-y-3.break-words h5").each(function() {
            var $h5 = $(this);
            var pattern = /<%ref-(\d+)%>/;
            var match = $h5.text().match(pattern);
            if (match) {
                var refNumber = match[1];
                $msg = $("#history_" + refNumber).text().trim()
                $h5.html(
                    `<button class="bg-gray-700 rounded p-2 hover:bg-gray-800" data-tooltip-target='ref-tooltip' data-tooltip-placement='top' onmouseover="refToolTip(${refNumber})" onclick="$('#chatroom').animate({scrollTop:$('#history_${refNumber}').offset().top - $('#chatroom').offset().top + $('#chatroom').scrollTop() }, 300);">${$msg.substring(0, 30) + ($msg.length < 30 ? "" : "...")}</button>`
                );
            }
        });
    }

    function refToolTip(refID) {
        $msg = $("#history_" + refID).text().trim()
        $('#ref-tooltip').text($msg);
    }
    var quoted = [];
    function quote(llm_id, history_id, node) {
        if ($("#chat_input").val() != "訊息處理中...請稍後..." && !quoted.includes(history_id)) {
            quoted.push(history_id);
            $('#chat_input').val(($('#chat_input').val() + '\n' + $(`#llm_${llm_id}_chat`).text().trim() +
                    ':「' + $(`#history_${history_id} div.text-sm.space-y-3.break-words`).text().trim() + '」\n')
                .trim());
            adjustTextareaRows($('#chat_input'))
            $(node).children("svg").eq(0).hide();
            $(node).children("svg").eq(1).show();
            if ($(node).children("span")) {
                $(node).children("span").text("{{ __('Copied') }}")
            }
            setTimeout(function() {
                $(node).children("svg").eq(0).show();
                $(node).children("svg").eq(1).hide();
                if ($(node).children("span")) {
                    $(node).children("span").text("{{ __('Copy') }}")
                }
            }, 3000);
        }
    }

    function translates(node, history_id) {
        $(node).parent().children("button.translates").addClass("hidden")
        $(node).removeClass("hidden")


        $(node).children("svg").addClass("hidden");
        $(node).children("svg").eq(1).removeClass("hidden");
        $(node).prop("disabled", true);
        $.ajax({
            url: '{{ route('chat.translate', '') }}/' + history_id,
            method: 'GET',
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
                    $($(node).parent().parent().children()[0]).text(response + "\n\n[此訊息經由該模型嘗試翻譯，瀏覽器重新整理後可復原]");
                    histories[history_id] = $($(node).parent().parent()
                        .children()[0]).text()
                    chatroomFormatter($("#history_" + history_id));
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

    function copytext(node, text) {
        var textArea = document.createElement("textarea");
        textArea.value = text;

        document.body.appendChild(textArea);

        textArea.select();

        try {
            document.execCommand("copy");
        } catch (err) {
            console.log("Copy not supported or failed: ", err);
        }

        document.body.removeChild(textArea);

        $(node).children("svg").eq(0).hide();
        $(node).children("svg").eq(1).show();
        if ($(node).children("span")) {
            $(node).children("span").text("{{ __('Copied') }}")
        }
        setTimeout(function() {
            $(node).children("svg").eq(0).show();
            $(node).children("svg").eq(1).hide();
            if ($(node).children("span")) {
                $(node).children("span").text("{{ __('Copy') }}")
            }
        }, 3000);
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
</script>

<script>
    var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
    var histories = {}


    function translate_msg($msg) {
        let msgTranslations = {
            "[Oops, the LLM returned empty message, please try again later or report to admins!]": "{{ __('[Oops, the LLM returned empty message, please try again later or report to admins!]') }}",
            "[Sorry, something is broken, please try again later!]": "{{ __('[Sorry, something is broken, please try again later!]') }}",
            "[Sorry, There're no machine to process this LLM right now! Please report to Admin or retry later!]": "{{ __('[Sorry, There\'re no machine to process this LLM right now! Please report to Admin or retry later!]') }}",
            "[Sorry, The input message is too huge!]": "{{ __('[Sorry, The input message is too huge!]') }}"
        };

        for (let original in msgTranslations) {
            if (msgTranslations.hasOwnProperty(original)) {
                $msg = $msg.replace(original, msgTranslations[original]);
            }
        }
        return $msg;
    }

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
                let warnings = /<<<WARNING>>>([\s\S]*?)<<<\/WARNING>>>/g.exec($(this).text());
                $(this).text($(this).text().replace(/<<<WARNING>>>[\s\S]*?<<<\/WARNING>>>/g, ''));
                $msg = $(this).text()
                if ($(this).hasClass("bot-msg")) {
                    if (warnings) {
                        warnings = warnings[1].split("\n")
                        var listItems = warnings.map(function(line) {
                            return `<div class="warning_msg mt-2 flex items-center p-4 text-sm text-gray-800 rounded-lg bg-gray-50 dark:bg-gray-800 dark:text-gray-300" role="alert">
  <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
  </svg>
  <span class="sr-only">Info</span>
  <div class="ml-2">
    <span class="font-medium">` + line + `</span>
  </div>
</div>`;
                        });
                        //Clear previous warning
                        $(this).parent().find("div.warning_msg").remove();
                        // Append the list items after the target element
                        $(this).after(listItems.join(''));
                        console.log(warnings);
                    }
                    $msg = translate_msg($msg);
                }
                $(this).html(DOMPurify.sanitize((marked.parse($msg))));

                $(node).find('div.text-sm.space-y-3.break-words table').addClass('table-auto');
                $(node).find('div.text-sm.space-y-3.break-words table *').addClass(
                    'border border-2 border-gray-500 border-solid p-1');
                $(node).find('div.text-sm.space-y-3.break-words ul').addClass('list-inside list-disc');
                $(node).find('div.text-sm.space-y-3.break-words ol').addClass('list-inside list-decimal');
                $(node).find('div.text-sm.space-y-3.break-words > p').addClass('whitespace-pre-wrap');
                $(node).find('div.text-sm.space-y-3.break-words a').addClass(
                    'text-blue-700 hover:text-blue-900').prop('target',
                    '_blank');
                $(node).find('div.text-sm.space-y-3.break-words pre code').each(function() {
                    hljs.highlightElement($(this)[0]);
                });
                $(node).find('div.text-sm.space-y-3.break-words pre code').addClass(
                    "scrollbar scrollbar-3 rounded-b-lg")
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
                        $msg = $("#history_" + refNumber + " div.text-sm.space-y-3.break-words").text()
                            .trim()
                        $h5.html(
                            `<button class="bg-gray-700 rounded p-2 hover:bg-gray-800" data-tooltip-target='ref-tooltip' data-tooltip-placement='top' onmouseover="refToolTip(${refNumber})" onclick="scrollToRef(${refNumber})">${$msg.substring(0, 30) + ($msg.length < 30 ? "" : "...")}</button>`
                        );
                    }
                });
            }
        });
    }

    function scrollToRef(refNumber) {
        $('#chatroom').animate({
            scrollTop: $(`#history_${refNumber}`).offset().top - $('#chatroom').offset().top + $('#chatroom')
                .scrollTop()
        }, 300);
        $(`#history_${refNumber} div[tabindex=0]`).focus();
    }

    function toggleHighlight(node, flag) {
        if ($(node).find(".bot-msg").length != 0) {
            if ($(node).find(".chain-msg").length != 0) {
                let $trigger = true;
                $prevMsgs = $(node).parent().parent().prevAll('div').filter(function() {
                    if ($(this).find("div div.bot-msg").length == 0) {
                        if ($trigger) {
                            $trigger = false;
                            return true
                        }
                        return false
                    } else if ($(this).find("img").attr("data-tooltip-target") == $(node).parent().parent()
                        .find(
                            "div img").attr("data-tooltip-target")) {
                        $trigger = true;
                        return true
                    }
                    return false
                }).find("div div");

                if (flag) {
                    $($prevMsgs).addClass("!bg-orange-400");
                    $(node).addClass("!bg-yellow-300");
                } else {
                    $($prevMsgs).removeClass("!bg-orange-400");
                    $(node).removeClass("!bg-yellow-300");
                }
            }
            $prevUser = $(node).parent().parent().prevAll('div').filter(function() {
                return $(this).find('div div div.bot-msg').length == 0;
            }).first()
            $prevUserMsg = $prevUser.find('div div div').text().trim()
            $refRecord = $(node).parent().parent().prevAll('div').filter(function() {
                $msgWindow = $(this).find('div div div.bot-msg');
                return $msgWindow.length != 0 && $msgWindow.text().trim() == $prevUserMsg;
            }).first().find("div div")
            if ($refRecord.length > 0) {
                if (flag) {
                    $($refRecord).addClass("!bg-orange-400");
                    $(node).addClass("!bg-yellow-300");
                } else {
                    $($refRecord).removeClass("!bg-orange-400");
                    $(node).removeClass("!bg-yellow-300");
                }
            } else {
                $prevUser = $prevUser.find("div div")
                if (flag) {
                    $($prevUser).addClass("!bg-orange-400");
                    $(node).addClass("!bg-yellow-300");
                } else {
                    $($prevUser).removeClass("!bg-orange-400");
                    $(node).removeClass("!bg-yellow-300");
                }
            }
        }
    }

    function refToolTip(refID) {
        $msg = $("#history_" + refID + " div.text-sm.space-y-3.break-words").text().trim()
        $('#ref-tooltip').text($msg);
    }
    let quoted = [];

    function quote(llm_id, history_id, node) {
        let isQuoted = false;

        // Check if the [llm_id, history_id] pair exists in the quoted array
        for (let i = 0; i < quoted.length; i++) {
            if (quoted[i][0] === llm_id && quoted[i][1] === history_id) {
                isQuoted = true;
                quoted.splice(i, 1); // Remove the pair from the array
                break;
            }
        }

        if (isQuoted) {
            $(node).removeClass("fill-green-400 text-green-400");
            $(node).parent().parent().removeClass("bg-green-100")
        } else {
            $(node).addClass("fill-green-400 text-green-400");
            $(node).parent().parent().addClass("bg-green-100")
            quoted.push([llm_id, history_id]); // Add the pair to the array
        }
    }

    function translates(node, history_id, model) {
        $(node).parent().children("button.translates").addClass("hidden")
        $(node).removeClass("hidden")

        $(node).children("svg").addClass("hidden");
        $(node).children("svg").eq(1).removeClass("hidden");
        $(node).prop("disabled", true);
        const url = '{{ route('chat.translate', '') }}/' + history_id + (model ? "?model=" + model : "");

        fetch(url)
            .then(response => {
                const reader = response.body.getReader();
                var output = "";

                function streamRead() {
                    reader.read().then(({
                        done,
                        value
                    }) => {
                        if (done) {
                            // Stream has ended
                            $(node).parent().children("button.translates").removeClass("hidden");
                            return;
                        }

                        const content = new TextDecoder().decode(value);
                        if (output ===
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
                            output += content
                            $($(node).parent().parent().children()[0]).text(output +
                                (model ? "" : '\n\n[此訊息經由該模型嘗試翻譯，瀏覽器重新整理後可復原]'));
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

                        // Continue reading the stream
                        streamRead();
                    }).catch(error => {
                        console.error('Error reading stream:', error);
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
                    });
                }
                streamRead();
            })
            .catch(error => {
                console.error('Fetch error:', error);
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

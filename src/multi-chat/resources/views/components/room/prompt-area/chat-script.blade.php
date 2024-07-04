@props(['llms', 'tasks'])

<div id="connection_indicator"
    class="fixed center left-0 right-0 top-0 bottom-0 justify-center items-end flex inset-0 pointer-events-none pb-10">

    <div class="w-64 hidden sm:block"></div>
    <div class="flex flex-col pb-20">
        <div class="flex justify-center items-center"><i
                class="fas fa-wifi text-white animate-pulse bg-green-500 rounded-full p-4"></i></div>
        <span class="text-black dark:text-white animate-bounce pt-2">{{ __('room.connecting.hint') }}</span>
    </div>
</div>

@php
    use Illuminate\Support\Facades\Cache;
    use App\Models\SystemSetting;

    $upload_max_size_mb = SystemSetting::where('key', 'upload_max_size_mb')->first()->value;
    $upload_allowed_extensions = SystemSetting::where('key', 'upload_allowed_extensions')->first()->value;
@endphp
<script>
@if (session('errorString'))
    showErrorMsg("{{session('errorString')}}")
@endif

    function showErrorMsg(msg){
        $("#error_alert >span").text(msg)
        $("#error_alert").fadeIn();
        $("#upload_btn").toggleClass("bg-green-500 hover:bg-green-600 bg-red-600 hover:bg-red-700")
        $("#upload").val("");
        $("#attachment").hide();
        setTimeout(function() {
            $("#error_alert").fadeOut();
            $("#upload_btn").toggleClass("bg-green-500 hover:bg-green-600 bg-red-600 hover:bg-red-700")
        }, 3000);
    }
    function uploadcheck() {
        if (!$("#upload")[0].files || $("#upload")[0].files[0].length <= 0) return;

        if ($("#upload")[0].files[0].size > {{ $upload_max_size_mb * (2 ** 20)}}){
            showErrorMsg("{{ __('chat.hint.upload_file_too_large') }}");
            return;
        } 
        
        if (!$("#upload")[0].files[0].name.match(/\.({{ str_replace(',', '|', $upload_allowed_extensions) }})$/)){
            showErrorMsg("{{ __('chat.hint.upload_not_allowed_ext') }}");
            return;
        
        }
        $("#attachment").show();
        $("#attachment button").text($("#upload")[0].files[0].name)
    }
</script>

<script>
    if ($("#chat_input")) {
        $("#chat_input").prop("readonly", true)
        $("#chat_input").val("{{ __('chat.hint.processing') }}")
        $("#submit_msg").hide()
        if ($("#upload_btn")) $("#upload_btn").hide()
        if ($("#abort_btn")) $("#abort_btn").hide()
        $chattable = false
    }
    if ($("#prompt_area")) {
        $("#prompt_area").submit(function(event) {
            event.preventDefault();
            var allDisabled = true;
            $('input[name="chatsTo[]"]').each(function() {
                if (!$(this).prop('disabled')) {
                    allDisabled = false;
                    return false; // exit the loop if at least one input is not disabled
                }
            });

            if ($chattable && $("#chat_input").val().trim() == "" && quoted.length == 1) {
                $("#chat_input").val(`"""${histories[quoted[0][1]]}"""`)
                this.submit();
                $chattable = false
                $("#submit_msg").hide()
                if ($("#upload_btn")) $("#upload_btn").hide()
                if (!isMac) {
                    $("#chat_input").val("{{ __('chat.hint.processing') }}")
                }
                $("#chat_input").prop("readonly", true)
            } else if ($chattable && (($("#chat_input").val().trim() != "") || quoted.length != 0)) {
                tmp = ""
                for (var i in quoted) {
                    @if(App::environment('arena') || $llms->count() == 1)
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
                if ($("#upload_btn")) $("#upload_btn").hide()
                if (!isMac) {
                    $("#chat_input").val("訊息處理中...請稍後...")
                }
                $("#chat_input").prop("readonly", true)
            } else if ($("#upload")[0].files.length > 0) {
                this.submit();
                $chattable = false
                $("#submit_msg").hide()
                if ($("#upload_btn")) $("#upload_btn").hide()
                if (!isMac) {
                    $("#chat_input").val("{{ __('chat.hint.processing') }}")
                }
                $("#chat_input").prop("readonly", true)
            } else {
                if ($("#chat_input").val().trim() == "") {
                    $("#error_alert >span").text(
                        "{{ __('chat.hint.send.empty') }}")
                } else if (!$chattable) {
                    $("#error_alert >span").text(
                        "{{ __('chat.hint.send.still_processing') }}")
                } else if (allDisabled) {
                    $("#error_alert >span").text(
                        "{{ __('chat.hint.must_select_llm') }}")
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
    let finsihed = false;

    function connect() {
        const task = new EventSource("{!! $tasks ? route('room.sse', ['listening' => $tasks]) : route('room.sse') !!}", {
            withCredentials: false
        });
        task.addEventListener('open', () => {
            setTimeout(() => {
                if (finsihed || task.readyState === EventSource.OPEN) {
                    console.log('Connected')
                    $('#connection_indicator span').text('{{ __('room.connected.hint') }}')
                    $('#connection_indicator').fadeOut();
                }
            }, 1);
        });


        task.addEventListener('error', error => {
            console.error("Connection timeouted...");
            $('#connection_indicator').fadeIn();
            task.close();

            setTimeout(() => {
                if (!finsihed && task.readyState !== EventSource.OPEN) {
                    console.log(`Retrying connection`);
                    connect()
                } else {
                    $('#connection_indicator span').text('Connected!')
                    $('#connection_indicator').fadeOut();
                    console.log('Connected')
                }
            }, 1);
        });

        task.addEventListener('message', event => {
            if (event.data == "finished" && $("#submit_msg")) {
                finsihed = true;
                $chattable = true
                $("#submit_msg").show()
                if ($("#abort_btn")) $("#abort_btn").hide();
                if ($("#upload_btn")) $("#upload_btn").show()
                $("#chat_input").prop("readonly", false)
                $("#chat_input").val("")
                adjustTextareaRows($("#chat_input"))
                $(".show-on-finished").attr("style", "")
                hljs.configure({
                    languages: hljs.listLanguages()
                }); //enable auto detect
                $('#chatroom div.text-sm.space-y-3.break-words pre >div').remove()
                $('#chatroom div.text-sm.space-y-3.break-words pre code.language-undefined').each(function() {
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
                task.close();
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
                if ($("#upload_btn")) $("#upload_btn").hide()
            }
        });

    }

    // Initial connection
    connect();

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

    @if (session('selLLMs'))
        @foreach (array_diff($llms->pluck('id')->toarray(), session('selLLMs')) as $id)
            $(`#btn_{{ $id }}_toggle`).click()
        @endforeach
    @endif
    @if (session('mode_track') == 1)
        $('#send_to_mode').click()
    @endif
</script>

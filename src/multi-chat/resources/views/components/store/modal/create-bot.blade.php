@props(['result'])

<div id="create-bot-modal" data-modal-backdropClasses="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-40"
    data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
    class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-4xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <button type="button"
                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                data-modal-hide="create-bot-modal">
                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <!-- Modal header -->
            <div class="pl-6 pr-12 py-2 border-b rounded-t dark:border-gray-600 flex">
                <h3
                    class="text-xl font-semibold text-gray-900 lg:text-xl dark:text-white mr-auto text-center leading-loose">
                    {{ __('store.button.create') }}
                </h3>
            </div>
            <!-- Modal body -->
            <form method="post" action="{{ route('store.create') }}" class="p-6" id="create_room"
                enctype="multipart/form-data" onsubmit="return checkCreateBotForm()">
                @csrf
                <ul class="flex flex-wrap flex-col -mx-3 mb-2 items-center">
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full md:w-1/3 mb-2">
                            <div class="w-full px-3 mb-5">
                                <div class="flex flex-wrap -mx-3">
                                    <div class="w-full px-3 flex flex-col items-center">
                                        <label for="create-bot_image">
                                            <img id="llm_img" class="rounded-full m-auto bg-black" width="50px"
                                                height="50px" src="{{ asset('/' . config('app.LLM_DEFAULT_IMG')) }}">
                                        </label>
                                        <input id="create-bot_image" name="bot_image"
                                            onchange="change_bot_image('#llm_img', '#create-bot_image')" type="file"
                                            accept="image/*" style="display:none">
                                    </div>
                                </div>
                            </div>
                            <div class="w-full flex justify-center items-center">
                                <button id="visibility" data-dropdown-toggle="visibility_list"
                                    class="text-white rounded-l-lg bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-blue-300 font-medium text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700"
                                    type="button">{{ __('store.button.community') }}</button>
                                <div id="visibility_list"
                                    class="z-10 hidden bg-gray-200 divide-y divide-gray-100 rounded-lg shadow w-60 dark:bg-gray-800 dark:divide-gray-600">
                                    <ul class="p-3 space-y-1 text-sm text-gray-700 dark:text-gray-200"
                                        aria-labelledby="visibility">
                                        @php
                                            $radioItems = [];
                                            if (request()->user()->hasPerm('tab_Manage')) {
                                                $radioItems[] = [
                                                    'id' => 'visibility_system_option',
                                                    'title' => __('store.button.system'),
                                                    'description' => __('store.hint.button.system'),
                                                    'value' => '0',
                                                    'checked' => request()
                                                        ->user()
                                                        ->hasPerm('Store_create_community_bot')
                                                        ? false
                                                        : true,
                                                    'onchange' =>
                                                        "this.value == 0 ? $('#visibility').text('" .
                                                        __('store.button.system') .
                                                        "') : 1",
                                                    'name' => 'visibility',
                                                ];
                                            }
                                            if (request()->user()->hasPerm('Store_create_community_bot')) {
                                                $radioItems[] = [
                                                    'id' => 'visibility_community_option',
                                                    'title' => __('store.button.community'),
                                                    'description' => __('store.hint.button.community'),
                                                    'value' => '1',
                                                    'checked' => true,
                                                    'onchange' =>
                                                        "this.value == 1 ? $('#visibility').text('" .
                                                        __('store.button.community') .
                                                        "') : 1",
                                                    'name' => 'visibility',
                                                ];
                                            }
                                            if (request()->user()->hasPerm('Store_create_group_bot')) {
                                                $radioItems[] = [
                                                    'id' => 'visibility_group_option',
                                                    'title' => __('store.button.groups'),
                                                    'description' => __('store.hint.button.groups'),
                                                    'value' => '2',
                                                    'checked' => request()
                                                        ->user()
                                                        ->hasPerm('Store_create_community_bot')
                                                        ? false
                                                        : true,
                                                    'onchange' =>
                                                        "this.value == 2 ? $('#visibility').text('" .
                                                        __('store.button.groups') .
                                                        "') : 1",
                                                    'name' => 'visibility',
                                                ];
                                            }
                                            if (request()->user()->hasPerm('Store_create_private_bot')) {
                                                $radioItems[] = [
                                                    'id' => 'visibility_private_option',
                                                    'title' => __('store.button.private'),
                                                    'description' => __('store.hint.button.private'),
                                                    'value' => '3',
                                                    'checked' => request()
                                                        ->user()
                                                        ->hasPerm('Store_create_community_bot')
                                                        ? false
                                                        : true,
                                                    'onchange' =>
                                                        "this.value == 3 ? $('#visibility').text('" .
                                                        __('store.button.private') .
                                                        "') : 1",
                                                    'name' => 'visibility',
                                                ];
                                            }
                                        @endphp

                                        @foreach ($radioItems as $item)
                                            @include('components.radio_item', $item)
                                        @endforeach
                                    </ul>
                                </div>
                                <script>
                                    $('#visibility_list input[checked]:last()').click()
                                </script>
                                <button id="react_button" data-dropdown-toggle="react_button_list"
                                    class="text-white rounded-r-lg bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-blue-300 font-medium text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700"
                                    type="button">{{ __('store.bot.react_buttons') }}
                                </button>
                                <div id="react_button_list"
                                    class="z-10 hidden bg-gray-200 divide-y divide-gray-100 rounded-lg shadow w-72 dark:bg-gray-800 dark:divide-gray-600">
                                    <ul class="p-3 space-y-1 text-sm text-gray-700 dark:text-gray-200"
                                        aria-labelledby="react_button">

                                        @foreach (['Feedback', 'Translate', 'Quote', 'Other'] as $label)
                                            @php $id = strtolower($label); @endphp
                                            <li>
                                                <div class="flex rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    <label for="{{ $id }}"
                                                        class="inline-flex p-2 items-center w-full cursor-pointer">
                                                        <input checked id="{{ $id }}" name="react_btn[]"
                                                            value="{{ $id }}" type="checkbox"
                                                            class="sr-only peer">
                                                        <div
                                                            class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:translate-x-[-100%] peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-500 peer-checked:bg-blue-600">
                                                        </div>
                                                        <span
                                                            class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('store.bot.react.allow_' . strtolower($label)) }}</span>
                                                    </label>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="w-full md:w-2/3">
                            <div class="w-full">
                                <label class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2"
                                    for="llm_name">
                                    {{ __('store.bot.base_model') }}
                                </label>
                                <input type="text" list="llm-list" name="llm_name" autocomplete="off" id="llm_name"
                                    oninput="change_bot_image('#llm_img', '#create-bot_image', $(this).val())"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="{{ __('store.bot.base_model.label') }}">
                                @once
                                    <datalist id="llm-list">
                                        @foreach ($result as $LLM)
                                            <option
                                                src="{{ $LLM->image ? asset(Storage::url($LLM->image)) : '/' . config('app.LLM_DEFAULT_IMG') }}"
                                                value="{{ $LLM->name }}" data-access-code="{{ $LLM->access_code }}">
                                            </option>
                                        @endforeach
                                    </datalist>
                                @endonce
                            </div>
                            <div class="w-full mt-2">
                                <div class="w-full">
                                    <label for="bot_name"
                                        class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2">{{ __('store.bot.name') }}</label>
                                    <input type="text" id="bot_name" name="bot_name" autocomplete="off"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="{{ __('store.bot.name.label') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label for="bot_describe"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('store.bot.description') }}</label>
                            <input type="text" id="bot_describe" name="bot_describe" autocomplete="off"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="{{ __('store.bot.description.label') }}">
                        </div>
                    </div>
                    <div
                        class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap modelfile-toggle">
                        <div class="w-full">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                for="bot-system_prompt">{{ __('store.bot.system_prompt') }}</label>
                            <div class="flex items-center">
                                <textarea id="bot-system_prompt" type="text"
                                    oninput="ace.edit('bot-modelfile-editor').setValue(modelfile_to_string((modelfile_parse(ace.edit('bot-modelfile-editor').getValue()).some(obj => obj.name === 'system') ? modelfile_parse(ace.edit('bot-modelfile-editor').getValue()) : [...modelfile_parse(ace.edit('bot-modelfile-editor').getValue()), { name: 'system', args: 'uwu' }])
                                    .map(obj => obj.name === 'system' ? { ...obj, args: $(this).val() } : obj))); adjustTextareaRows(this);ace.edit('modelfile-editor').gotoLine(0);"
                                    rows="1" max-rows="4" placeholder="{{ __('store.bot.system_prompt.label') }}"
                                    class="bg-gray-50 border scrollbar border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap modelfile-toggle"
                        id="before_prompt">
                        <div class="w-full">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                for="bot-before_prompt">{{ __('store.bot.before_prompt') }}</label>
                            <div class="flex items-center">
                                <textarea id="bot-before_prompt" type="text"
                                    oninput="ace.edit('bot-modelfile-editor').setValue(modelfile_to_string((modelfile_parse(ace.edit('bot-modelfile-editor').getValue()).some(obj => obj.name === 'before-prompt') ? modelfile_parse(ace.edit('bot-modelfile-editor').getValue()) : [...modelfile_parse(ace.edit('bot-modelfile-editor').getValue()), { name: 'before-prompt', args: 'uwu' }])
                                    .map(obj => obj.name === 'before-prompt' ? { ...obj, args: $(this).val() } : obj))); adjustTextareaRows(this);ace.edit('modelfile-editor').gotoLine(0);"
                                    rows="1" max-rows="4" placeholder="{{ __('store.bot.before_prompt.label') }}"
                                    class="bg-gray-50 border scrollbar border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap modelfile-toggle"
                        id="after_prompt">
                        <div class="w-full">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                for="bot-after_prompt">{{ __('store.bot.after_prompt') }}</label>
                            <div class="flex items-center">
                                <textarea id="bot-after_prompt" type="text"
                                    oninput="ace.edit('bot-modelfile-editor').setValue(
                                        modelfile_to_string(
                                            (modelfile_parse(ace.edit('bot-modelfile-editor').getValue())
                                            .some(obj => obj.name === 'after-prompt') ? 
                                            modelfile_parse(ace.edit('bot-modelfile-editor').getValue()) : [...modelfile_parse(ace.edit('bot-modelfile-editor').getValue())
                                            , { name: 'after-prompt', args: 'uwu' }])
                                    .map(obj => obj.name === 'after-prompt' ? { ...obj, args: $(this).val() } : obj))); adjustTextareaRows(this);ace.edit('modelfile-editor').gotoLine(0);"
                                    rows="1" max-rows="4" placeholder="{{ __('store.bot.after_prompt.label') }}"
                                    class="bg-gray-50 border scrollbar border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white cursor-pointer bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-800 p-2 rounded-lg"
                                onclick="$('.modelfile-toggle').toggle()"
                                for="modelfile">{{ __('store.bot.modelfile') }}</label>
                            <div class="flex items-center modelfile-toggle" style="display:none">
                                <textarea name="modelfile" hidden></textarea>
                                <div id="bot-modelfile-editor" class="w-full h-64"></div>
                            </div>
                        </div>
                    </div>
                </ul>
                <div>
                    <div class="border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                        <button type="submit"
                            class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-500 hover:bg-gray-400 transition duration-300">
                            <p class="flex-1 text-center text-gray-700 dark:text-white">
                                {{ __('store.bot.button.create') }}
                            </p>
                        </button>
                    </div>
                </div>
                <span id="create_error" class="font-medium text-sm text-red-800 rounded-lg dark:text-red-400 hidden"
                    role="alert"></span>
            </form>
        </div>
    </div>
</div>

<script>
    function checkCreateBotForm() {
        if ($("#create_room input[name='llm_name']").val() && $("#create_room input[name='bot_name']").val()) {
            $('#create-bot-modal textarea[name=modelfile]').val(modelfile_to_string(modelfile_parse(ace.edit(
                    'bot-modelfile-editor')
                .getValue())))
            ace.edit('bot-modelfile-editor').setValue(modelfile_to_string(modelfile_parse(ace.edit(
                    'bot-modelfile-editor')
                .getValue())))
            ace.edit('bot-modelfile-editor').gotoLine(0);
            return true;
        }
        if (!$("#create_room input[name='llm_name']").val()) $("#create_error").text(
            "{{ __('store.hint.must_select_base_model') }}")
        else if (!$("#create_room input[name='bot_name']").val()) $("#create_error").text(
            "{{ __('You must name your bot') }}")
        $("#create_error").show().delay(3000).fadeOut();
        return false;
    }

    function importBotModelfile(modelfile) {
        modelfile = modelfile_parse(modelfile);
        console.debug(modelfile);
        let get_bot_config = function(modelfile, k) {
            modelfile = modelfile.map((inst) => `${inst['name'].toLowerCase()} ${inst['args']}`);
            let config = modelfile.filter((inst) => inst.startsWith(k));
            if (config.length == 0) return '';
            value = config[config.length - 1].replace(k, '').trim().replace(/^"(.+)"$/, '$1');
            return value;
        }
        const prefix = 'kuwabot'
        let base_access_code = get_bot_config(modelfile, `${prefix} base`);
        let base = $(`#llm-list option[data-access-code='${base_access_code}']`).val()
        $("#create_room input[name='llm_name']").val(base);
        $("#create_room input[name='llm_name']").trigger("input");
        $("#create_room input[name='bot_name']").val(get_bot_config(modelfile, `${prefix} name`));
        $("#create_room input[name='bot_describe']").val(get_bot_config(modelfile, `${prefix} description`));

        modelfile = modelfile.filter((inst) => inst['name'].toLowerCase() != 'kuwabot');
        ace.edit('bot-modelfile-editor').setValue(modelfile_to_string(modelfile))
        ace.edit('bot-modelfile-editor').gotoLine(0);
        $('#bot-modelfile-editor textarea').trigger("change");
    }

    function importBotAvatar(content_type, base64_data) {

        function dataUrl2File(content_type, base64_data) {

            const byte_string = atob(base64_data);

            // write the bytes of the string to an ArrayBuffer
            let buffer = new ArrayBuffer(byte_string.length);
            let buffer_uint8_view = new Uint8Array(buffer);
            for (var i = 0; i < byte_string.length; i++) {
                buffer_uint8_view[i] = byte_string.charCodeAt(i);
            }
            return new File([buffer], {
                type: content_type
            });
        }

        const avatar_file = dataUrl2File(content_type, base64_data);
        const input = $("#create-bot_image")[0];
        const transfer = new DataTransfer();
        transfer.items.add(avatar_file);
        input.files = transfer.files;
        const event = new Event("change", {
            bubbles: !0,
        });
        input.dispatchEvent(event);
    }

    function importBot(files) {
        const reader = new FileReader()
        const getMimeHeader = (x) => x.substr(0, x.indexOf('\r\n\r\n'));
        const getMimeBody = (x) => x.substr(x.indexOf('\r\n\r\n') + 1);
        const handleFileLoad = function(e) {
            let header = getMimeHeader(e.target.result);
            let body = getMimeBody(e.target.result);
            header = new Headers(header.split('\r\n').map((x) => x.split(': ')));
            console.debug("Header:", header, "Body: ", body);
            const parser = new MultipartRelatedParser(header.get('Content-Type'));
            const parts = parser.read(new TextEncoder().encode(body));
            console.debug(parts);
            if (parts.length == 0) {
                console.warn("Wrong botfile format.");
                return;
            }

            let modelfile = new TextDecoder().decode(parts[0].data);
            let avatar_part = parts.filter((x) => x.headers["Content-Location"] === "/bot-avatar");

            importBotModelfile(modelfile);

            if (avatar_part.length != 0) {
                console.debug(avatar_part);
                let avatar_type = avatar_part[0].headers["Content-Type"];
                let avatar_base64_data = new TextDecoder().decode(avatar_part[0].data)
                importBotAvatar(avatar_type, avatar_base64_data)
            }


            $(".create-bot-btn").click();
        }

        reader.onload = handleFileLoad;
        reader.readAsText(files[0]);
    }

    var editor = ace.edit($('#bot-modelfile-editor')[0], {
        mode: "ace/mode/dockerfile",
        selectionStyle: "text"
    })
    editor.setHighlightActiveLine(true);
    // Set the onblur event
    $('#bot-modelfile-editor textarea').on('blur', function() {
        let data = modelfile_parse(ace.edit('bot-modelfile-editor').getValue());
        for (let obj of data) {
            if (obj.name === 'system') {
                $("#bot-system_prompt").val(obj.args)
            }
        }
        ace.edit('bot-modelfile-editor').setValue(modelfile_to_string(data))
        ace.edit('bot-modelfile-editor').gotoLine(0);
    });
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        editor.setTheme("ace/theme/monokai");
    }

    $('#bot-modelfile-editor textarea').on('change', function() {
        let data = modelfile_parse(ace.edit('bot-modelfile-editor').getValue());
        for (let obj of data) {
            if (obj.name === 'system') {
                $("#bot-system_prompt").val(obj.args)
            } else if (obj.name === 'before-prompt') {
                $("#bot-before_prompt").val(obj.args)
            } else if (obj.name === 'after-prompt') {
                $("#bot-after_prompt").val(obj.args)
            }
        }
    });
    @once
    function change_bot_image(bot_image_elem, user_upload_elem, new_base_bot_name) {
            /**
             * Dynamically updates the bot's displayed image based on user interaction.
             *
             * Image selection priority:
             * 1. User-uploaded image (highest)
             * 2. Base bot image (if the bot image hasn't been changed)
             * 3. Original image (lowest)
             */
            const [user_uploaded_image] = $(user_upload_elem)[0].files;
            const follow_base_bot = $(bot_image_elem).data("follow-base-bot") ?? true;
            let bot_image_uri = $(bot_image_elem).attr("src");
            if (user_uploaded_image) {
                bot_image_uri = URL.createObjectURL(user_uploaded_image);
            } else if (follow_base_bot && new_base_bot_name) {
                const fallback_image_uri = "{{ asset('/' . config('app.LLM_DEFAULT_IMG')) }}";
                bot_image_uri = $(`#llm-list option[value="${new_base_bot_name}"]`).attr("src") ?? fallback_image_uri;
            }
            $(bot_image_elem).attr("src", bot_image_uri);
        }
    @endonce
</script>

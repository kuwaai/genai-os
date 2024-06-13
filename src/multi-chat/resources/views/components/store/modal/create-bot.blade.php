@props(['result'])

<div id="create-bot-modal" data-modal-backdropClasses="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-40"
    data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
    class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-2xl max-h-full">
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
            <div class="px-6 py-4 border-b rounded-t dark:border-gray-600">
                <h3 class="text-base font-semibold text-gray-900 lg:text-xl dark:text-white">
                    {{ __('store.button.create') }}
                </h3>
            </div>
            <!-- Modal body -->
            <form method="post" action="{{ route('store.create') }}" class="p-6" id="create_room"
                onsubmit="return checkForm2()">
                @csrf
                <ul class="flex flex-wrap -mx-3 mb-2 items-center">
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <div class="flex flex-wrap -mx-3">
                            <div class="w-full px-3 flex flex-col items-center">
                                <label for="llm_name">
                                    <img id="llm_img" class="rounded-full m-auto bg-black" width="50px"
                                        height="50px" src="/{{ config('app.LLM_DEFAULT_IMG') }}">
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 px-3 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2"
                                for="llm_name">
                                {{ __('store.bot.base_model') }}
                            </label>
                            <input type="text" list="llm-list" name="llm_name" autocomplete="off" id="llm_name"
                                oninput='$("#llm_img").attr("src",$(`#llm-list option[value="${$(this).val()}"]`).attr("src") ?? "/{{ config('app.LLM_DEFAULT_IMG') }}")'
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="{{ __('store.bot.base_model.label') }}">
                            <datalist id="llm-list">
                                @foreach ($result as $LLM)
                                    <option
                                        src="{{ $LLM->image ? asset(Storage::url($LLM->image)) : '/' . config('app.LLM_DEFAULT_IMG') }}"
                                        value="{{ $LLM->name }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                    <div class="w-full grid grid-cols-2 gaps-2 md:grid-cols-3 md:gaps-2">
                        <div class="w-full md:col-span-2">
                            <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                                <div class="w-full">
                                    <label for="bot-name"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('store.bot.name') }}</label>
                                    <input type="text" id="bot-name" name="bot-name" autocomplete="off"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="{{ __('store.bot.name.label') }}">
                                </div>
                            </div>
                            <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                                <div class="w-full">
                                    <label for="bot-describe"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('store.bot.description') }}</label>
                                    <input type="text" id="bot-describe" name="bot-describe" autocomplete="off"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="{{ __('store.bot.description.label') }}">
                                </div>
                            </div>
                        </div>
                        <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                            <div class="w-full">
                                <p class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2">
                                    {{ __('store.bot.react_buttons') }}
                                </p>

                                @foreach (['Feedback', 'Translate', 'Quote', 'Other'] as $label)
                                    @php $id = strtolower($label); @endphp
                                    <div class="flex items-center">
                                        <input checked id="{{ $id }}" name="react_btn[]"
                                            value="{{ $id }}" type="checkbox"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="{{ $id }}"
                                            class="ml-2 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                            {{ __('store.bot.react.allow_' . strtolower($label)) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
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
    function checkForm2() {
        if ($("#create_room input[name='llm_name']").val() && $("#create_room input[name='bot-name']").val()) {
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
        else if (!$("#create_room input[name='bot-name']").val()) $("#create_error").text(
            "{{ __('You must name your bot') }}")
        $("#create_error").show().delay(3000).fadeOut();
        return false;
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
</script>

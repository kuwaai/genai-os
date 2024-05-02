@props(['result'])
<div id="detail-modal" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
    class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-2xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                <div style="display:none;"
                    class="mr-4 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:h-10 sm:w-10 bg-green-100">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                        stroke-linecap="round" stroke-linejoin="round" class="icon-lg text-green-700" aria-hidden="true"
                        height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
                        </path>
                    </svg>
                </div>
                <div style="display:none;"
                    class="mr-4 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:h-10 sm:w-10 bg-red-100">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                        stroke-linecap="round" stroke-linejoin="round" class="icon-lg text-red-600" aria-hidden="true"
                        height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl my-auto font-semibold text-gray-900 dark:text-white">
                    Null
                </h3>
                <button type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                    data-modal-hide="detail-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form method="post" action="{{ route('store.update') }}" class="p-6" id="update_bot"
                onsubmit="return checkForm()">
                @csrf
                @method('patch')
                <input name="id" hidden>
                <ul class="flex flex-wrap -mx-3 mb-2 items-center">
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <div class="flex flex-wrap -mx-3">
                            <div class="w-full px-3 flex flex-col items-center">
                                <img class="rounded-full m-auto bg-black" width="50px" height="50px">
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 px-3 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2"
                                for="llm_name">
                                選取模型
                            </label>
                            <input type="text" list="llm-list" name="llm_name" autocomplete="off" id="llm_name"
                                oninput='$("#llm_img").attr("src",$(`#llm-list option[value="${$(this).val()}"]`).attr("src") ?? "/{{ config('app.LLM_DEFAULT_IMG') }}")'
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="基底模型">
                            <datalist id="llm-list">
                                @foreach ($result as $LLM)
                                    <option
                                        src="{{ $LLM->image ? asset(Storage::url($LLM->image)) : '/' . config('app.LLM_DEFAULT_IMG') }}"
                                        value="{{ $LLM->name }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label for="bot-name"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('store.bot.name') }}</label>
                            <input type="text" name="bot-name" autocomplete="off"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="{{ __('store.bot.name.label') }}">
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label for="bot-describe"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('store.bot.description') }}</label>
                            <input type="text" name="bot-describe" autocomplete="off"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="{{ __('store.bot.description.label') }}">
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <p class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2">
                                {{ __('React Buttons') }}
                            </p>

                            @foreach (['Feedback', 'Translate', 'Quote', 'Other'] as $label)
                                @php $id = strtolower($label); @endphp
                                <div class="flex items-center">
                                    <input id="{{ $id }}" name="react_btn[]" value="{{ $id }}"
                                        type="checkbox"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="{{ $id }}"
                                        class="ml-2 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                        {{ __('Allow ' . $label) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                for="modelfile">Modelfile</label>
                            <div class="flex items-center">
                                <textarea id="modelfile" name="modelfile" type="text" oninput="adjustTextareaRows(this)"
                                    onblur="modelfile_update($(this));" rows="1" max-rows="10" placeholder="modelfile"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <button type="button" id="save_bot" data-modal-target="update_modal"
                            data-modal-toggle="update_modal"
                            class="bg-green-500 hover:bg-green-600 text-white focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            {{ __('manage.button.save') }}
                        </button>
                        <button type="button" id="delete_bot" data-modal-target="delete_modal"
                            data-modal-toggle="delete_modal"
                            class="bg-red-500 hover:bg-red-600 text-white focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            {{ __('manage.button.delete') }}
                        </button>
                    </div>
                </ul>
            </form>
        </div>
    </div>
</div>

<form id="del_bot_by_ID" method="post" action="{{ route('store.delete') }}" style="display:none">
    @csrf
    @method('delete')
    <input name="id">
</form>
<div id="update_modal" data-modal-backdrop="static" tabindex="-2"
    class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <button type="button"
                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                data-modal-hide="update_modal">
                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="p-6 text-center">
                <svg aria-hidden="true" class="mx-auto mb-4 text-gray-400 w-14 h-14 dark:text-gray-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                    {{ __('manage.modal.update_model.header') }}</h3>
                <button data-modal-hide="update_modal" type="submit" id="update_bot_btn"
                    class="text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                    {{ __('manage.button.yes') }}
                </button>
                <button data-modal-hide="update_modal" type="button"
                    class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">{{ __('manage.button.no') }}</button>
            </div>
        </div>
    </div>
</div>
<div id="delete_modal" data-modal-backdrop="static" tabindex="-2"
    class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <button type="button"
                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                data-modal-hide="delete_modal">
                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="p-6 text-center">
                <svg aria-hidden="true" class="mx-auto mb-4 text-gray-400 w-14 h-14 dark:text-gray-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                    {{ __('manage.modal.delete_model.header') }}</h3>
                <button id="delete_bot_btn" data-modal-hide="delete_modal" type="button"
                    class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                    {{ __('manage.button.yes') }}
                </button>
                <button data-modal-hide="delete_modal" type="button"
                    class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">{{ __('manage.button.no') }}</button>
            </div>
        </div>
    </div>
</div>
<script>
    function detail_update(data, readonly) {
        console.log(readonly)
        $("#save_bot").hide()
        $("#delete_bot").hide()
        if (!readonly) {
            $("#save_bot").show()
            $("#delete_bot").show()
        }
        $("#detail-modal input").prop("disabled", readonly)
        $("#detail-modal input").prop("readonly", readonly)
        $("#detail-modal textarea").prop("readonly", readonly)
        $("#detail-modal textarea").prop("disabled", readonly)
        $("#detail-modal h3").text(data.name);
        $("#detail-modal input[name=llm_name]").val(data.llm_name)
        $("#detail-modal input[name=bot-name]").val(data.name)
        $("#detail-modal img").attr("src", data.image)
        $("#detail-modal input[name=bot-describe]").val(data.description)
        $("#detail-modal input[name='react_btn[]']").prop("checked", false);
        if (data.config) {
            config = JSON.parse(data.config)
            config["react_btn"].forEach((a) => {
                $(`#detail-modal input[value='${a}']`).prop("checked", true);
            });
            $("#detail-modal textarea[name=modelfile]").val(modelfile_to_string(config['modelfile'] ?? []))
        }
        $("#update_bot_btn").off('click').on('click', function() {
            $("#update_bot input[name=id]").val(data.id);
            $("#update_bot").submit();
        });
        $("#delete_bot_btn").off('click').on('click', function() {
            $("#del_bot_by_ID input:eq(2)").val(data.id);
            $("#del_bot_by_ID").submit();
        });
        adjustTextareaRows($("#detail-modal textarea[name=modelfile]")[0])
    }

    function checkForm() {
        if ($("#update_bot input[name='llm_name']").val() && $("#update_bot input[name='bot-name']").val()) {
            return true;
        }
        if (!$("#update_bot input[name='llm_name']").val()) $("#create_error").text(
            '{{ __('store.hint.must_select_base_model') }}')
        else if (!$("#update_bot input[name='bot-name']").val()) $("#create_error").text(
            "{{ __('You must name your bot') }}")
        $("#create_error").show().delay(3000).fadeOut();
        return false;
    }
</script>

<div id="safety-guard-interface" class="flex flex-1 overflow-hidden">
    <div class="overflow-hidden flex flex-1" style="display:none;">
        <div class="flex flex-1 h-full mx-auto overflow-hidden">
            <div
                class="flex flex-col bg-white dark:bg-gray-700 p-2 text-white w-64 flex-shrink-0 relative overflow-hidden">
                <div class="mb-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                    <button onclick="CreateRule()" id="new_rule_btn"
                        class="flex menu-btn flex items-center justify-center w-full h-12 hover:bg-green-500 dark:hover:bg-green-700 transition duration-300 bg-green-500 dark:bg-green-700">
                        <p class="flex-1 text-center text-white">{{ __('dashboard.button.create_rule') }}</p>
                    </button>
                </div>
                <form id="delete_rule_by_id" method="post" action="" style="display:none">
                    @csrf
                    @method('delete')
                    <input name="tab" value="safetyguard" hidden>
                </form>
                <div class="flex-1 overflow-y-auto scrollbar text-black dark:text-white" id="rule_list">
                    <div class="my-2 border border-black dark:border-white border-1 rounded-lg overflow-hidden hidden">
                        <button
                            class="flex menu-btn items-center justify-center w-full dark:hover:bg-gray-600 hover:bg-gray-200 transition duration-300">
                            <div class="flex flex-1 overflow-hidden">
                                <div class="flex flex-1 flex-col h-[48px] overflow-hidden">
                                    <span
                                        class="px-2 my-auto truncate-text overflow-ellipsis overflow-hidden whitespace-nowrap"></span>
                                    <span
                                        class="px-2 my-auto text-sm text-gray-300 truncate-text overflow-ellipsis overflow-hidden whitespace-nowrap"></span>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
            <div id="edit_rule"
                class="flex-1 h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 shadow-xl overflow-hidden justify-center items-center text-gray-700 dark:text-white">
                <h3 class="my-4 text-xl font-medium text-gray-900 dark:text-white">
                    {{ __('dashboard.header.create_rule') }}</h3>
                @if ($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p class="font-bold">Oops! Something went wrong:</p>
                        <ul class="list-disc ml-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form id="safetyguard_create_rule" method="post" enctype="multipart/form-data" autocomplete="off"
                    onsubmit="return create_validate()" action="{{ route('dashboard.safetyguard.create') }}"
                    class="w-full  p-4 overflow-y-auto scrollbar overflow-x-hidden space-y-2">
                    @csrf
                    @method('patch')
                    <input name="tab" value="safetyguard" hidden>
                    <input name='last_change' hidden>
                    <div id="safetygard_targetInputsContainer"></div>
                    <div class="flex overflow-hidden -mx-3">
                        <div class="flex flex-1 flex-col overflow-hidden px-3">
                            <label class="block uppercase tracking-wide dark:text-white text-xs font-bold"
                                for="ruleName">
                                規則名稱<span class="text-red-400">*</span>
                            </label>
                            <input name="ruleName"
                                class="appearance-none block w-full text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="ruleName" placeholder="規則名稱" value="" required>
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide dark:text-white text-xs font-bold"
                                for="description">
                                規則敘述
                            </label>
                            <input name="description"
                                class="appearance-none block w-full text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="description" placeholder="規則敘述" value="">
                        </div>
                    </div>
                    <hr>
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full px-3">
                            <label for="safetyguard_tagInput"
                                class="block uppercase tracking-wide dark:text-white text-xs font-bold">指定套用規則模型</label>
                            <div class="relative mt-1">
                                <div id="safetyguard_tagContainer" class="mt-2 flex flex-wrap">
                                    <input id="safetyguard_tagInput" type="text"
                                        class="bg-transparent border-2 rounded-lg placeholder:text-black dark:placeholder:text-white"
                                        placeholder="請選擇模型">
                                </div>
                                <div id="safetyguard_tagSuggestions" style="display:none;"
                                    class="absolute z-10 mt-2 bg-white border border-gray-300 rounded-md shadow-lg p-2">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide dark:text-white text-xs font-bold"
                                for="action">
                                規則行為<span class="text-red-400">*</span>
                            </label>
                            <select id="action" name="action"
                                class="appearance-none block w-full text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                <option value="none" selected>無行為</option>
                                <option value="overwrite">改寫(由系統決定內容)</option>
                                <option value="block">封鎖+(可選警告)</option>
                                <option value="warn">純警告</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full px-3">
                            <label class="block uppercase tracking-wide dark:text-white text-xs font-bold"
                                for="message">
                                警告提示訊息(可選)
                            </label>
                            <input name="message"
                                class="appearance-none block w-full text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                id="message" placeholder="警告提示訊息">
                        </div>
                    </div>
                    @php
                        $filters = ['keyword' => ['name' => 'Keyword 規則', 'filters' => ['pre-filter' => ['name' => '輸入過濾'], 'post-filter' => ['name' => '輸出過濾']]], 'embedding' => ['name' => 'Embedding 規則', 'filters' => ['pre-filter' => ['name' => '輸入過濾'], 'post-filter' => ['name' => '輸出過濾']]]];
                    @endphp
                    <div id="safetyguard-collapse" data-accordion="collapse"
                        class="rounded-lg overflow-hidden bg-gray-300 dark:bg-gray-500">
                        @foreach ($filters as $index => $topfilter)
                            @php
                                $topfilterId = "safetyguard-collapse-heading-$index";
                                $accordionBodyId = "safetyguard-collapse-body-$index";
                            @endphp
                            <div id="{{ $topfilterId }}">
                                <button type="button"
                                    class="flex items-center justify-between w-full p-3 text-black dark:text-white gap-3 border border-1 border-gray-300 dark:border-gray-800"
                                    data-accordion-target="#{{ $accordionBodyId }}" aria-expanded="true"
                                    aria-controls="{{ $accordionBodyId }}">
                                    <span class="text-black dark:text-white">{{ $topfilter['name'] }}</span>
                                    <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M9 5 5 1 1 5" />
                                    </svg>
                                </button>
                            </div>
                            <div id="{{ $accordionBodyId }}" class="hidden" aria-labelledby="{{ $topfilterId }}">
                                <div
                                    class="p-2 border border-b-0 border-gray-200 dark:border-gray-700 dark:bg-gray-900 flex flex-col md:flex-row">
                                    @foreach ($topfilter['filters'] as $subindex => $subfilter)
                                        <div class="flex-1 p-1" id="{{ $index . '-' . $subindex }}">
                                            <p class="text-center">{{ $subfilter['name'] }}</p>
                                            <input name="{{ $index . '-' . $subindex }}[]" rows='1'
                                                max-rows="2"
                                                class="dynamic-input mb-2 px-2 py-1 resize-none scrollbar appearance-none block w-full text-gray-700 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-gray-500"
                                                placeholder="{{ $subfilter['name'] }}">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>


                    <script>
                        $(document).on('input', '.dynamic-input', function() {
                            const dynamicInputs = $(this).parent();

                            dynamicInputs.find('.dynamic-input:not(:last)').each(function() {
                                const inputValue = $(this).val().trim();
                                if (inputValue === '') {
                                    $(this).remove();
                                }
                            });

                            if (dynamicInputs.find('.dynamic-input:last').val().trim() !== '') {
                                const newInput = $(this).clone()
                                newInput.val('');
                                dynamicInputs.append(newInput);
                            }
                        });
                    </script>
                    <div class="text-center">
                        <button type="button" data-modal-target="popup-modal2" data-modal-toggle="popup-modal2"
                            class="bg-green-500 hover:bg-green-600 text-white focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            儲存
                        </button>
                        <div id="popup-modal2" data-modal-backdrop="static" tabindex="-2"
                            class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full justify-center items-center">
                            <div class="relative w-full max-w-md max-h-full">
                                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                                    <button type="button"
                                        class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                                        data-modal-hide="popup-modal2">
                                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                    <div class="p-6 text-center">
                                        <svg aria-hidden="true"
                                            class="mx-auto mb-4 text-gray-400 w-14 h-14 dark:text-gray-200"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                                            您確定要創建這個設定檔？</h3>
                                        <button data-modal-hide="popup-modal2" type="submit"
                                            class="text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                            是，我確定
                                        </button>
                                        <button data-modal-hide="popup-modal2" type="button"
                                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">否，取消</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button data-modal-target="popup-modal" data-modal-toggle="popup-modal" type="button"
                            id="delete_button"
                            class="bg-red-500 hover:bg-red-600 text-white focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2"
                            style="display: none;">
                            刪除
                        </button>
                        <div id="popup-modal" data-modal-backdrop="static" tabindex="-2"
                            class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full justify-center items-center">
                            <div class="relative w-full max-w-md max-h-full">
                                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                                    <button type="button"
                                        class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                                        data-modal-hide="popup-modal">
                                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                    <div class="p-6 text-center">
                                        <svg aria-hidden="true"
                                            class="mx-auto mb-4 text-gray-400 w-14 h-14 dark:text-gray-200"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                                            您確定要刪除這個語言模型設定檔嗎</h3>
                                        <button id="delete_rule_btn" data-modal-hide="popup-modal" type="button"
                                            class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                            是，我確定
                                        </button>
                                        <button data-modal-hide="popup-modal" type="button"
                                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">否，取消</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="w-full overflow-hidden flex justify-center items-center">
        <p class="text-red-500 text-center text-lg">Safety guard offline</p>
    </div>
</div>

<script>
    $.ajax({
        url: "{{ route('dashboard.safetyguard.fetch') }}",
        type: 'GET',
        success: function(data, textStatus, xhr) {
            // Check if the HTTP status code is 200
            if (xhr.status === 200) {
                let rulesSection = $('#safety-guard-interface >div:eq(0)');
                let offlineMessage = $('#safety-guard-interface >div:eq(1)');

                // Show the rules section div and hide the offline message div
                rulesSection.show();
                offlineMessage.hide();
            } else {
                // If the HTTP status code is not 200, show the offline message div and hide rulesSection
                $('#safety-guard-interface >div:eq(0)').hide();
                $('#safety-guard-interface >div:eq(1)').show();
            }
            $("#rule_list").find(">:not(div.hidden)").remove();
            $("#rule_list").append(`<p>{{ __('dashboard.header.enabled_rules') }}</p><hr>`)
            tmp = [];
            for (let key in data) {
                cloned = $("#rule_list").find("div.hidden:first()").clone();
                cloned.removeClass("hidden");
                cloned.find("span:first()").text("ID:" + data[key]["id"] + " " + data[key]["name"])
                if (data[key]["description"]) cloned.find("span:nth-child(2)").text(data[key][
                    "description"
                ])
                else cloned.find("span:nth-child(2)").remove();
                cloned.attr("id", "rule_" + data[key]['id'])
                cloned.on('click', () => {
                    edit_rule(data[key]['id'])
                })
                if (data[key]['id'] >= 1 && data[key]['id'] <= 10) cloned.children(0).addClass(
                    "bg-blue-800 hover:bg-blue-700").removeClass(
                    "dark:hover:bg-gray-600 hover:bg-gray-200")
                if (data[key]['action'] !== 'none') {
                    $("#rule_list").append(cloned)
                } else {
                    tmp.push(cloned);
                }
            }
            $("#rule_list").append(`<p>{{ __('dashboard.header.disabled_rules') }}</p><hr>`)
            for (let key in tmp) {
                $("#rule_list").append(tmp[key])
            }
            $rules = data.reduce((dict, item) => {
                dict[item.id] = item;
                return dict;
            }, {});;
            @if (session('rule_id'))
                $('#rule_{{ session('rule_id') }}').click();
            @endif
        },
        error: function() {
            // If the GET request fails, show the offline message div and hide rulesSection
            $('#safety-guard-interface >div:eq(0)').hide();
            $('#safety-guard-interface >div:eq(1)').show();
        }
    });
    const safetyguard_tagInput = document.getElementById('safetyguard_tagInput');
    const safetyguard_tagSuggestions = document.getElementById('safetyguard_tagSuggestions');
    const safetyguard_tagContainer = document.getElementById('safetyguard_tagContainer');

    const safetyguard_enabled_tags = JSON.parse({!! json_encode(
        App\Models\LLMs::orderby('order')->orderby('order')->where('enabled', '=', true)->get()->pluck('access_code')->toJson(),
    ) !!});
    const safetyguard_disabled_tags = JSON.parse({!! json_encode(
        App\Models\LLMs::orderby('order')->orderby('order')->where('enabled', '=', false)->get()->pluck('access_code')->toJson(),
    ) !!});
    const safetyguard_selectedTags = [];

    safetyguard_tagInput.addEventListener('input', (event) => {
        const inputValue = event.target.value.toLowerCase();
        const filteredTags = safetyguard_filterTags(inputValue);

        if (filteredTags.length == 0) {
            safetyguard_clearSuggestions();
        } else {
            safetyguard_render_tagSuggestions(filteredTags);
        }
    });
    safetyguard_tagInput.addEventListener('focus', () => {
        const inputValue = safetyguard_tagInput.value.toLowerCase();
        const filteredTags = safetyguard_filterTags(inputValue);
        safetyguard_render_tagSuggestions(filteredTags);
    });
    safetyguard_tagInput.addEventListener('blur', () => {
        setTimeout(safetyguard_clearSuggestions, 200);
    });

    function safetyguard_filterTags(inputValue) {
        const allTags = [...safetyguard_enabled_tags, ...safetyguard_disabled_tags];
        return allTags.filter(tag => tag.toLowerCase().includes(inputValue) && !safetyguard_selectedTags.includes(tag));
    }

    function safetyguard_render_tagSuggestions(filteredTags) {
        safetyguard_tagSuggestions.innerHTML = '';
        if (filteredTags.length != 0) {
            const suggestionContainer = document.createElement('div');
            suggestionContainer.className = 'flex flex-wrap';

            filteredTags.forEach(tag => {
                const suggestionItem = safetyguard_createSuggestionItem(tag);
                suggestionContainer.appendChild(suggestionItem);
            });

            safetyguard_tagSuggestions.appendChild(suggestionContainer);
            $(safetyguard_tagSuggestions).show();
        }
    }

    function safetyguard_createSuggestionItem(tag) {
        const suggestionItem = document.createElement('div');
        suggestionItem.className = 'p-2 cursor-pointer text-black bg-gray-300 hover:bg-gray-100 rounded-md mb-2 mr-2';
        suggestionItem.textContent = tag;
        suggestionItem.addEventListener('click', () => safetyguard_addTag(tag));
        return suggestionItem;
    }

    function safetyguard_clearSuggestions() {
        safetyguard_tagSuggestions.innerHTML = '';
        $(safetyguard_tagSuggestions).hide();
    }

    function safetyguard_addTag(tag, notFound = false) {
        safetyguard_selectedTags.push(tag);

        const tagElement = safetyguard_createTagElement(tag, notFound);
        safetyguard_tagContainer.prepend(tagElement);
        safetyguard_updateTargetInput();
        safetyguard_tagInput.value = '';
        safetyguard_clearSuggestions();

        tagElement.addEventListener('click', () => safetyguard_removeTag(tag, tagElement));
    }

    function safetyguard_createTagElement(tag, notFound = false) {
        const tagElement = document.createElement('div');
        tagElement.className = 'text-white px-2 py-1 rounded-md cursor-pointer mr-2 mb-2 ' + (notFound ?
            "bg-orange-400 hover:bg-red-700" : "bg-blue-500 hover:bg-red-600");
        tagElement.textContent = tag;
        return tagElement;
    }

    function safetyguard_removeTag(tag, tagElement) {
        const index = safetyguard_selectedTags.indexOf(tag);
        if (index !== -1) {
            safetyguard_selectedTags.splice(index, 1);
        }

        safetyguard_tagContainer.removeChild(tagElement);
        safetyguard_updateTargetInput();
    }

    function safetyguard_updateTargetInput() {
        const safetygard_targetInputsContainer = document.getElementById('safetygard_targetInputsContainer');
        safetygard_targetInputsContainer.innerHTML = ''; // Clear existing inputs

        safetyguard_selectedTags.forEach((tag, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `target[]`; // Use index to create multiple inputs
            input.value = tag;
            safetygard_targetInputsContainer.appendChild(input);
        });
    }
    last_rule_id = null;

    function edit_rule(id) {
        $("#edit_rule input:eq(1)").prop("disabled", false)
        $("#edit_rule form").attr("action", `{{ route('dashboard.safetyguard.update', '') }}/` + id);
        $("#new_rule_btn").removeClass("bg-green-500 dark:bg-green-700").addClass("bg-green-400 dark:bg-green-600")
        rule = $rules[id];
        $('#edit_rule >h3').text(`ID: ${id} {{ __('dashboard.header.update_rule') }} ${rule["name"]}`)
        $('#edit_rule input[name=ruleName]').val(rule["name"])
        $('#edit_rule input[name=last_change]').val(rule["retrieval-timestamp"])
        $('#edit_rule input[name=description]').val(rule["description"])
        $('#edit_rule select[name=action]').val(rule["action"])
        $('#edit_rule input[name=message]').val(rule["message"])
        if (id >= 1 && id <= 10) {
            $("#safetyguard-collapse").hide()
            $("#delete_button").hide()
        } else {
            $("#safetyguard-collapse").show()
            $("#delete_button").show()
        }
        $("#safetyguard_tagContainer >div").click()
        let missingTargets = [];
        rule["target"].forEach((target) => {
            safetyguard_render_tagSuggestions(safetyguard_filterTags(""))
            let $matchingElement = $(`#safetyguard_tagSuggestions div:contains('${target}')`).filter((index,
                element) => {
                return $(element).text().trim() === target;
            });
            if ($matchingElement.length === 0) {
                missingTargets.push(target);
            } else {
                // Click on the target if it is found
                $matchingElement.click();
            }
        });
        for (let key in missingTargets) {
            safetyguard_addTag(missingTargets[key], true);
        }
        safetyguard_clearSuggestions();
        ["pre-filter", "post-filter"].forEach((element1) => {
            ["embedding", "keyword"].forEach((element2) => {
                dynamicInputs = $("#" + element2 + "-" + element1)
                dynamicInputs.find('.dynamic-input:not(:last)').remove();
                last = dynamicInputs.find('.dynamic-input:last')
                if (last.val().trim() !== '') {
                    last.val("")
                }
                if (rule[element1] !== undefined && Array.isArray(rule[element1][element2])) {
                    rule[element1][element2].forEach((element3) => {
                        changed = $("#" + element2 + "-" + element1 + " >input:last()")
                        changed.val(element3)
                        dynamicInputs.find('.dynamic-input:not(:last)').each(function() {
                            if ($(this).val().trim() === '') {
                                $(this).remove();
                            }
                        });

                        if (dynamicInputs.find('.dynamic-input:last').val().trim() !== '') {
                            const newInput = $(changed).clone()
                            newInput.val('');
                            dynamicInputs.append(newInput);
                        }
                    })
                }
            })
        })

        $("#delete_rule_btn").attr("onclick", `DeleteRule(${id})`)

        if (last_rule_id != id) {
            $("#edit_rule").show()
            $("#rule_list").find(">:not(div.hidden)").removeClass("bg-gray-600")
            $("#rule_" + id).addClass("bg-gray-600");
        } else {
            $("#edit_rule").toggle();
            $("#rule_" + id).toggleClass("bg-gray-600");
        }
        last_rule_id = id;
    }

    function DeleteRule(id) {
        $("#delete_rule_by_id").attr("action", "{{ route('dashboard.safetyguard.delete', '') }}/" + id)
        $("#delete_rule_by_id").submit();
    }

    function CreateRule() {
        $("#edit_rule input:eq(1)").prop("disabled", true)
        $("#edit_rule form").attr("action", "{{ route('dashboard.safetyguard.create') }}");
        $("#new_rule_btn").addClass("bg-green-500 dark:bg-green-700").removeClass("bg-green-400 dark:bg-green-600")
        $("#rule_list").find(">:not(div.hidden)").removeClass("bg-gray-600")
        last_rule_id = null;
        $("#safetyguard_tagContainer >div").click()
        $("#delete_button").hide();
        $("#safetyguard-collapse").show()
        $('#edit_rule >h3').text("{{ __('dashboard.header.create_rule') }}");
        $('#edit_rule input[name=ruleName]').val("");
        $('#edit_rule input[name=description]').val("");
        $('#edit_rule select[name=action]').val("none");
        $('#edit_rule input[name=message]').val("");
        ["pre-filter", "post-filter"].forEach((element1) => {
            ["embedding", "keyword"].forEach((element2) => {
                dynamicInputs = $("#" + element2 + "-" + element1)
                dynamicInputs.find('.dynamic-input:not(:last)').remove();
                last = dynamicInputs.find('.dynamic-input:last')
                if (last.val().trim() !== '') {
                    last.val("")
                }
            })
        })
    }

    function create_validate() {
        $("#safetyguard-collapse .dynamic-input").each(function() {
            if ($(this).val().trim() === '') {
                $(this).remove();
            }
        });
        return true;
    }


    $(this).on("load", () => {
        CreateRule();
    })
</script>

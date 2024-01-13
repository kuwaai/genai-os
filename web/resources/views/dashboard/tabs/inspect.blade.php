<x-chat.functions />
<div class="w-full overflow-hidden flex">
    <form method="post"
        class="flex flex-col bg-white dark:bg-gray-700 p-2 text-white w-72 flex-shrink-0 relative overflow-y-auto overflow-x-hidden scrollbar">
        @csrf
        <label for="tagInput" class="block uppercase tracking-wide dark:text-white">搜尋訊息</label>
        <input placeholder="過濾內容"
            class="appearance-none block w-full text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
        <div id="inspect_targetInputsContainer"></div>
        <div class="flex flex-wrap -mx-3">
            <div class="w-full px-3">
                <label for="tagInput" class="block uppercase tracking-wide dark:text-white">過濾模型</label>
                <div class="relative mt-1">
                    <div id="inspect_tagContainer" class="mt-2 flex flex-wrap">
                        <input id="inspect_tagInput" type="text"
                            class="bg-transparent border-2 rounded-lg placeholder:text-black dark:placeholder:text-white"
                            placeholder="請選擇模型">
                    </div>
                    <div id="inspect_tagSuggestions" style="display:none;"
                        class="absolute z-10 mt-2 bg-white border border-gray-300 rounded-md shadow-lg p-2">
                    </div>
                </div>
            </div>
        </div>

    </form>
    
    <div class="flex flex-col">
        <div class="flex-1 text-black h-full flex flex-col w-full bg-gray-200 dark:bg-gray-600 scrollbar overflow-y-auto ">
            @php 
            $histories = App\Models\Histories::where('isbot', '=', true)->paginate(15, ['*'], 'page', session('page') ?? 1);
            @endphp
            @foreach ($histories as $history)
                <div class="flex-1 p-4 flex flex-col-reverse scrollbar">
                    <div>
                        @php
                            $inptHistory = App\Models\Histories::join('chats', 'chats.id', '=', 'histories.chat_id')
                                ->where('chat_id', '=', $history->chat_id)
                                ->where('isbot', '=', false)
                                ->orderby('chats.llm_id')
                                ->orderby('histories.id', 'desc')
                                ->first();
                        @endphp
                        <x-chat.message :history="$inptHistory" :readonly="true" />
                        <x-chat.message :history="$history" :readonly="true" />
                    </div>
                </div>
            @endforeach
        </div>
    
        <div class="mt-auto">
            <ul class="pagination">
                {{ $histories->onEachSide(3)->links('components.pagination', ['tab' => 'inspect']) }}
            </ul>
        </div>
    </div>

</div>
<script>
    const inspect_tagInput = document.getElementById('inspect_tagInput');
    const inspect_tagSuggestions = document.getElementById('inspect_tagSuggestions');
    const inspect_tagContainer = document.getElementById('inspect_tagContainer');

    const inspect_enabled_tags = JSON.parse({!! json_encode(
        App\Models\LLMs::orderby('order')->orderby('order')->where('enabled', '=', true)->get()->pluck('access_code')->toJson(),
    ) !!});
    const inspect_disabled_tags = JSON.parse({!! json_encode(
        App\Models\LLMs::orderby('order')->orderby('order')->where('enabled', '=', false)->get()->pluck('access_code')->toJson(),
    ) !!});
    const inspect_selectedTags = [];

    inspect_tagInput.addEventListener('input', (event) => {
        const inputValue = event.target.value.toLowerCase();
        const filteredTags = inspect_filterTags(inputValue);

        if (filteredTags.length == 0) {
            inspect_clearSuggestions();
        } else {
            inspect_render_tagSuggestions(filteredTags);
        }
    });
    inspect_tagInput.addEventListener('focus', () => {
        const inputValue = inspect_tagInput.value.toLowerCase();
        const filteredTags = inspect_filterTags(inputValue);
        inspect_render_tagSuggestions(filteredTags);
    });
    inspect_tagInput.addEventListener('blur', () => {
        setTimeout(inspect_clearSuggestions, 200);
    });

    function inspect_filterTags(inputValue) {
        const allTags = [...inspect_enabled_tags, ...inspect_disabled_tags];
        return allTags.filter(tag => tag.toLowerCase().includes(inputValue) && !inspect_selectedTags.includes(tag));
    }

    function inspect_render_tagSuggestions(filteredTags) {
        inspect_tagSuggestions.innerHTML = '';
        if (filteredTags.length != 0) {
            const suggestionContainer = document.createElement('div');
            suggestionContainer.className = 'flex flex-wrap';

            filteredTags.forEach(tag => {
                const suggestionItem = inspect_createSuggestionItem(tag);
                suggestionContainer.appendChild(suggestionItem);
            });

            inspect_tagSuggestions.appendChild(suggestionContainer);
            $(inspect_tagSuggestions).show();
        }
    }

    function inspect_createSuggestionItem(tag) {
        const suggestionItem = document.createElement('div');
        suggestionItem.className = 'p-2 cursor-pointer text-black bg-gray-300 hover:bg-gray-100 rounded-md mb-2 mr-2';
        suggestionItem.textContent = tag;
        suggestionItem.addEventListener('click', () => inspect_addTag(tag));
        return suggestionItem;
    }

    function inspect_clearSuggestions() {
        inspect_tagSuggestions.innerHTML = '';
        $(inspect_tagSuggestions).hide();
    }

    function inspect_addTag(tag) {
        inspect_selectedTags.push(tag);

        const tagElement = inspect_createTagElement(tag);
        inspect_tagContainer.prepend(tagElement);
        inspect_updateTargetInput();
        inspect_tagInput.value = '';
        inspect_clearSuggestions();

        tagElement.addEventListener('click', () => inspect_removeTag(tag, tagElement));
    }

    function inspect_createTagElement(tag) {
        const tagElement = document.createElement('div');
        tagElement.className = 'bg-blue-500 hover:bg-red-600 text-white px-2 py-1 rounded-md cursor-pointer mr-2 mb-2';
        tagElement.textContent = tag;
        return tagElement;
    }

    function inspect_removeTag(tag, tagElement) {
        const index = inspect_selectedTags.indexOf(tag);
        if (index !== -1) {
            inspect_selectedTags.splice(index, 1);
        }

        inspect_tagContainer.removeChild(tagElement);
        inspect_updateTargetInput();
    }

    function inspect_updateTargetInput() {
        const targetInputsContainer = document.getElementById('targetInputsContainer');
        targetInputsContainer.innerHTML = ''; // Clear existing inputs

        inspect_selectedTags.forEach((tag, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `target[]`; // Use index to create multiple inputs
            input.value = tag;
            targetInputsContainer.appendChild(input);
        });
    }
</script>

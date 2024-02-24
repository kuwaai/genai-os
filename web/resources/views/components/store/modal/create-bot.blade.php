@props(['result'])

<div id="create-bot-modal" data-modal-backdropClasses="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-40"
    tabindex="-1" aria-hidden="true"
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
                    {{ __('Create Bot') }}
                </h3>
            </div>
            <!-- Modal body -->
            <form method="post" action="{{ route('store.create') }}" class="p-6" id="create_room"
                onsubmit="return checkForm()">
                @csrf
                <ul class="flex flex-wrap -mx-3 mb-2 items-center">
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <div class="flex flex-wrap -mx-3">
                            <div class="w-full px-3 flex flex-col items-center">
                                <label for="llm_name">
                                    <img id="llm_img" class="rounded-full m-auto bg-black" width="50px"
                                        height="50px"
                                        src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z/C/HgAGlwJ/lXeUPwAAAABJRU5ErkJggg==">
                                </label>
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
                                oninput='$("#llm_img").attr("src",$(`#llm-list option[value="${$(this).val()}"]`).attr("src") || "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z/C/HgAGlwJ/lXeUPwAAAABJRU5ErkJggg==")'
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="基底模型">
                            <datalist id="llm-list">
                                @foreach ($result as $LLM)
                                    <option
                                        src="{{ strpos($LLM->image, 'data:image/png;base64') === 0 ? $LLM->image : asset(Storage::url($LLM->image)) }}"
                                        value="{{ $LLM->name }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label for="bot-name"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Name') }}</label>
                            <input type="text" id="bot-name" name="bot-name" autocomplete="off"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="{{ __('Bot Name') }}">
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label for="bot-describe"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Description') }}</label>
                            <input type="text" id="bot-describe" name="bot-describe" autocomplete="off"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="{{ __('Bot Description') }}">
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <p class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Startup Prompt') }}</p>
                            <x-store.prompt-form />
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                for="welcome-message">{{ __('Welcome message') }}</label>
                            <div class="flex items-center">
                                <textarea id="welcome-message" name="welcome-message" type="text" oninput="adjustTextareaRows(this)" rows="0"
                                    max-rows="5" placeholder="{{ __('Welcome message that shows but won\'t be passed inside when chatting.') }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                </ul>
                <div>
                    <div class="border border-black dark:border-white border-1 rounded-lg overflow-hidden">
                        <button type="submit"
                            class="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-500 hover:bg-gray-400 transition duration-300">
                            <p class="flex-1 text-center text-gray-700 dark:text-white">{{ __('Create') }}
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
    function checkForm() {
        if ($("#create_room input[name='llm_name']").val() && $("#create_room input[name='bot-name']").val()) {
            return true;
        }
        if (!$("#create_room input[name='llm_name']").val()) $("#create_error").text(
            {{ __('You must select a base model') }})
        else if (!$("#create_room input[name='bot-name']").val()) $("#create_error").text("{{ __('You must name your bot') }}")
        $("#create_error").show().delay(3000).fadeOut();
        return false;
    }
</script>

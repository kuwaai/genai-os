<div id="detail-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
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
            <div class="p-6">
                <ul class="flex flex-wrap -mx-3 mb-2 items-center">
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <div class="flex flex-wrap -mx-3">
                            <div class="w-full px-3 flex flex-col items-center">
                                <img class="rounded-full m-auto bg-black" width="50px" height="50px"
                                    src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z/C/HgAGlwJ/lXeUPwAAAABJRU5ErkJggg==">
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 px-3 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2"
                                for="llm_name">
                                選取模型
                            </label>
                            <input readonly type="text" name="llm_name" autocomplete="off"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="基底模型">
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label for="bot-name"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('store.bot.name') }}</label>
                            <input readonly type="text" name="bot-name" autocomplete="off"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="{{ __('store.bot.name.label') }}">
                        </div>
                    </div>
                    <div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <div class="w-full">
                            <label for="bot-describe"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('store.bot.description') }}</label>
                            <input readonly type="text" name="bot-describe" autocomplete="off"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="{{ __('store.bot.description.label') }}">
                        </div>
                    </div>
                    <!--<div class="w-full px-3 mt-2 flex justify-center items-center flex-wrap md:flex-nowrap">
                        <button type="button"
                            class="bg-green-500 hover:bg-green-600 text-white focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            {{ __('manage.button.save') }}
                        </button>
                        <button type="button" id="delete_button"
                            class="bg-red-500 hover:bg-red-600 text-white focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            {{ __('manage.button.delete') }}
                        </button>
                    </div>-->
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
    function detail_update(data) {
        $("#detail-modal h3").text(data.name);
        $("#detail-modal input[name=llm_name]").val(data.llm_name)
        $("#detail-modal input[name=bot-name]").val(data.name)
        $("#detail-modal img").attr("src", data.image)
        $("#detail-modal input[name=bot-describe]").val(data.description)
    }
</script>

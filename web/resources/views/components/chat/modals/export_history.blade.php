@props(['name'])

<div id="exportModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
    class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-2xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('Export Chat') }}
                </h3>
                <button type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                    data-modal-hide="exportModal">
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
                <label class="text-black dark:text-white" for="export_json">{{ __('JSON format') }}</label>
                <textarea id="export_json" rows="5" readonly
                    class="w-full pl-4 pr-12 py-2 rounded text-black scrollbar dark:text-white placeholder-black dark:placeholder-white bg-gray-200 dark:bg-gray-600 border border-gray-300 focus:outline-none shadow-none border-none focus:ring-0 focus:border-transparent rounded-l-md resize-none"></textarea>

                <label class="text-black dark:text-white" for="export_tsv">{{ __('Tab Separate Values') }}</label>
                <textarea id="export_tsv" rows="5" readonly
                    class="w-full pl-4 pr-12 py-2 rounded text-black scrollbar dark:text-white placeholder-black dark:placeholder-white bg-gray-200 dark:bg-gray-600 border border-gray-300 focus:outline-none shadow-none border-none focus:ring-0 focus:border-transparent rounded-l-md resize-none"></textarea>
                <a id="download_holder_json" style="display:none;" download="{{ $name . '.json' }}"></a>
                <a id="download_holder_tsv" style="display:none;" download="{{ $name . '.txt' }}"></a>
            </div>
            <!-- Modal footer -->
            <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b dark:border-gray-600">
                <div id="export_json_btn" role="tooltip"
                    class="absolute z-10 inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm tooltip dark:bg-gray-600 opacity-0 invisible"
                    style="position: absolute; inset: auto auto 0px 0px; margin: 0px; transform: translate3d(352.8px, -108px, 0px);"
                    data-popper-placement="top">
                    {{ __('Unescaped JSON Format') }}
                    <div class="tooltip-arrow" data-popper-arrow=""
                        style="position: absolute; left: 0px; transform: translate3d(89.6px, 0px, 0px);"></div>
                </div>
                <button data-modal-hide="exportModal" data-tooltip-target="export_json_btn"
                    onclick='$("#download_holder_json").attr("href",window.URL.createObjectURL(new Blob([$("#export_json").val()], { type: "text/plain" }))); $("#download_holder_json")[0].click();'
                    class="bg-green-500 hover:bg-green-600 px-3 py-2 rounded cursor-pointer text-white">{{ __('Download JSON') }}</button>
                <div id="export_txt_btn" role="tooltip"
                    class="absolute z-10 inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm tooltip dark:bg-gray-600 opacity-0 invisible"
                    style="position: absolute; inset: auto auto 0px 0px; margin: 0px; transform: translate3d(352.8px, -108px, 0px);"
                    data-popper-placement="top">
                    {{ __('Tab Separated Value') }}
                    <div class="tooltip-arrow" data-popper-arrow=""
                        style="position: absolute; left: 0px; transform: translate3d(89.6px, 0px, 0px);"></div>
                </div>
                <button data-modal-hide="exportModal" data-tooltip-target="export_txt_btn"
                    onclick='$("#download_holder_tsv").attr("href",window.URL.createObjectURL(new Blob([$("#export_tsv").val()], { type: "text/plain" }))); $("#download_holder_tsv")[0].click();'
                    class="bg-green-500 hover:bg-green-600 px-3 py-2 rounded cursor-pointer text-white">{{ __('Download TXT') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="feedback" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
                    class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                    <div class="relative w-full max-w-2xl max-h-full">
                        <!-- Modal content -->
                        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                            <!-- Modal header -->
                            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                                <div style="display:none;"
                                    class="mr-4 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:h-10 sm:w-10 bg-green-100">
                                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round" class="icon-lg text-green-700"
                                        aria-hidden="true" height="1em" width="1em"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
                                        </path>
                                    </svg>
                                </div>
                                <div style="display:none;"
                                    class="mr-4 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:h-10 sm:w-10 bg-red-100">
                                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round" class="icon-lg text-red-600"
                                        aria-hidden="true" height="1em" width="1em"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="text-xl my-auto font-semibold text-gray-900 dark:text-white">
                                    {{ __('Provide feedback') }}
                                </h3>
                                <button type="button"
                                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                    data-modal-hide="feedback">
                                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 14 14">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                    </svg>
                                    <span class="sr-only">Close modal</span>
                                </button>
                            </div>
                            <!-- Modal body -->
                            <div class="p-6">
                                <form id="feedback_form" action="{{ route('chat.feedback') }}" method="post">
                                    @csrf
                                    <input name="history_id" style="display:none;">
                                    <input name="type" style="display:none;">

                                    <textarea rows="1" maxlength="4096" max-rows="5" name="feedbacks" id="feedbacks"
                                        class="w-full resize-none" oninput="adjustTextareaRows(this)"></textarea>
                                    @php
                                        $badFeedback = [['value' => 'unsafe', 'label' => __('Unsafe')], ['value' => 'incorrect', 'label' => __('Incorrect')], ['value' => 'inrelvent', 'label' => __('Inrelvent')], ['value' => 'language', 'label' => __('In Wrong Language')]];

                                        $goodFeedback = [['value' => 'Correct', 'label' => __('Correct')], ['value' => 'Simplify', 'label' => __('Simplify')], ['value' => 'Integrated', 'label' => __('Integrated')]];
                                    @endphp

                                    @foreach ($badFeedback as $key => $item)
                                        <div class="bad">
                                            <input name="feedback[]" id="feedback_{{ $key + 1 }}"
                                                type="checkbox" value="{{ $item['value'] }}">
                                            <label for="feedback_{{ $key + 1 }}"
                                                class="ml-2 text-sm font-medium text-gray-800 dark:text-gray-300">{{ $item['label'] }}</label>
                                        </div>
                                    @endforeach

                                    @foreach ($goodFeedback as $key => $item)
                                        <div class="good">
                                            <input name="feedback[]"
                                                id="feedback_{{ count($badFeedback) + $key + 1 }}" type="checkbox"
                                                value="{{ $item['value'] }}">
                                            <label for="feedback_{{ count($badFeedback) + $key + 1 }}"
                                                class="ml-2 text-sm font-medium text-gray-800 dark:text-gray-300">{{ $item['label'] }}</label>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-end">
                                        <button data-modal-hide="feedback" type="submit"
                                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">{{ __('Submit feedback') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
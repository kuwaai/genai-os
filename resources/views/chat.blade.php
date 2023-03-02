<x-app-layout>
    <div class="flex h-full max-w-7xl mx-auto py-2">
        <div class="bg-gray-800 text-white w-64 flex-shrink-0 relative rounded-l-lg overflow-hidden">
            <div class="p-3">
                @foreach (App\Models\LLMs::all() as $LLM)
                <div class="mb-2 border border-white border-1 rounded-lg">
                    <a href="{{$LLM->link}}" target="_blank"
                        class="inline-block menu-btn mt-2 w-auto ml-4 mr-auto h-6 transition duration-300 text-blue-300">{{$LLM->name}}</a>
                    <div class="m-2 border border-white border-1 rounded-lg overflow-hidden">
                        <button
                            class="flex menu-btn flex items-center justify-center w-full h-12 hover:bg-gray-700 transition duration-300">
                            <p class="flex-1 text-center">New Chat</p>
                        </button>
                    </div>
                    @foreach (App\Models\Chats::where("user_id", Auth::user()->id)->where("llm_id", $LLM->id)->get() as $chat)
                    <div class="m-2 border border-white border-1 rounded-lg overflow-hidden">
                        <button
                            class="flex menu-btn flex items-center justify-center w-full h-12 hover:bg-gray-700 transition duration-300">
                            <p class="flex-1 text-center">{{$chat->name}}</p>
                        </button>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
        <div class="flex-1 h-full flex flex-col w-full bg-gray-600 shadow-xl rounded-r-lg overflow-hidden">
            <div class="flex-1 p-4 overflow-y-scroll flex flex-col-reverse scrollbar">
                <div class="flex w-full mt-2 space-x-3 max-w-xs">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">Bot
                    </div>
                    <div>
                        <div class="bg-gray-300 p-3 rounded-r-lg rounded-bl-lg">
                            <p class="text-sm whitespace-pre-line">Oh!
                                I understand your question,
                                Here's the answer...</p>
                        </div>
                    </div>
                </div>
                <div class="flex w-full mt-2 space-x-3 max-w-xs ml-auto justify-end">
                    <div>
                        <div class="bg-blue-600 text-white p-3 rounded-l-lg rounded-br-lg">
                            <p class="text-sm whitespace-pre-line">I want to ask something...</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">User
                    </div>
                </div>
                <div class="flex w-full mt-2 space-x-3 max-w-xs">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">Bot
                    </div>
                    <div>
                        <div class="bg-gray-300 p-3 rounded-r-lg rounded-bl-lg">
                            <p class="text-sm whitespace-pre-line">Hello! I'm a bot!
                                How can I help you?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-500 p-4 h-20">
                <div class="flex">
                    <input type="text" placeholder="Enter your text here"
                        class="w-full px-4 py-2 text-white placeholder-white bg-gray-600 border border-gray-300 focus:outline-none shadow-none border-none focus:ring-0 focus:border-transparent rounded-l-md">
                    <button type="submit"
                        class="inline-flex items-center justify-center w-12 h-12 bg-blue-500 rounded-r-md hover:bg-blue-700">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M2.5 9.5L17.5 2.5V17.5L2.5 10.5V9.5Z">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

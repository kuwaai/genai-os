@props(['llmId', 'chatId', 'LLM'])

@if (request()->route('chat_id'))
    <form id="editChat" action="{{ route('chat.edit') }}" method="post" class="hidden">
        @csrf
        <input name="id" value="{{ App\Models\Chats::findOrFail(request()->route('chat_id'))->id }}" />
        <input name="new_name" />
    </form>
@endif
<div id="chatHeader"
    class="bg-gray-300 dark:bg-gray-700 p-2 sm:p-4 h-20 max-h-20 overflow-hidden text-gray-700 dark:text-white flex">

    <button
        class="block sm:hidden text-center text-white bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-6 py-2 dark:bg-gray-700 dark:hover:bg-gray-800 focus:outline-none dark:focus:ring-blue-800"
        type="button" data-drawer-target="chatlist_drawer" data-drawer-show="chatlist_drawer"
        aria-controls="chatlist_drawer">
        <i class="fas fa-bars"></i>
    </button>
    <div
        class="flex-shrink-0 mx-2 my-auto h-10 w-10 rounded-full bg-black flex items-center justify-center overflow-hidden">
        <img class="h-full w-full"
            src="{{ strpos($LLM->image, 'data:image/png;base64') === 0 ? $LLM->image : asset(Storage::url($LLM->image)) }}">
    </div>
    @if ($llmId)
        <p class="flex-1 flex flex-wrap items-center mr-3 overflow-y-auto overflow-x-hidden scrollbar">
            {{ __('New Chat with') }}
            {{ App\Models\LLMs::findOrFail($llmId)->name }}</p>
        @if (request()->user()->hasPerm('Chat_update_import_chat'))
            <div class="flex">
                <button onclick="import_chat()" data-modal-target="importModal" data-modal-toggle="importModal"
                    class="bg-green-500 ml-3 hover:bg-green-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                    <i class="fas fa-file-import"></i>
                </button>
            </div>
        @endif
    @elseif($chatId)
        <p class="flex-1 flex flex-wrap items-center mr-3 overflow-y-auto overflow-x-hidden scrollbar"
            style='word-break:break-word'>
            {{ App\Models\Chats::findOrFail($chatId)->name }}</p>

        <div class="flex">
            <button onclick="saveChat()"
                class="bg-green-500 ml-3 hover:bg-green-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center hidden">
                <i class="fas fa-save"></i>
            </button>
            <button onclick="editChat()"
                class="bg-orange-500 ml-3 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                <i class="fas fa-pen"></i>
            </button>
            @if (request()->user()->hasPerm('Chat_delete_chatroom'))
                <x-chat.modals.delete_confirm />
                <button data-modal-target="delete_chat_modal" data-modal-toggle="delete_chat_modal"
                    class="bg-red-500 ml-3 hover:bg-red-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
                    <i class="fas fa-trash"></i>
                </button>
            @endif
        </div>
    @endif
</div>

<script>
    function editChat() {
        $("#chatHeader button").find('.fa-pen').parent().addClass('hidden');
        $("#chatHeader button").find('.fa-save').parent().removeClass('hidden');
        name = $("#chatHeader > p:eq(0)").text().trim();
        $("#chatHeader > p:eq(0)").html(
            `<input type='text' class='form-input rounded-md w-full bg-gray-200 dark:bg-gray-700 border-gray-300 border'/>`
        );
        $("#chatHeader > p:eq(0) input").val(name).attr('old', name);
        $("#chatHeader >p >input:eq(0)").keypress(function(e) {
            if (e.which == 13) saveChat();
        });
    }


    function saveChat() {
        input = $("#chatHeader >p >input:eq(0)")
        if (input.val() != input.attr("old")) {
            $("#editChat input:eq(2)").val(input.val())
            $("#editChat").submit();
        }
        $("#chatHeader button").find('.fa-pen').parent().removeClass('hidden');
        $("#chatHeader button").find('.fa-save').parent().addClass('hidden');
        $("#chatHeader >p").text(input.val())
    }
</script>

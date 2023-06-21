<x-app-layout>

    <!-- Main modal -->
    <div id="edit_llm" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button"
                    class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                    data-modal-hide="edit_llm">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="px-6 py-6 ml:px-8">
                    <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white"></h3>

                    <form id="update_LLM_by_ID" method="post" enctype="multipart/form-data" autocomplete="off"
                        action="{{ route('update_LLM_by_id') }}" class="w-full max-w-xl">
                        @csrf
                        @method('patch')
                        <input id="update_img" name="image" type="file" style="display:none">
                        <input name="id" style="display:none">
                        <div class="flex flex-wrap -mx-3 mb-2 items-center">
                            <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                                <div class="flex flex-wrap -mx-3">
                                    <div class="w-full px-3 flex flex-col items-center">
                                        <label for="update_img">
                                            <span
                                                class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2">
                                                LLM Image
                                            </span>
                                            <img id="image"
                                                class="rounded-full border border-gray-400 dark:border-gray-900 m-auto bg-black"
                                                width="50px" height="50px" class="m-auto"
                                                src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z/C/HgAGlwJ/lXeUPwAAAABJRU5ErkJggg==" />
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full md:w-2/3 px-3">
                                <label class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2"
                                    for="llm_name">
                                    LLM Name
                                </label>
                                <input name="name" required
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    id="llm_name" placeholder="LLM Name">
                            </div>
                        </div>
                        <div class="flex flex-wrap -mx-3 mb-2">
                            <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                                <label class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2"
                                    for="version">
                                    Version
                                </label>
                                <input name="version"
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    id="version" type="text" placeholder="Version">
                            </div>
                            <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                                <label class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2"
                                    for="access_code">
                                    Access Code
                                </label>
                                <input name="access_code" required
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    id="access_code" type="text" placeholder="Access Code">
                            </div>
                            <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                                <label class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2"
                                    for="order">
                                    Order
                                </label>
                                <input name="order"
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    id="order" type="text" placeholder="Order">
                            </div>
                        </div>
                        <div class="flex flex-wrap -mx-3 mb-2">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2"
                                    for="link">
                                    Link
                                </label>
                                <input name="link"
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    id="link" placeholder="Link for more information to this LLM" value="">
                            </div>
                        </div>
                        <div class="flex flex-wrap -mx-3 mb-2">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide dark:text-white text-xs font-bold mb-2"
                                    for="description">
                                    Description
                                </label>
                                <input name="description"
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    id="description" placeholder="Description for this LLM">
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="button" data-modal-target="popup-modal2" data-modal-toggle="popup-modal2"
                                class="bg-green-500 hover:bg-green-600 text-white focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                Save
                            </button>
                            <div id="popup-modal2" data-modal-backdrop="static" tabindex="-2"
                                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
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
                                            <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are
                                                you sure you want to update this LLM?</h3>
                                            <button data-modal-hide="popup-modal2" type="submit"
                                                class="text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                                Yes, I'm sure
                                            </button>
                                            <button data-modal-hide="popup-modal2" type="button"
                                                class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No,
                                                cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button data-modal-target="popup-modal" data-modal-toggle="popup-modal" type="button"
                                id="delete_button"
                                class="bg-red-500 hover:bg-red-600 text-white focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                Delete
                            </button>
                            <div id="popup-modal" data-modal-backdrop="static" tabindex="-2"
                                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
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
                                            <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are
                                                you sure you want to delete this LLM?</h3>
                                            <button id="delete_llm" data-modal-hide="popup-modal" type="button"
                                                class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                                Yes, I'm sure
                                            </button>
                                            <button data-modal-hide="popup-modal" type="button"
                                                class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No,
                                                cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="post" action="{{ route('System.update') }}" class="space-y-6">
                        <header class="flex">
                            @csrf
                            @method('patch')
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('System Settings') }}

                                </h2>

                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('All the system settings here') }}
                                </p>
                            </div>
                            <button type="submit"
                                class="px-4 py-2 rounded bg-green-500 hover:bg-green-700 ml-auto text-white"><i
                                    class="fas fa-save"></i></button>
                        </header>
                        <div class="max-w-xl">
                            <label class="relative inline-flex items-center mr-5 cursor-pointer">
                                <input type="checkbox" value="allow" name="allow_register" class="sr-only peer"
                                    {{ \App\Models\SystemSetting::where('key', 'allowRegister')->first()->value == 'true' ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600">
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Allow
                                    Register</span>
                            </label>

                            <div>
                                <x-input-label for="agent_location" :value="__('Agent API Location')" />
                                <div class="flex items-center">
                                    <x-text-input id="agent_location" name="agent_location" type="text"
                                        class="mr-2 mb-1 block w-full"
                                        value="{{ \App\Models\SystemSetting::where('key', 'agent_location')->first()->value }}"
                                        required autocomplete="no" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                @if (session('status') === 'setting_saved')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600 dark:text-green-400">
                                        {{ __('Saved.') }}</p>
                                @elseif (session('status') === 'smtp_not_configured')
                                    <p x-data="{ show: true }" x-show="show" 
                                        class="text-sm text-red-600 dark:text-red-400">
                                        {{ __("Failed to allow registering, SMTP haven't been configured!") }}</p>
                                    <p x-data="{ show: true }" x-show="show" 
                                        class="text-sm text-gray-600 dark:text-green-400">
                                        {{ __('The rest of setting are saved.') }}</p>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <section>
                        <div class="flex">
                            <header class="mr-auto">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('LLM Managements') }}
                                </h2>

                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('Edit the LLM that is availables') }}
                                </p>
                            </header>

                            <button onclick="CreateRow()" data-modal-target="edit_llm" data-modal-toggle="edit_llm"
                                class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4  rounded flex items-center justify-center">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li class="text-red-500">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div
                            class="shadow overflow-hidden border dark:border-gray-900 sm:rounded-lg mt-3 overflow-x-auto scrollbar">
                            <form id="del_LLM_by_ID" method="post" action="{{ route('delete_LLM_by_id') }}"
                                style="display:none">
                                @csrf
                                @method('delete')
                                <input name="id">
                            </form>
                            <table class="whitespace-nowrap w-full">
                                <thead class="bg-gray-200 dark:bg-gray-900">
                                    <tr>
                                        <th scope="col"
                                            class="px-4 py-2 text-center text-xs font-medium uppercase tracking-wider">
                                            Image
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">
                                            Access Code
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (App\Models\LLMs::orderby('order')->orderby('created_at')->get() as $LLM)
                                        <tr id="llm{{ $LLM->id }}">
                                            <td class="px-3 py-2 flex justify-center">
                                                <img class="rounded-full border border-gray-400 dark:border-gray-900 bg-black"
                                                    width="40px" height="40px" class="m-auto"
                                                    src="{{ strpos($LLM->image, 'data:image/png;base64') === 0 ? $LLM->image : asset(Storage::url($LLM->image)) }}" />
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium">{{ $LLM->name }}</div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium">{{ $LLM->access_code }}</div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex items-center space-x-4">
                                                    <button
                                                        class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center"
                                                        data-modal-target="edit_llm" data-modal-toggle="edit_llm"
                                                        onclick='edit({!! json_encode(
                                                            [
                                                                strpos($LLM->image, 'data:image/png;base64') === 0 ? $LLM->image : asset(Storage::url($LLM->image)),
                                                                $LLM->name,
                                                                $LLM->link,
                                                                $LLM->order,
                                                                $LLM->access_code,
                                                                $LLM->id,
                                                                $LLM->description,
                                                                $LLM->version,
                                                            ],
                                                            JSON_HEX_APOS,
                                                        ) !!})'>
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <a class="{{ $LLM->enabled ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }} text-white font-bold py-3 px-4 rounded flex items-center justify-center"
                                                        href="{{ route('toggle_LLM', $LLM->id) }}">
                                                        <i class="fas fa-power-off"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Debug buttons') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Just a menu of buttons for debugging, should be removed in future') }}
                            </p>
                        </header>
                        <div class="mt-3 mx-auto flex">
                            <a class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center"
                                href="{{ route('reset_redis') }}">Reset<br>Redis<br>Caches</a>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
    <script>
        function DeleteRow(id) {
            $("#del_LLM_by_ID input:eq(2)").val(id);
            $("#del_LLM_by_ID").submit();
        }

        function CreateRow() {
            $("#edit_llm form").attr("action", "{{ route('create_new_LLM') }}");
            $("#update_LLM_by_ID img").attr("src",
                "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z/C/HgAGlwJ/lXeUPwAAAABJRU5ErkJggg=="
            )
            $("#update_LLM_by_ID input:eq(1)").prop("disabled", true)
            $("#update_LLM_by_ID input[name='name']").val("")
            $("#update_LLM_by_ID input[name='link']").val("")
            $("#update_LLM_by_ID input[name='order']").val("")
            $("#update_LLM_by_ID input[name='access_code']").val("")
            $("#update_LLM_by_ID input[name='id']").val("")
            $("#update_LLM_by_ID input[name='description']").val("")
            $("#update_LLM_by_ID input[name='version']").val("")
            $("#edit_llm h3:eq(0)").text("Create LLM Profile")
            $("#edit_llm h3:eq(1)").text("Are you sure you want to CREATE this LLM Profile?")
            $("#delete_button").hide()
        }

        function edit(data) {
            console.log(data)
            $("#update_LLM_by_ID img").attr("src", data[0])
            $("#update_LLM_by_ID input[name='_method']").prop("disabled", false)
            $("#update_LLM_by_ID input[name='name']").val(data[1])
            $("#update_LLM_by_ID input[name='link']").val(data[2])
            $("#update_LLM_by_ID input[name='order']").val(data[3])
            $("#update_LLM_by_ID input[name='access_code']").val(data[4])
            $("#update_LLM_by_ID input[name='id']").val(data[5])
            $("#update_LLM_by_ID input[name='description']").val(data[6])
            $("#update_LLM_by_ID input[name='version']").val(data[7])
            $("#edit_llm form").attr("action", "{{ route('update_LLM_by_id') }}")
            $("#delete_llm").off('click').on('click', function() {
                DeleteRow(data[5]);
            });
            $("#edit_llm h3:eq(0)").text("Modify LLM Profile")
            $("#edit_llm h3:eq(1)").text("Are you sure you want to UPDATE this LLM Profile?")
            $("#edit_llm h3:eq(2)").text("Are you sure you want to DELETE this LLM Profile?")
            $("#delete_button").show()
        }
        $('#update_img').on('change', function(event) {
            var input = event.target;
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("#update_LLM_by_ID img").attr("src", e.target.result)
                };
                reader.readAsDataURL(input.files[0]);
            }
        });
    </script>
</x-app-layout>

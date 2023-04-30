<x-app-layout>
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('System Settings') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('All the system settings here') }}
                            </p>
                        </header>
                        <div class="max-w-xl">
                            <form method="post" action="{{ route('System.update') }}" class="mt-6 space-y-6">
                                @csrf
                                @method('patch')

                                <div>
                                    <x-input-label for="agent_location" :value="__('Agent API Location')" />
                                    <x-text-input id="agent_location" name="agent_location" type="text" class="mt-1 block w-full"
                                        value="{{ \App\Models\SystemSetting::where('key', 'agent_location')->first()->value }}"
                                        required autocomplete="no" />
                                </div>

                                <div class="flex items-center gap-4">
                                    <x-primary-button>{{ __('Save') }}</x-primary-button>

                                    @if (session('status') === 'agent_location-updated')
                                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                            class="text-sm text-gray-600 dark:text-green-400">{{ __('Saved.') }}</p>
                                    @endif
                                </div>
                            </form>
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
                                {{ __('LLM Managements') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Edit the LLM that is availables') }}
                            </p>
                        </header>
                        <div
                            class="shadow overflow-hidden border dark:border-gray-900 sm:rounded-lg mt-3 overflow-x-auto scrollbar">
                            <form id="del_LLM_by_ID" method="post" action="{{ route('delete_LLM_by_id') }}"
                                style="display:none">
                                @csrf
                                @method('delete')
                                <input name="id">
                            </form>
                            <form id="update_LLM_by_ID" method="post" enctype="multipart/form-data"
                                action="{{ route('update_LLM_by_id') }}" style="display:none">
                                @csrf
                                @method('patch')
                                <input id="update_img" name="image" type="file">
                                <input name="name">
                                <input name="link">
                                <input name="order">
                                <input name="access_code">
                                <input name="id">
                                <input name="limit_per_day">
                            </form>
                            <form id="create_new_LLM" method="post" enctype="multipart/form-data"
                                action="{{ route('create_new_LLM') }}" style="display:none">
                                @csrf
                                <input id="new_img" name="image" type="file">
                                <input name="name">
                                <input name="link">
                                <input name="order">
                                <input name="access_code">
                                <input name="limit_per_day">
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
                                            Link
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">
                                            Order
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
                                                <img class="rounded-full border border-gray-400 dark:border-gray-900"
                                                    width="40px" class="m-auto"
                                                    src="{{ asset(Storage::url($LLM->image)) }}" />
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium">{{ $LLM->name }}</div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium">{{ $LLM->link }}</div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium">{{ $LLM->order }}</div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium">{{ $LLM->access_code }}</div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex items-center space-x-4">
                                                    <button
                                                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center"
                                                        onclick="DeleteRow({{ $LLM->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <button
                                                        class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center"
                                                        onclick="EditRow({{ $LLM->id }})">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <button
                                                        class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center hidden"
                                                        onclick="SaveRow({{ $LLM->id }})">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                    <a class="{{ $LLM->enabled ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }} text-white font-bold py-3 px-4 rounded flex items-center justify-center"
                                                        href="{{ route('toggle_LLM', $LLM->id) }}">
                                                        <i class="fas fa-power-off"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <tr id="createLLM">
                                        <td class="px-3 py-2">
                                            <div class="flex items-center">
                                                <label
                                                    class="appearance-none m-auto border rounded py-2 px-3 border-gray-300 dark:border-gray-900 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 leading-tight placeholder:text-gray-700 dark:placeholder:text-gray-300 focus:outline-none focus:shadow-outline"
                                                    for="new_img">Upload</label>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center">
                                                <input
                                                    class="appearance-none border rounded w-20 py-2 px-3 text-gray-700 border-gray-300 dark:border-gray-900 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 leading-tight placeholder:text-gray-700 dark:placeholder:text-gray-300 focus:outline-none focus:shadow-outline"
                                                    id="new-name" type="text" placeholder="Name">
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center">
                                                <input
                                                    class="appearance-none border rounded w-full py-2 px-3 border-gray-300 dark:border-gray-900 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 leading-tight placeholder:text-gray-700 dark:placeholder:text-gray-300 focus:outline-none focus:shadow-outline"
                                                    id="new-link" type="text" placeholder="Link">
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center">
                                                <input
                                                    class="appearance-none border rounded w-20 py-2 px-3 border-gray-300 dark:border-gray-900 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 leading-tight placeholder:text-gray-700 dark:placeholder:text-gray-300 focus:outline-none focus:shadow-outline"
                                                    id="new-order" type="text" placeholder="Order">
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center">
                                                <input
                                                    class="appearance-none border rounded w-full py-2 px-3 border-gray-300 dark:border-gray-900 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 leading-tight placeholder:text-gray-700 dark:placeholder:text-gray-300 focus:outline-none focus:shadow-outline"
                                                    id="new-access_code" type="text" placeholder="Access Code">
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <button onclick="CreateRow()"
                                                class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4  rounded flex items-center justify-center">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
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

        function EditRow(id) {
            const row = $(`#llm${id}`);
            row.find('.fa-pen').parent().addClass('hidden');
            row.find('.fa-save').parent().removeClass('hidden');
            row.find('td').each(function(index) {
                const cell = $(this);
                const value = cell.find('div').text();
                if (index == 0) {
                    cell.html(
                        `<label class="appearance-none m-auto border rounded py-2 px-3 border-gray-300 dark:border-gray-900 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 leading-tight placeholder:text-gray-700 dark:placeholder:text-gray-300 focus:outline-none focus:shadow-outline" for="update_img" old="${cell.find("img").attr("src")}">Upload</label>`
                    )
                } else if (index < 5) {
                    cell.html(
                        `<input type="text" class="border rounded w-20 py-2 px-3 border-gray-300 dark:border-gray-900 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 leading-tight placeholder:text-gray-700 dark:placeholder:text-gray-300 focus:outline-none focus:shadow-outline form-input rounded-md ${index == 1 || index == 3 ? "w-20" : "w-full"}" old="${value}" value="${value}">`
                    );
                }
            });
        }

        function SaveRow(id) {
            const row = $(`#llm${id}`);
            const vals = row.find('td').find("input");
            let check = vals.toArray().some(input => input.value !== input.getAttribute('old'));
            if (!check && $("#update_img").val()) check = true;
            if (check && !vals.toArray().every(input => input.value !== "")) check = false;
            if (check) {
                $("#update_LLM_by_ID input").each(function(index) {
                    if (index < 7 && index > 2) $(this).val($(vals[index - 3]).val())
                    else if (index == 7) $(this).val(id);
                    else if (index == 8) $(this).val(100);
                });
                $("#update_LLM_by_ID").submit();
            }
            row.find('.fa-save').parent().addClass('hidden');
            row.find('.fa-pen').parent().removeClass('hidden');
            row.find('td').each(function(index) {
                const cell = $(this);
                if (index == 0) {
                    cell.html(
                        `<img class="rounded-full border border-gray-400 dark:border-gray-900" width="40px" src="${cell.find("label").attr('old')}">`
                    )
                } else if (index < 5) {
                    const value = cell.find("input").attr('old');
                    cell.html(`<div class="text-sm font-medium">${value}</div>`);
                }
            });
        }

        function CreateRow() {
            datas = $("#createLLM input");
            $("#create_new_LLM input").each(function(index) {
                if (index > 1 && index < 6) $(this).val($(datas[index - 2]).val());
                else if (index == 6) $(this).val(100);
            })
            $("#create_new_LLM").submit();
        }
    </script>
</x-app-layout>

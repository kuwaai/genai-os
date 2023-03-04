<x-app-layout>
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('LLM Mnaagements') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Edit the LLM that is availables') }}
                            </p>
                        </header>
                        <div
                            class="shadow overflow-hidden border-b border-gray-800 sm:rounded-lg mt-3 overflow-x-auto scrollbar">
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
                                <input name="limit_per_day">
                                <input name="API">
                                <input name="id">
                            </form>
                            <form id="create_new_LLM" method="post" enctype="multipart/form-data"
                                action="{{ route('create_new_LLM') }}" style="display:none">
                                @csrf
                                <input id="new_img" name="image" type="file">
                                <input name="name">
                                <input name="link">
                                <input name="limit_per_day">
                                <input name="API">
                            </form>
                            <table class="whitespace-nowrap w-full">
                                <thead class="bg-gray-800">
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
                                            Limit
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">
                                            API Endpoint
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (App\Models\LLMs::all() as $LLM)
                                        <tr id="llm{{ $LLM->id }}">
                                            <td class="px-3 py-2 flex">
                                                <img width="40px" class="m-auto"
                                                    src="{{ asset(Storage::url($LLM->image)) }}" />
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium">{{ $LLM->name }}</div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium">{{ $LLM->link }}</div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium">{{ $LLM->limit_per_day }}</div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="text-sm font-medium">{{ $LLM->API }}</div>
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
                                                    class="appearance-none m-auto border rounded py-2 px-3 text-gray-300 bg-gray-700 leading-tight placeholder:text-gray-300 focus:outline-none focus:shadow-outline"
                                                    for="new_img">Upload</label>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center">
                                                <input
                                                    class="appearance-none border rounded w-20 py-2 px-3 text-gray-300 bg-gray-700 leading-tight placeholder:text-gray-300 focus:outline-none focus:shadow-outline"
                                                    id="new-name" type="text" placeholder="Name">
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center">
                                                <input
                                                    class="appearance-none border rounded w-full py-2 px-3 text-gray-300 bg-gray-700 leading-tight placeholder:text-gray-300 focus:outline-none focus:shadow-outline"
                                                    id="new-link" type="text" placeholder="Link">
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center">
                                                <input
                                                    class="appearance-none border rounded w-20 py-2 px-3 text-gray-300 bg-gray-700 leading-tight placeholder:text-gray-300 focus:outline-none focus:shadow-outline"
                                                    id="new-limit" type="text" placeholder="Limit">
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center">
                                                <input
                                                    class="appearance-none border rounded w-full py-2 px-3 text-gray-300 bg-gray-700 leading-tight placeholder:text-gray-300 focus:outline-none focus:shadow-outline"
                                                    id="new-limit" type="text" placeholder="API Endpoint">
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
                        `<label class="appearance-none m-auto border rounded py-2 px-3 text-gray-300 bg-gray-700 leading-tight placeholder:text-gray-300 focus:outline-none focus:shadow-outline" for="update_img" old="${cell.find("img").attr("src")}">Upload</label>`
                        )
                } else if (index < 5) {
                    cell.html(
                        `<input type="text" class="form-input rounded-md ${index == 1 || index == 3 ? "w-20" : "w-full"} bg-gray-700" old="${value}" value="${value}">`
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
                });
                $("#update_LLM_by_ID").submit();
            }
            row.find('.fa-save').parent().addClass('hidden');
            row.find('.fa-pen').parent().removeClass('hidden');
            row.find('td').each(function(index) {
                const cell = $(this);
                if (index == 0) {
                    cell.html(`<img width="40px" class="m-auto" src="${cell.find("label").attr('old')}">`)
                } else if (index < 5) {
                    const value = cell.find("input").attr('old');
                    cell.html(`<div class="text-sm font-medium">${value}</div>`);
                }
            });
        }

        function CreateRow() {
            datas = $("#createLLM input");
            $("#create_new_LLM input").each(function(index) {
                if (index > 1) $(this).val($(datas[index - 2]).val());
            })
            $("#create_new_LLM").submit();
        }
    </script>
</x-app-layout>

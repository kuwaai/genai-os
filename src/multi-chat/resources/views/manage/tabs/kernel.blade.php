<div class="flex flex-col flex-1 h-full mx-auto overflow-y-auto scrollbar p-2">
    <form class="kernel-form" method="post" action="{{ route('manage.setting.update') }}" class="space-y-6 flex-1 m-4 border-2 border-gray-500 rounded p-2">
        <header class="flex">
            @csrf
            @method('patch')
            <input name='tab' value='kernel' hidden/>
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('manage.header.settings') }}

                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('manage.label.settings') }}
                </p>
            </div>
            <button type="submit" class="px-4 py-2 rounded bg-green-500 hover:bg-green-700 ml-auto text-white"><i
                    class="fas fa-save"></i></button>
        </header>
    <div>
        <x-input-label for="kernel_location" :value="__('manage.label.kernel_location')" />
        <div class="flex items-center">
            <x-text-input id="kernel_location" name="kernel_location" type="text"
                class="mr-2 mb-1 block w-full"
                value="{{ \App\Models\SystemSetting::where('key', 'kernel_location')->first()->value }}"
                autocomplete="off" />
        </div>
    </div>
    <div>
        <x-input-label for="safety_guard_location" :value="__('manage.label.safety_guard_API')" />
        <div class="flex items-center">
            <x-text-input id="safety_guard_location" name="safety_guard_location" type="text"
                class="mr-2 mb-1 block w-full"
                value="{{ \App\Models\SystemSetting::where('key', 'safety_guard_location')->first()->value }}"
                autocomplete="off" />
        </div>
    </div>
    </form>
    <div id="error-message" class="hidden text-red-500 dark:text-red-400"></div>
    <div id="loading-spinner" class="flex justify-center items-center py-8">
        <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <ul id="access-code-list"></ul>
    <div class="mb-6">
        <button id="create-record-btn"
            class="bg-green-500 text-white px-4 py-2 rounded dark:bg-green-600 hover:bg-green-600 dark:hover:bg-green-700">{{ __('manage.button.new_executor') }}</button>
    </div>
</div>

<div id="record-modal" class="fixed inset-0 z-50 hidden bg-gray-800 bg-opacity-75 flex items-center justify-center">
    <div class="bg-white rounded shadow-lg p-4 w-2/3 dark:bg-gray-800 dark:text-white">
        <h2 id="modal-title" class="text-xl font-semibold mb-4"></h2>
        <form id="record-form" class="space-y-2">
            @csrf
            <input type="hidden" name="original_access_code" id="original-access-code">
            <input type="hidden" name="original_endpoint" id="original-endpoint">
            <input type="hidden" name="original_status" id="original-status">
            <input type="hidden" name="original_history_id" id="original-history-id">
            <input type="hidden" name="original_user_id" id="original-user-id">
            <div>
                <label for="modal-access-code" class="block">{{ __('manage.label.accesscode') }}</label>
                <input type="text" name="access_code" id="modal-access-code"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-endpoint" class="block">{{ __('manage.label.endpoint') }}</label>
                <input type="text" name="endpoint" id="modal-endpoint"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-status" class="block">{{ __('manage.label.status') }}</label>
                <select name="status" id="modal-status"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
                    <option value="READY">{{ __('manage.label.ready') }}</option>
                    <option value="BUSY">{{ __('manage.label.busy') }}</option>
                </select>
            </div>
            <div>
                <label for="modal-history-id" class="block">{{ __('manage.label.historyid') }}</label>
                <input type="text" name="history_id" id="modal-history-id" value='-1'
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-user-id" class="block">{{ __('manage.label.userid') }}</label>
                <input type="text" name="user_id" id="modal-user-id" value='-1'
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div class="flex justify-between">
                <button type="button" class="bg-gray-300 text-black rounded px-4 py-2"
                    id="modal-cancel-btn">{{ __('manage.button.cancel') }}</button>
                <button type="submit"
                    class="bg-blue-500 text-white rounded px-4 py-2">{{ __('manage.button.save') }}</button>
            </div>
        </form>
        <div class="mt-4 flex justify-between">
            <button id="shutdown-btn"
                class="bg-red-500 text-white px-4 py-2 rounded hidden">{{ __('manage.button.shutdown') }}</button>
            <button id="delete-btn"
                class="bg-red-600 text-white px-4 py-2 rounded hidden">{{ __('manage.button.delete') }}</button>
        </div>
    </div>

    <script>
        function fetchData() {
            $.get('{{ route('manage.kernel.fetchData') }}', function(data) {
                $('#loading-spinner').addClass('hidden');
                if (data.error) {
                    $('#error-message').text(data.error).removeClass('hidden');
                } else {
                    renderAccessCodes(data);
                }
            });
        }

        function renderAccessCodes(data) {
            $('#access-code-list').empty();

            $.each(data, function(access_code, connections) {
                let busyCount = 0;
                let readyCount = 0;

                const accessCodeItem = $(`
<li class="bg-gray-100 dark:bg-gray-800 rounded shadow px-2 py-1 flex items-center">
    <div class="text-sm">
        <span class="font-semibold">${access_code}</span>
        <div class="flex justify-between">
            <span class="text-green-500 font-semibold">{{ __('manage.label.ready') }}: <span class="ready-count">0</span></span>
        </div>
        <div class="flex justify-between">
            <span class="text-red-500 font-semibold">{{ __('manage.label.busy') }}: <span class="busy-count">0</span></span>
        </div>
    </div>
    <ul class="ml-4 space-y-2 flex-1 border-l-2 pl-2 border-green-500 py-2"></ul>
</li>
`);

                const ipList = accessCodeItem.find('ul');

                $.each(connections, function(index, connection) {
                    const [url, status, history_id, user_id] = connection;
                    const ip = url.split('/')[2].split(':')[0];
                    const port = url.split(':')[2].split('/')[0];

                    // Count "BUSY" and "READY" statuses
                    if (status === "BUSY") {
                        busyCount++;
                    } else if (status === "READY") {
                        readyCount++;
                    }

                    const statusClass = status === 'BUSY' ? 'bg-red-500 hover:bg-red-700' :
                        'bg-green-500 hover:bg-green-700';
                    const portButton =
                        `<button class="${statusClass} text-white px-2 py-1 m-1 rounded edit-port-btn transition duration-200" data-endpoint="${url}" data-status="${status}" data-history-id="${history_id}" data-user-id="${user_id}" data-access-code="${access_code}">:${port}${new URL(url).pathname}, ${history_id}, ${user_id}</button>`;

                    let ipItem = ipList.find(`li[data-ip="${ip}"]`);
                    if (ipItem.length === 0) {
                        ipItem = $(`
    <li data-ip="${ip}" class="dark:text-white flex flex-1 border-l-2 border-green-500 pl-2 items-center">
        <div class="text-sm w-[100px]">
            <span class="font-semibold">${ip}</span>
            <div class="flex justify-between">
                <span class="text-green-500 font-semibold">{{ __('manage.label.ready') }}: <span class="ip-ready-count">0</span></span>
            </div>
            <div class="flex justify-between">
                <span class="text-red-500 font-semibold">{{ __('manage.label.busy') }}: <span class="ip-busy-count">0</span></span>
            </div>
        </div>
        <div class="flex flex-1 overflow-hidden flex-wrap"></div>
    </li>
`);
                        ipList.append(ipItem);
                    }
                    ipItem.find('div:last').append(portButton);

                    // Update individual IP ready/busy counts
                    if (status === "BUSY") {
                        ipItem.find('.ip-busy-count').text(parseInt(ipItem.find(
                            '.ip-busy-count').text()) + 1);
                    } else if (status === "READY") {
                        ipItem.find('.ip-ready-count').text(parseInt(ipItem.find(
                            '.ip-ready-count').text()) + 1);
                    }
                });

                // Update ready and busy counts for the access code
                accessCodeItem.find('.ready-count').text(readyCount);
                accessCodeItem.find('.busy-count').text(busyCount);

                $('#access-code-list').append(accessCodeItem);
            });
        }



        $(document).on('click', '.edit-port-btn', function() {
            const access_code = $(this).data("access-code");
            const history_id = $(this).data('history-id');
            const user_id = $(this).data('user-id');
            const endpoint = $(this).data('endpoint');
            const status = $(this).data('status');
            $('#original-access-code').val(access_code);
            $('#original-endpoint').val(endpoint);
            $('#original-status').val(status);
            $('#original-history-id').val(history_id);
            $('#original-user-id').val(user_id);

            $('#modal-access-code').val(access_code)
            $('#modal-endpoint').val(endpoint);
            $('#modal-status').val(status);
            $('#modal-history-id').val(history_id);
            $('#modal-user-id').val(user_id);
            $('#modal-title').text('{{ __('manage.label.edit_executor') }}');
            $('#shutdown-btn').removeClass('hidden');
            $('#delete-btn').removeClass('hidden');
            $('#record-modal').removeClass('hidden');
        });

        $('#modal-cancel-btn').on('click', function() {
            $('#record-modal').addClass('hidden');
        });

        $('#record-form').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route('manage.kernel.updateData') }}',
                data: formData,
                success: function() {
                    fetchData();
                    $('#record-modal').addClass('hidden');
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'An error occurred.';
                    $('#error-message').text(error).removeClass('hidden');
                }
            });
        });

        $('#create-record-btn').on('click', function() {
            $('#modal-title').text('{{ __('manage.label.create_executor') }}');
            $('#shutdown-btn').addClass('hidden');
            $('#delete-btn').addClass('hidden');
            $('#record-modal').removeClass('hidden');
            $('#record-form')[0].reset();
        });

        $('#shutdown-btn').on('click', function() {
            const endpoint = $('#modal-endpoint').val();
            const access_Code = $('#modal-access-code').val();
            const history_id = $('#modal-history-id').val();
            const status = $('#modal-status').val();
            const user_id = $('#modal-user-id').val();
            const url = `{{ route('manage.kernel.shutdown') }}`;
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    _token: '{{ csrf_token() }}',
                    access_code: access_Code,
                    endpoint: endpoint,
                    status: status,
                    history_id: history_id,
                    user_id: user_id,
                },
                success: function() {
                    fetchData();
                    $('#record-modal').addClass('hidden');
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'An error occurred.';
                    $('#error-message').text(error).removeClass('hidden');
                }
            });
        });

        $('#delete-btn').on('click', function() {
            const formData = $('#record-form').serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route('manage.kernel.deleteData') }}',
                data: formData,
                success: function() {
                    fetchData();
                    $('#record-modal').addClass('hidden');
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'An error occurred.';
                    $('#error-message').text(error).removeClass('hidden');
                }
            });
        });
    </script>
</div>

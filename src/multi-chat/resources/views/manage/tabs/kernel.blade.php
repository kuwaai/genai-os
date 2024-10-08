<div class="flex flex-col flex-1 h-full mx-auto overflow-y-auto scrollbar">
    <div id="error-message" class="hidden text-red-500 dark:text-red-400"></div>
    <div id="loading-spinner" class="flex justify-center items-center py-8">
        <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <ul id="access-code-list"></ul>
    <div class="mb-6">
        <button id="create-record-btn"
            class="bg-green-500 text-white px-4 py-2 rounded dark:bg-green-600 hover:bg-green-600 dark:hover:bg-green-700">Create
            New Record</button>
    </div>
</div>

<div id="record-modal" class="fixed inset-0 z-50 hidden bg-gray-800 bg-opacity-75 flex items-center justify-center">
    <div class="bg-white rounded shadow-lg p-4 w-1/3 dark:bg-gray-800 dark:text-white">
        <h2 id="modal-title" class="text-xl font-semibold mb-4">Create New Record</h2>
        <form id="record-form" class="space-y-2">
            <input type="hidden" name="original_access_code" id="modal-access-code">
            <div>
                <label for="modal-ip" class="block">IP:</label>
                <input type="text" name="ip" id="modal-ip"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-port" class="block">Port:</label>
                <input type="text" name="port" id="modal-port"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-status" class="block">Status:</label>
                <select name="status" id="modal-status"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
                    <option value="READY">READY</option>
                    <option value="BUSY">BUSY</option>
                </select>
            </div>
            <div>
                <label for="modal-history-id" class="block">History ID:</label>
                <input type="text" name="history_id" id="modal-history-id" value='-1'
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-user-id" class="block">User ID:</label>
                <input type="text" name="user_id" id="modal-user-id" value='-1'
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div class="flex justify-between">
                <button type="button" class="bg-gray-300 text-black rounded px-4 py-2"
                    id="modal-cancel-btn">Cancel</button>
                <button type="submit" class="bg-blue-500 text-white rounded px-4 py-2">Save</button>
            </div>
        </form>
        <div class="mt-4 flex justify-between">
            <button id="shutdown-btn" class="bg-red-500 text-white px-4 py-2 rounded hidden">Shutdown</button>
            <button id="delete-btn" class="bg-red-600 text-white px-4 py-2 rounded hidden">Delete</button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
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
            <span class="text-green-500 font-semibold">Ready: <span class="ready-count">0</span></span>
        </div>
        <div class="flex justify-between">
            <span class="text-red-500 font-semibold">Busy: <span class="busy-count">0</span></span>
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
                            `<button class="${statusClass} text-white px-2 py-1 m-1 rounded edit-port-btn transition duration-200" data-ip="${ip}" data-port="${port}" data-status="${status}" data-history-id="${history_id}" data-user-id="${user_id}">${port}</button>`;

                        let ipItem = ipList.find(`li[data-ip="${ip}"]`);
                        if (ipItem.length === 0) {
                            ipItem = $(`
    <li data-ip="${ip}" class="dark:text-white flex flex-1 border-l-2 border-green-500 pl-2 items-center">
        <div class="text-sm w-[100px]">
            <span class="font-semibold">${ip}</span>
            <div class="flex justify-between">
                <span class="text-green-500 font-semibold">Ready: <span class="ip-ready-count">0</span></span>
            </div>
            <div class="flex justify-between">
                <span class="text-red-500 font-semibold">Busy: <span class="ip-busy-count">0</span></span>
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


            fetchData();

            $(document).on('click', '.edit-port-btn', function() {
                const ip = $(this).data('ip');
                const history_id = $(this).data('history-id');
                const user_id = $(this).data('user-id');
                const port = $(this).data('port');
                const status = $(this).data('status');
                $('#modal-ip').val(ip);
                $('#modal-port').val(port);
                $('#modal-status').val(status);
                $('#modal-history-id').val(history_id);
                $('#modal-user-id').val(user_id);
                $('#modal-title').text('Edit Executor');
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
                $('#modal-title').text('Create New Record');
                $('#shutdown-btn').addClass('hidden');
                $('#delete-btn').addClass('hidden');
                $('#record-modal').removeClass('hidden');
                $('#record-form')[0].reset();
            });

            $('#shutdown-btn').on('click', function() {
                const ip = $('#modal-ip').val();
                const url = `{{ route('manage.kernel.shutdown') }}`;
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        url: ip
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

            setInterval(fetchData, 60000);
        });
    </script>
</div>

<div class="flex flex-1 h-full mx-auto flex-col p-2 bg-gray-600 overflow-y-auto">
    <div id="error-message" class="hidden text-red-500 dark:text-red-400"></div>
    <div id="loading-spinner" class="flex justify-center items-center py-8">
        <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin">
        </div>
    </div>
    <ul id="access-code-list"></ul>
    <div class="mt-3">
        <button id="create-record-btn"
            class="bg-green-500 text-white px-4 py-2 rounded-md transition-all duration-300 ease-in-out hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700">
            {{ __('workers.button.new_executor') }}
        </button>
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
                <label for="modal-access-code" class="block">{{ __('kernel.label.accesscode') }}</label>
                <input type="text" name="access_code" id="modal-access-code"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-endpoint" class="block">{{ __('kernel.label.endpoint') }}</label>
                <input type="text" name="endpoint" id="modal-endpoint"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-status" class="block">{{ __('kernel.label.status') }}</label>
                <select name="status" id="modal-status"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
                    <option value="READY">{{ __('kernel.label.ready') }}</option>
                    <option value="BUSY">{{ __('kernel.label.busy') }}</option>
                </select>
            </div>
            <div>
                <label for="modal-history-id" class="block">{{ __('kernel.label.historyid') }}</label>
                <input type="text" name="history_id" id="modal-history-id" value='-1'
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-user-id" class="block">{{ __('kernel.label.userid') }}</label>
                <input type="text" name="user_id" id="modal-user-id" value='-1'
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div class="flex justify-between">
                <button type="button" class="bg-gray-300 text-black rounded px-4 py-2"
                    id="modal-cancel-btn">{{ __('kernel.button.cancel') }}</button>
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
        function fetchStorageData() {
            $.getJSON('{{ route('manage.kernel.storage') }}', function(data) {
                $('#storage-list').empty(); // Clear previous data
                if (data.models && data.models.length > 0) {
                    data.models.forEach(function(model) {
                        $('#storage-list').append(`
                    <li class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow">
                        ${model}
                    </li>
                `);
                    });
                } else {
                    $('#storage-list').append(
                        '<li class="text-gray-500 dark:text-gray-400">No models available</li>'
                    );
                }
            }).fail(function() {
                $('#storage-list').append(
                    '<li class="text-red-500 dark:text-red-400">Error fetching storage data</li>'
                );
            });
        }

        function fetchJobsData() {
            $.getJSON('{{ route('manage.kernel.storage.jobs') }}', function(data) {
                $('#jobs-list').empty(); // Clear previous data
                if (data.active_jobs && data.active_jobs.length > 0) {
                    data.active_jobs.forEach(function(job) {
                        // Calculate how long the job has been running
                        const startTime = new Date(job.start_time);
                        const now = new Date();
                        const duration = Math.floor((now - startTime) / 1000); // Duration in seconds
                        const minutes = Math.floor(duration / 60);
                        const seconds = duration % 60;

                        // Generate a random ID for the job
                        const randomId = 'job-' + Math.random().toString(36).substr(2,
                            9); // Unique random ID

                        $('#jobs-list').append(`
                    <li class="flex justify-between items-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow" id="${randomId}">
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">${job.model_name}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Running for: ${minutes}m ${seconds}s</div>
                        </div>
                        <button onclick="abortJob('${job.model_name}', this, '${randomId}')" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                            Abort
                        </button>
                    </li>
                `);
                    });
                } else {
                    $('#jobs-list').append('<li class="text-gray-500 dark:text-gray-400">No active jobs</li>');
                }
            }).fail(function() {
                $('#jobs-list').append('<li class="text-red-500 dark:text-red-400">Error fetching jobs data</li>');
            });
        }

        function abortJob(jobName, button, jobId) {
            // Prevent multiple clicks by disabling the button
            $(button).prop('disabled', true).addClass('opacity-50'); // Change opacity to indicate disabled state
            $(button).html('<i class="fa fa-spinner fa-spin"></i> Aborting...'); // Show spinner icon

            $.ajax({
                url: '{{ route('manage.kernel.storage.abort') }}', // POST route for aborting jobs
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    _token: '{{ csrf_token() }}',
                    model_name: jobName
                }), // Send model_name in the request body
                success: function(response) {
                    // Display the success message for 500 ms
                    showAlert(response.message);
                    // Remove the job entry from the UI using the random ID
                    $(`#${jobId}`).remove();
                },
                error: function() {
                    showAlert('Error aborting job'); // Show error message
                },
                complete: function() {
                    $(button).prop('disabled', true); // Keep the button disabled
                }
            });
        }

        function showAlert(message) {
            // Create alert element
            const alert = $(`
                    <div class="flex items-center w-full p-4 mb-2 text-green-800 border-t-4 border-green-300 bg-green-50 dark:text-green-400 dark:bg-gray-800 dark:border-green-800"
                        role="alert">
                        <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                        </svg>
                        <div class="ml-3 text-sm font-medium">${message}</div>
                    </div>
                `);

            // Append alert to the alert container
            $('#alert-container').append(alert);

            setTimeout(() => {
                alert.remove();
            }, 3000);
        }

        function fetchData() {
            $.get('{{ route('manage.kernel.record.fetchData') }}', function(data) {
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
<li class="px-2 py-1 flex items-center">
    <div class="text-sm">
        <span class="font-semibold">${access_code}</span>
        <div class="flex justify-between">
            <span class="text-green-500 font-semibold">{{ __('kernel.label.ready') }}: <span class="ready-count">0</span></span>
        </div>
        <div class="flex justify-between">
            <span class="text-red-500 font-semibold">{{ __('kernel.label.busy') }}: <span class="busy-count">0</span></span>
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
        <div class="text-sm w-[120px]">
            <span class="font-semibold">${ip}</span>
            <div class="flex justify-between">
                <span class="text-green-500 font-semibold">{{ __('kernel.label.ready') }}: <span class="ip-ready-count">0</span></span>
            </div>
            <div class="flex justify-between">
                <span class="text-red-500 font-semibold">{{ __('kernel.label.busy') }}: <span class="ip-busy-count">0</span></span>
            </div>
        </div>
        <div class="flex flex-1 overflow-hidden flex-wrap"></div>
    </li>
`);
                        ipList.append(ipItem);
                    }
                    ipItem.find('div:last').append(portButton);

                    if (status === "BUSY") {
                        ipItem.find('.ip-busy-count').text(parseInt(ipItem.find(
                            '.ip-busy-count').text()) + 1);
                    } else if (status === "READY") {
                        ipItem.find('.ip-ready-count').text(parseInt(ipItem.find(
                            '.ip-ready-count').text()) + 1);
                    }
                });

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
            $('#modal-title').text('{{ __('kernel.label.edit_executor') }}');
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
                url: '{{ route('manage.kernel.record.updateData') }}',
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
            $('#modal-title').text('{{ __('kernel.label.create_executor') }}');
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
            const url = `{{ route('manage.kernel.record.shutdown') }}`;
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
                url: '{{ route('manage.kernel.record.deleteData') }}',
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
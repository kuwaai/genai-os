<div class="flex flex-1 h-full mx-auto flex-col p-2 overflow-y-auto">
    <div id="error-message" class="hidden text-red-500 dark:text-red-400"></div>
    <div id="loading-spinner" class="flex justify-center items-center py-8">
        <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin">
        </div>
    </div>
    <ul id="access-code-list"></ul>
    <div class="mt-3">
        <button id="create-record-btn"
            class="bg-green-500 text-white px-4 py-2 rounded-md transition-all duration-300 ease-in-out hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700">
            {{ __('executors.button.new_executor') }}
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
                <label for="modal-access-code" class="block">{{ __('executors.label.accesscode') }}</label>
                <input type="text" name="access_code" id="modal-access-code"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-endpoint" class="block">{{ __('executors.label.endpoint') }}</label>
                <input type="text" name="endpoint" id="modal-endpoint"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-status" class="block">{{ __('executors.label.status') }}</label>
                <select name="status" id="modal-status"
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
                    <option value="READY">{{ __('executors.label.ready') }}</option>
                    <option value="BUSY">{{ __('executors.label.busy') }}</option>
                </select>
            </div>
            <div>
                <label for="modal-history-id" class="block">{{ __('executors.label.historyid') }}</label>
                <input type="text" name="history_id" id="modal-history-id" value='-1'
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div>
                <label for="modal-user-id" class="block">{{ __('executors.label.userid') }}</label>
                <input type="text" name="user_id" id="modal-user-id" value='-1'
                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-full px-3 py-2"
                    required>
            </div>
            <div class="flex justify-between">
                <button type="button" class="bg-gray-300 text-black rounded px-4 py-2"
                    id="modal-cancel-btn">{{ __('executors.button.cancel') }}</button>
                <button type="submit"
                    class="bg-blue-500 text-white rounded px-4 py-2">{{ __('executors.button.save') }}</button>
            </div>
        </form>
        <div class="mt-4 flex justify-between">
            <button id="shutdown-btn"
                class="bg-red-500 text-white px-4 py-2 rounded hidden">{{ __('executors.button.shutdown') }}</button>
            <button id="delete-btn"
                class="bg-red-600 text-white px-4 py-2 rounded hidden">{{ __('executors.button.delete') }}</button>
        </div>
    </div>

    <script>
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
                let busyCount = 0,
                    readyCount = 0;

                // Create the access code item with counts
                const accessCodeItem = $('<li>', {
                    class: 'px-2 py-1 flex items-start'
                }).append(
                    $('<div>', {
                        class: 'text-sm w-[100px] overflow-hidden',
                        style: 'word-wrap:break-word'
                    }).append(
                        $('<span>', {
                            class: 'font-semibold line-clamp-4',
                            text: access_code
                        }),
                        createCountDisplay('{{ __('executors.label.ready') }}', 'green', 'ready-count', 0),
                        createCountDisplay('{{ __('executors.label.busy') }}', 'red', 'busy-count', 0)
                    ),
                    $('<ul>', {
                        class: 'ml-4 space-y-2 flex-1 border-l-2 pl-2 border-green-500 py-2'
                    })
                );

                const ipList = accessCodeItem.find('ul');

                $.each(connections, function(_, [url, status, history_id, user_id]) {
                    const [ip, port] = extractIPAndPort(url);
                    const statusClass = status === 'BUSY' ? 'bg-red-500 hover:bg-red-700' :
                        'bg-green-500 hover:bg-green-700';
                    const countClass = status === 'BUSY' ? '.ip-busy-count' : '.ip-ready-count';
                    const ipItem = ipList.find(`li[data-ip="${ip}"]`);

                    // Increment counts
                    status === 'BUSY' ? busyCount++ : readyCount++;

                    // Create IP item if it doesn’t exist
                    if (!ipItem.length) {
                        ipList.append(
                            $('<li>', {
                                'data-ip': ip,
                                class: 'dark:text-white flex flex-1 border-l-2 border-green-500 pl-2 items-center'
                            }).append(
                                $('<div>', {
                                    class: 'text-sm w-[120px]'
                                }).append(
                                    $('<span>', {
                                        class: 'font-semibold',
                                        text: ip
                                    }),
                                    createCountDisplay('{{ __('executors.label.ready') }}', 'green', 'ip-ready-count', 0),
                                    createCountDisplay('{{ __('executors.label.busy') }}', 'red', 'ip-busy-count', 0)
                                ),
                                $('<div>', {
                                    class: 'flex flex-1 overflow-hidden flex-wrap'
                                })
                            )
                        );
                    }

                    // Append the port button to the IP item and update counts
                    ipList.find(`li[data-ip="${ip}"] div:last`).append(
                        $('<button>', {
                            class: `${statusClass} text-white px-2 py-1 m-1 rounded edit-port-btn transition duration-200`,
                            'data-endpoint': url,
                            'data-status': status,
                            'data-history-id': history_id,
                            'data-user-id': user_id,
                            'data-access-code': access_code,
                            text: `:${port}${new URL(url).pathname}`
                        })
                    ).closest('li').find(countClass).text((i, val) => +val + 1);
                });

                // Update access code item counts
                accessCodeItem.find('.ready-count').text(readyCount);
                accessCodeItem.find('.busy-count').text(busyCount);

                $('#access-code-list').append(accessCodeItem);
            });

            function createCountDisplay(label, color, countClass, initialCount) {
                return $('<div>', {
                    class: 'flex justify-between'
                }).append(
                    $('<span>', {
                        class: `text-${color}-500 font-semibold`,
                        text: `${label}: `
                    })
                    .append($('<span>', {
                        class: countClass,
                        text: initialCount
                    }))
                );
            }

            function extractIPAndPort(url) {
                const ip = url.split('/')[2].split(':')[0];
                const port = url.split(':')[2].split('/')[0];
                return [ip, port];
            }
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
            $('#modal-title').text('{{ __('executors.label.edit_executor') }}');
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
            $('#modal-title').text('{{ __('executors.label.create_executor') }}');
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

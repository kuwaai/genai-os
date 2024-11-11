<div class="flex flex-col flex-1 h-full mx-auto overflow-y-auto scrollbar">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full">
        <div id="worker_result" class="mb-2 text-center rounded-lg"></div>
        <div class="flex flex-col justify-between mb-4 space-y-2">
            <button id="start-workers-button"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded shadow-md dark:bg-blue-700 dark:hover:bg-blue-800">
                <i id="start-icon" class="fa fa-play" aria-hidden="true"></i> {{ __('workers.button.start') }}
            </button>
            <button id="stop-workers-button"
                class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded shadow-md dark:bg-red-700 dark:hover:bg-red-800">
                <i id="stop-icon" class="fa fa-stop" aria-hidden="true"></i> {{ __('workers.button.stop') }}
            </button>
        </div>
        <div id="worker-count-display" class="text-lg text-gray-700 dark:text-gray-300 mt-4 text-center">
            <span id="worker-count">{{ __('workers.label.loading') }}</span>
            <div id="last-refresh-time" class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                {{ __('workers.label.loading') }}</div>
        </div>
    </div>
</div>

<div id="stop-workers-modal" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-bold mb-4">{{ __('workers.modal.stop.title') }}</h2>
        <p class="text-gray-700 dark:text-gray-300">{{ __('workers.modal.stop.confirm') }}</p>
        <div class="flex justify-end mt-4">
            <button id="confirm-stop-workers"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded shadow-md">{{ __('workers.button.confirm') }}</button>
            <button id="cancel-stop-workers"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow-md ml-2">{{ __('workers.button.cancel') }}</button>
        </div>
    </div>
</div>

<script>
    let lastFetchTime = null;

    function fetchWorkerCount() {
        $.ajax({
            url: '{{ route('manage.workers.get') }}',
            method: 'GET',
            success: function(response) {
                $('#worker-count').text('{{ __('workers.label.current_worker_count') }}: ' +
                    response.worker_count).data('current', response.worker_count);
                lastFetchTime = Date.now();
                updateLastRefreshTime();
            },
            error: function() {
                $('#worker-count').text(
                    '{{ __('workers.label.error_fetching_worker_count') }}');
            }
        });
    }

    function updateLastRefreshTime() {
        if (lastFetchTime) {
            $('#last-refresh-time').text('{{ __('workers.label.last_refresh_time') }}: ' + Math.floor((Date
                .now() - lastFetchTime) / 1000) + ' {{ __('workers.label.seconds_ago') }}');
        }
    }

    $('#start-workers-button').click(() => $('#start-workers-modal').removeClass('hidden'));
    $('#stop-workers-button').click(() => $('#stop-workers-modal').removeClass('hidden'));

    $('#confirm-stop-workers').click(function() {
        if (!canSubmit) return;
        $.ajax({
            url: '{{ route('manage.workers.stop') }}',
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#stop-workers-button').addClass('opacity-50 cursor-not-allowed')
                    .prop('disabled', true);
                $('#stop-icon').removeClass('fa-stop').addClass('fa-spinner fa-spin');
                $('#stop-workers-modal').addClass('hidden');
                canSubmit = false;
            },
            success: response => appendMessage(response.message, true, '#worker_result'),
            error: xhr => appendMessage('{{ __('workers.label.error') }}: ' + xhr
                .responseText, false, '#worker_result'),
            complete: function() {
                $('#stop-workers-button').removeClass('opacity-50 cursor-not-allowed')
                    .prop('disabled', false);
                $('#stop-icon').removeClass('fa-spinner fa-spin').addClass('fa-stop');
                setTimeout(() => canSubmit = true, cooldownDuration);
            }
        });
    });
</script>

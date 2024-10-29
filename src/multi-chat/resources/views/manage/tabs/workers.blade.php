<div class="flex flex-col flex-1 h-full mx-auto overflow-y-auto scrollbar">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full">
        <div id="result-message" class="mb-2 text-center rounded-lg"></div>
        <div class="flex flex-col justify-between mb-4 space-y-2">
            <button id="start-workers-button"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded shadow-md dark:bg-blue-700 dark:hover:bg-blue-800">
                <i id="start-icon" class="fa fa-play" aria-hidden="true"></i> {{ __('kernel.button.start') }}
            </button>
            <button id="stop-workers-button"
                class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded shadow-md dark:bg-red-700 dark:hover:bg-red-800">
                <i id="stop-icon" class="fa fa-stop" aria-hidden="true"></i> {{ __('kernel.button.stop') }}
            </button>
        </div>
        <div id="worker-count-display" class="text-lg text-gray-700 dark:text-gray-300 mt-4 text-center">
            <span id="worker-count">{{ __('kernel.label.loading') }}</span>
            <div id="last-refresh-time" class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                {{ __('kernel.label.last_refresh', ['time' => '0 seconds']) }}</div>
        </div>
    </div>
</div>

<div id="start-workers-modal" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-bold mb-4">{{ __('kernel.modal.start.title') }}</h2>
        <label for="modal-worker-count-input"
            class="block text-gray-700 dark:text-gray-300 mb-2">{{ __('kernel.modal.start.label') }}</label>
        <input id="modal-worker-count-input" type="number" min="1" value='10'
            class="w-full px-4 py-2 border dark:border-gray-700 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300" />
        <div class="flex justify-end mt-4">
            <button id="confirm-start-workers"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded shadow-md">{{ __('kernel.button.confirm') }}</button>
            <button id="cancel-start-workers"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow-md ml-2">{{ __('manage.button.cancel') }}</button>
        </div>
    </div>
</div>

<div id="stop-workers-modal" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-bold mb-4">{{ __('kernel.modal.stop.title') }}</h2>
        <p class="text-gray-700 dark:text-gray-300">{{ __('kernel.modal.stop.confirm') }}</p>
        <div class="flex justify-end mt-4">
            <button id="confirm-stop-workers"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded shadow-md">{{ __('kernel.button.confirm') }}</button>
            <button id="cancel-stop-workers"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow-md ml-2">{{ __('manage.button.cancel') }}</button>
        </div>
    </div>
</div>

<script>
    const cooldownDuration = 2000;
    let canSubmit = true,
        lastFetchTime = null;

    function fetchWorkerCount() {
        $.ajax({
            url: '{{ route('manage.workers.get') }}',
            method: 'GET',
            success: function(response) {
                $('#worker-count').text('{{ __('kernel.label.current_worker_count') }}: ' +
                    response.worker_count).data('current', response.worker_count);
                lastFetchTime = Date.now();
                updateLastRefreshTime();
            },
            error: function() {
                $('#worker-count').text(
                    '{{ __('kernel.label.error_fetching_worker_count') }}');
            }
        });
    }

    function updateLastRefreshTime() {
        if (lastFetchTime) {
            $('#last-refresh-time').text('{{ __('kernel.label.last_refresh_time') }}: ' + Math.floor((Date
                .now() - lastFetchTime) / 1000) + ' {{ __('kernel.label.seconds_ago') }}');
        }
    }

    function appendMessage(message, isSuccess) {
        const messageDiv = $('<div></div>').text(message).addClass(
            'mb-2 text-center p-2 rounded-lg border-2').css({
            'background-color': isSuccess ? '#d4edda' : '#f8d7da',
            'color': isSuccess ? '#155724' : '#721c24',
            'border-color': isSuccess ? '#c3e6cb' : '#f5c6cb'
        }).prependTo('#result-message').hide().fadeIn();
        setTimeout(() => messageDiv.fadeOut(400, () => messageDiv.remove()), 5000);
    }

    $('#start-workers-button').click(() => $('#start-workers-modal').removeClass('hidden'));
    $('#stop-workers-button').click(() => $('#stop-workers-modal').removeClass('hidden'));
    $('#cancel-start-workers, #cancel-stop-workers').click(() => $(
        '#start-workers-modal, #stop-workers-modal').addClass('hidden'));

    $('#confirm-start-workers').click(function() {
        if (!canSubmit) return;
        const count = parseInt($('#modal-worker-count-input').val()),
            currentCount = $('#worker-count').data('current') || 0;
        if (count > 0) {
            $.ajax({
                url: '{{ route('manage.workers.start') }}',
                method: 'POST',
                data: {
                    '_token': '{{ csrf_token() }}',
                    count
                },
                beforeSend: function() {
                    $('#start-workers-button').addClass('opacity-50 cursor-not-allowed')
                        .prop('disabled', true);
                    $('#start-icon').removeClass('fa-play').addClass(
                        'fa-spinner fa-spin');
                    $('#start-workers-modal').addClass('hidden');
                    canSubmit = false;
                },
                success: response => appendMessage(response.message, true),
                error: xhr => appendMessage('{{ __('kernel.label.error') }}: ' + xhr
                    .responseText, false),
                complete: function() {
                    $('#start-workers-button').removeClass(
                        'opacity-50 cursor-not-allowed').prop('disabled', false);
                    $('#start-icon').removeClass('fa-spinner fa-spin').addClass(
                        'fa-play');
                    setTimeout(() => canSubmit = true, cooldownDuration);
                }
            });
        } else appendMessage('{{ __('kernel.label.valid_worker_count') }}', false);
    });

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
            success: response => appendMessage(response.message, true),
            error: xhr => appendMessage('{{ __('kernel.label.error') }}: ' + xhr
                .responseText, false),
            complete: function() {
                $('#stop-workers-button').removeClass('opacity-50 cursor-not-allowed')
                    .prop('disabled', false);
                $('#stop-icon').removeClass('fa-spinner fa-spin').addClass('fa-stop');
                setTimeout(() => canSubmit = true, cooldownDuration);
            }
        });
    });
</script>

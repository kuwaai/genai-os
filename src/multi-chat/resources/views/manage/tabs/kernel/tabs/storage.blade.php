<div id="alert-container"></div>

<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Storage Models</h2>
    <ul id="storage-list"
        class="list-disc pl-5 space-y-2 max-h-32 overflow-y-auto text-gray-900 dark:text-gray-200 bg-gray-100 dark:bg-gray-600 rounded-md p-3 shadow-md">
        <!-- Storage models will be populated here -->
    </ul>
</div>

<div>
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Active Jobs</h2>
    <ul id="jobs-list"
        class="list-disc pl-5 space-y-2 max-h-32 overflow-y-auto text-gray-900 dark:text-gray-200 bg-gray-100 dark:bg-gray-600 rounded-md p-3 shadow-md">
        <!-- Jobs will be populated here -->
    </ul>
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
</script>

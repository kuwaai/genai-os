<div class="flex flex-1">
    <!-- Navigator Sidebar -->
    <div class="bg-gray-100 dark:bg-gray-700 p-4 overflow-y-auto">
        <ul data-tabs-toggle="#Contents" role="tablist" class="space-y-2">
            <li role="presentation">
                <button id="record-tab" data-tabs-target="#record" type="button" role="tab" aria-controls="record"
                    aria-selected="true"
                    class="block w-full p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('Records') }}
                </button>
            </li>
            <li role="presentation">
                <button id="huggingface-tab" data-tabs-target="#huggingface" type="button" role="tab"
                    aria-selected="false" aria-controls="huggingface"
                    class="block w-full p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('Huggingface') }}
                </button>
            </li>
            <li role="presentation">
                <button id="storage-tab" data-tabs-target="#storage" type="button" role="tab" aria-selected="false"
                    aria-controls="storage"
                    class="block w-full p-4 text-gray-900 dark:text-gray-200 rounded-md transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('Storage') }}
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Contents -->
    <div id="Contents" class="flex flex-1 overflow-hidden">
        <!-- Record Tab Panel -->
        <div class="flex-1 bg-gray-50 dark:bg-gray-800 overflow-hidden" id="record" role="tabpanel"
            aria-labelledby="record-tab">
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
                        {{ __('kernel.button.new_executor') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Huggingface Tab Panel -->
        <div class="flex-1 bg-gray-50 dark:bg-gray-800 overflow-hidden" id="huggingface" role="tabpanel"
            aria-labelledby="huggingface-tab">
            <div class="h-full p-6 bg-gray-800 rounded-lg overflow-hidden">
                <!-- User Authentication Section -->
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md h-full flex flex-col">
                    <div class="flex flex-col space-y-4" id='hf_status'>
                        <div class="hidden">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white" id="userGreeting">Guest
                                </h2>
                                <button id="logoutButton"
                                    class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded shadow-md dark:bg-red-700 dark:hover:bg-red-800 flex items-center"
                                    onclick="handleLogoutButtonClick()">
                                    <i id="stop-icon-logout" class="fa fa-spinner fa-spin hidden text-lg mr-2"
                                        aria-hidden="true"></i>
                                    <span id="logoutText">Logout</span>
                                </button>
                            </div>

                            <div id="orgBadges" class="flex space-x-2">
                                <span class="badge bg-blue-200 text-blue-800 rounded-full px-3 py-1">Org 1</span>
                                <span class="badge bg-green-200 text-green-800 rounded-full px-3 py-1">Org 2</span>
                                <span class="badge bg-yellow-200 text-yellow-800 rounded-full px-3 py-1">Org 3</span>
                            </div>
                        </div>

                        <div class="flex items-center w-full">
                            <input type="text" id="token" placeholder="Enter your token"
                                class="border border-gray-300 rounded-l-md p-3 w-full text-gray-900 dark:text-gray-100 dark:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 h-12" />
                            <button id="loginButton"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 rounded-r-md shadow-md dark:bg-blue-700 dark:hover:bg-blue-800 flex items-center h-12"
                                onclick="handleLoginButtonClick()">
                                <i id="stop-icon-login" class="fa fa-spinner fa-spin hidden text-lg mr-2"
                                    aria-hidden="true"></i>
                                <span id="loginText">Login</span>
                            </button>
                        </div>
                    </div>


                    <script>
                        checkLoginStatus(formatUsername);

                        function formatUsername(data) {
                            if (!data.logged_in) return;

                            const {
                                username
                            } = data;
                            const [name, orgsString] = username.split('[1morgs: [0m');
                            const orgs = (orgsString || '').trim().split(',').filter(Boolean);

                            $('#hf_status > div').toggleClass('hidden');
                            $('#userGreeting').text(name.trim());
                            $('#orgBadges').empty().append(
                                orgs.map(org =>
                                    `<span class="badge bg-blue-200 text-blue-800 rounded-full px-3 py-1">${org.trim()}</span>`).join(
                                    '')
                            );
                        }

                        function performRequest(url, data = {}, onSuccess, onError) {
                            $.ajax({
                                type: 'POST',
                                url: url,
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    ...data
                                },
                                success: onSuccess,
                                error: onError
                            });
                        }

                        function checkLoginStatus(onSuccess, onError) {
                            const url = "{{ route('manage.kernel.storage.hf_login') }}";
                            performRequest(url, {}, onSuccess, onError);
                        }

                        function handleLogin(userToken, onSuccess, onError) {
                            const url = "{{ route('manage.kernel.storage.hf_login') }}";
                            performRequest(url, {
                                token: userToken
                            }, onSuccess, onError);
                        }

                        function handleLogout(onSuccess, onError) {
                            const url = "{{ route('manage.kernel.storage.hf_logout') }}";
                            performRequest(url, {}, onSuccess, onError);
                        }

                        function toggleButtonState(button, isLoading) {
                            button.prop('disabled', isLoading).toggleClass('cursor-not-allowed opacity-50', isLoading);
                            button.find('#stop-icon-logout, #stop-icon-login').toggleClass('hidden', !isLoading);
                            button.find('#logoutText, #loginText').toggleClass('opacity-50', isLoading);
                        }

                        function handleLogoutButtonClick() {
                            const button = $('#logoutButton');
                            toggleButtonState(button, true);

                            handleLogout(() => {
                                toggleButtonState(button, false);
                                $('#hf_status > div').toggleClass('hidden');
                            }, (error) => {
                                console.error(error);
                                toggleButtonState(button, false);
                            });
                        }

                        function handleLoginButtonClick() {
                            const button = $('#loginButton');
                            toggleButtonState(button, true);

                            handleLogin($('#token').val(), (data) => {
                                if (data.logged_in) {
                                    checkLoginStatus((userData) => {
                                        toggleButtonState(button, false);
                                        formatUsername(userData);
                                    });
                                } else {
                                    toggleButtonState(button, false);
                                }
                            }, (error) => {
                                console.error(error);
                                toggleButtonState(button, false);
                            });
                        }
                    </script>
                    <!-- Search Section -->
                    <div class="flex-grow mt-4">
                        <div class="flex items-center w-full">
                            <input id="searchInput" type="text" placeholder="Search for models..."
                                class="border border-gray-300 rounded-l-md p-3 w-full text-gray-900 dark:text-gray-100 dark:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 h-12" />
                            <button id="searchButton"
                                class="bg-blue-600 text-white py-2 px-4 rounded-r-md transition duration-200 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 h-12 flex items-center">
                                Search
                            </button>
                        </div>
                    </div>
                    <div id="message" class="mt-4 text-gray-300 hidden"></div>
        
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 mt-6 overflow-y-auto scrollbar overflow-x-hidden h-full" id="results"></div>
                </div>
            </div>

            <!-- Loader Element -->
            <div id="loader" class="hidden mt-4" role="status">
                <svg aria-hidden="true"
                    class="inline w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-green-500"
                    viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                        fill="currentColor" />
                    <path
                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                        fill="currentFill" />
                </svg>
                <span class="sr-only">Loading...</span>
            </div>

            <script>
                const apiUrl = "https://huggingface.co/api/models";
                $(document).ready(function() {
                    $('#searchButton').click(() => {
                        const searchTerm = $('#searchInput').val().trim();
                        if (searchTerm) fetchModels(searchTerm);
                    });
                    $('#searchInput').keypress(e => {
                        if (e.which === 13) $('#searchButton').click();
                    });
                    $('#closeModal').click(() => $('#modelModal').addClass('hidden'));
                    $(window).click(event => {
                        if ($(event.target).is('#modelModal')) $('#modelModal').addClass('hidden');
                    });

                    $('#downloadButton').click(() => {
                        const modelName = $('#modalTitle').text().trim();
                        if ($('#gatedIndicator').is(':visible')) {
                            $('#loginMessage').text(
                                `Access to model ${modelName} is restricted. You must have access to it and be authenticated to access it. Please log in.`
                            );
                            $('#loginModal').removeClass('hidden');
                            return;
                        }
                        const url = "{{ route('manage.kernel.storage.download') }}?model_name=" +
                            encodeURIComponent(modelName);
                        $('#responseText').removeClass('hidden').text('Downloading...');
                        fetch(url)
                            .then(response => {
                                if (!response.ok) throw new Error(
                                    `Error: ${response.status} - ${response.statusText}`);
                                const reader = response.body.getReader(),
                                    decoder = new TextDecoder("utf-8");
                                (function push() {
                                    return reader.read().then(({
                                        done,
                                        value
                                    }) => {
                                        if (done) {
                                            $('#responseText').append('<br/>Download complete.');
                                            return;
                                        }
                                        $('#responseText').append(decoder.decode(value, {
                                            stream: true
                                        }));
                                        push();
                                    });
                                })();
                            })
                            .catch(error => {
                                $('#responseText').append('<br/>Error: ' + error.message);
                            });
                    });

                    $('#closeLoginModal').click(() => $('#loginModal').addClass('hidden'));
                });

                function fetchModels(searchTerm) {
                    const params = new URLSearchParams({
                        filter: searchTerm,
                        sort: 'downloads',
                        limit: 10
                    });
                    fetch(`${apiUrl}?${params.toString()}`)
                        .then(response => response.json())
                        .then(data => displayResults(data))
                        .catch(error => console.error('Error fetching models:', error));
                }

                function fetchModelFiles(modelId) {
                    $('#modalFiles').empty();
                    $('#responseText').addClass('hidden');
                    fetch(`${apiUrl}/${modelId}`)
                        .then(response => response.json())
                        .then(data => {
                            $('#modalTitle').text(modelId);
                            $('#gatedIndicator').toggleClass('hidden', !data.gated);
                            data.siblings.forEach(file => {
                                $('#modalFiles').append(
                                    `<li class="flex justify-between items-center text-gray-800 dark:text-gray-200"><span class="font-medium">${file.rfilename}</span></li>`
                                );
                            });
                            $('#modelModal').removeClass('hidden');
                        })
                        .catch(error => console.error('Error fetching model files:', error));
                }

                function displayResults(models) {
                    const resultsGrid = $('#results');
                    resultsGrid.empty();
                    if (models.length === 0) {
                        resultsGrid.append('<div class="col-span-full text-red-500">No models found.</div>');
                        return;
                    }
                    models.forEach(model => {
                        resultsGrid.append(`
                        <div class="border my-2 ml-2 mr-4 rounded-lg shadow-md bg-white dark:bg-gray-800 p-4 transition transform hover:scale-105 relative cursor-pointer" onclick="fetchModelFiles('${model.modelId}')">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100" title="${model.modelId}" style="word-wrap: break-word;">${model.modelId}</h2>
                            <p class="text-gray-700 dark:text-gray-300"><strong>Downloads:</strong> ${model.downloads.toLocaleString()}</p>
                            <p class="text-gray-700 dark:text-gray-300"><strong>Likes:</strong> ${model.likes.toLocaleString()}</p>
                            <button class="view-tags-button bg-blue-500 text-white py-1 px-2 rounded mt-2 hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-600">View Tags</button>
                            <div class="tags hidden mt-2 flex flex-wrap">${model.tags.map(tag => `<span class="bg-blue-100 text-blue-600 text-xs font-medium mr-1 mb-1 px-2.5 py-0.5 rounded-full dark:bg-blue-700 dark:text-blue-300">${tag}</span>`).join('')}</div>
                        </div>
                    `);
                    });
                    $('.view-tags-button').click(function(event) {
                        event.stopPropagation();
                        const tagsDiv = $(this).next('.tags');
                        tagsDiv.toggleClass('hidden');
                        $(this).text(tagsDiv.hasClass('hidden') ? 'View Tags' : 'Hide Tags');
                    });
                }
            </script>
        </div>
        <div id="modelModal"
            class="fixed inset-0 flex items-center justify-center hidden z-50 bg-black bg-opacity-50">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-11/12 sm:w-3/4 lg:w-1/2 max-h-[80vh] overflow-y-auto">
                <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white" id="modalTitle"></h3>
                <div id="gatedIndicator" class="hidden mb-2 text-yellow-600 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 2v4m0 4v8m0 0H8m4 0h4m-2-18a2 2 0 012 2v2a2 2 0 01-2 2H8a2 2 0 01-2-2V4a2 2 0 012-2h8z" />
                    </svg>
                    This model is gated. Please log in to access it.
                </div>
                <h4 class="font-semibold mb-1 text-gray-900 dark:text-white">Files:</h4>
                <ul id="modalFiles" class="list-disc list-inside mb-4 space-y-2 max-h-40 overflow-y-auto scrollbar">
                </ul>
                <button id="downloadButton"
                    class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-500 mb-2">Download
                    Model</button>
                <button id="closeModal"
                    class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-600 mb-4">Close</button>
                <div id="responseText"
                    class="hidden text-gray-800 dark:text-white mt-2 overflow-y-auto max-h-32 scrollbar"></div>
            </div>
        </div>

        <!-- Login Modal for Gated Models -->
        <div id="loginModal"
            class="fixed inset-0 flex items-center justify-center hidden z-50 bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-11/12 sm:w-3/4 lg:w-1/2">
                <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Access Restricted</h3>
                <p class="text-gray-700 dark:text-gray-300" id="loginMessage"></p>
                <button id="closeLoginModal"
                    class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-600 mt-4">
                    Close
                </button>
            </div>
        </div>

        <!-- Storage Tab Panel -->
        <div class="hidden flex-1 bg-gray-50 dark:bg-gray-800 overflow-x-hidden overflow-y-auto scrollbar p-6"
            id="storage" role="tabpanel" aria-labelledby="storage-tab">
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
        </div>

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

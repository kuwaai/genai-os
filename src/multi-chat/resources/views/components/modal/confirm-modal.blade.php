<div id="confirm_modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 flex justify-center items-center hidden z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md p-6">
        <h2 id="modal_title" class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Are you sure?</h2>
        <p id="modal_content" class="mb-6 text-gray-700 dark:text-gray-300">This action cannot be undone.</p>
        <div class="flex justify-end">
            <button id="cancel_btn"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded mr-2 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-200">
                Cancel
            </button>
            <button id="confirm_btn" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded">
                Confirm
            </button>
        </div>
    </div>
</div>


<script>
    function showConfirmationModal(title, content, confirmHandler, cancelHandler, confirmText = 'Confirm', cancelText =
        'Cancel') {
        // Set modal title
        $('#modal_title').text(title);

        // Create content using jQuery
        const $contentContainer = $('<div></div>').html(content); // Create a div for content
        $('#modal_content').empty().append($contentContainer); // Clear and append to modal content

        // Set custom text for confirm and cancel buttons
        $('#confirm_btn').text(confirmText);
        $('#cancel_btn').text(cancelText);

        // Unbind any previous event handlers for the confirm and cancel buttons
        $('#confirm_btn').off('click');
        $('#cancel_btn').off('click');

        // Attach new handler to the confirm button
        $('#confirm_btn').on('click', function() {
            confirmHandler(); // Execute the confirm action
            $('#confirm_modal').addClass('hidden'); // Hide the modal
        });

        // Attach new handler to the cancel button
        $('#cancel_btn').on('click', function() {
            if (cancelHandler) {
                cancelHandler(); // Execute the cancel action if provided
            }
            $('#confirm_modal').addClass('hidden'); // Hide the modal
        });

        // Show the modal
        $('#confirm_modal').removeClass('hidden');
    }
</script>

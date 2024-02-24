@props(['prefix' => 'prefix_'])

<div class="border border-2 border-gray-500 rounded-lg p-2">
    <div class="sm:flex w-full overflow-hidden mb-2">
        <label class="min-w-[100px] text-center m-auto" for="{{ $prefix }}system">System:</label>
        <textarea id="{{ $prefix }}system" name="{{ $prefix }}system" rows='1' max-rows="5"
            oninput="adjustTextareaRows(this)"
            class="px-2 py-1 resize-none scrollbar appearance-none block w-full text-gray-700 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-gray-500"></textarea>
    </div>
    <div class="overflow-hidden space-y-2" id="{{ $prefix }}root">
        <div class="hidden">
            <div class="sm:flex w-full mb-2">
                <label class="min-w-[100px] text-center m-auto" for="{{ $prefix }}user_1">User:</label>
                <textarea id="{{ $prefix }}user_1" name="{{ $prefix }}user[]" rows='1' max-rows="5"
                    oninput="adjustTextareaRows(this);checkAndDuplicate();"
                    class="px-2 py-1 resize-none scrollbar appearance-none block w-full text-gray-700 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-gray-500"></textarea>
            </div>
            <div class="sm:flex w-full">
                <label class="min-w-[100px] text-center m-auto" for="{{ $prefix }}model_1">Model:</label>
                <textarea id="{{ $prefix }}model_1" name="{{ $prefix }}model[]" rows='1' max-rows="5"
                    oninput="adjustTextareaRows(this);checkAndDuplicate();"
                    class="px-2 py-1 resize-none scrollbar appearance-none block w-full text-gray-700 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-gray-500"></textarea>
            </div>
        </div>
        <div>
            <div class="sm:flex w-full mb-2">
                <label class="min-w-[100px] text-center m-auto" for="{{ $prefix }}user_1">User:</label>
                <textarea id="{{ $prefix }}user_1" name="{{ $prefix }}user[]" rows='1' max-rows="5"
                    oninput="adjustTextareaRows(this);checkAndDuplicate();"
                    class="px-2 py-1 resize-none scrollbar appearance-none block w-full text-gray-700 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-gray-500"></textarea>
            </div>
            <div class="sm:flex w-full">
                <label class="min-w-[100px] text-center m-auto" for="{{ $prefix }}model_1">Model:</label>
                <textarea id="{{ $prefix }}model_1" name="{{ $prefix }}model[]" rows='1' max-rows="5"
                    oninput="adjustTextareaRows(this);checkAndDuplicate();"
                    class="px-2 py-1 resize-none scrollbar appearance-none block w-full text-gray-700 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-gray-500"></textarea>
            </div>
        </div>
    </div>
</div>

<script>
    function adjustTextareaRows(obj) {
        obj = $(obj)
        if (obj.length) {
            const textarea = obj;
            const maxRows = parseInt(textarea.attr('max-rows')) || 5;
            const lineHeight = parseInt(textarea.css('line-height'));

            textarea.attr('rows', 1);

            const contentHeight = textarea[0].scrollHeight;
            const rowsToDisplay = Math.floor(contentHeight / lineHeight);

            textarea.attr('rows', Math.min(maxRows, rowsToDisplay));
        }
    }

    function checkAndDuplicate() {
        last = $('#{{ $prefix }}root').children().last();
        let lastUser = last.find("textarea").first();
        let lastModel = last.find("textarea").last();
        if (lastUser.val().trim() !== '' && lastModel.val().trim() !== '') {
            // Get the last pair's index
            let lastIndex = parseInt(lastUser[0].id.split("_").pop())

            // Duplicate a pair of user and model textareas
            let newUserModel = last.clone();

            // Update the IDs and names of the new pair
            let newIndex = lastIndex + 1;
            newUserModel.find('textarea').first().attr({
                id: '{{ $prefix }}user_' + newIndex,
                name: '{{ $prefix }}user[]'
            });
            newUserModel.find('textarea').last().attr({
                id: '{{ $prefix }}model_' + newIndex,
                name: '{{ $prefix }}model[]'
            });
            // Clean the cloned pair's textareas
            newUserModel.find('textarea').val('');

            // Append the cloned pair
            last.parent().append(newUserModel);
        } else {
            // Ensure there is always at least 1 pair of user and model textareas
            let numPairs = $('#{{ $prefix }}root').children();
            if (numPairs.length > 1){
                numPairs.slice(1).each((index, div)=>{
                    top = div.find("textarea");
                    if (top.first().val().trim() == "" || top.last().val().trim() == ""){
                        div.remove();
                    }
                })
            }
        }
    }
</script>

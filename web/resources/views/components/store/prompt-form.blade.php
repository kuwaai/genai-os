@props(['prefix' => 'prefix_'])

<div class="border border-2 border-gray-500 rounded-lg p-2">
    <div class="sm:flex w-full overflow-hidden">
        <label class="min-w-[100px] text-center m-auto" for="{{ $prefix }}system">System:</label>
        <textarea id="{{ $prefix }}system" name="{{ $prefix }}system" rows='1' max-rows="5"
            oninput="adjustTextareaRows(this)"
            class="px-2 py-1 resize-none scrollbar appearance-none block w-full text-gray-700 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-gray-500"></textarea>
    </div>
    <div class="overflow-hidden space-y-2" id="{{ $prefix }}root">
        <div class="sm:flex w-full mb-2">
            <label class="min-w-[100px] text-center m-auto" for="{{ $prefix }}user_1">User:</label>
            <textarea id="{{ $prefix }}user_1" name="{{ $prefix }}user[]" rows='1' max-rows="5"
                oninput="adjustTextareaRows(this);{{ $prefix }}_checkAndDuplicate();"
                class="px-2 py-1 resize-none scrollbar appearance-none block w-full text-gray-700 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-gray-500"></textarea>
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

    function {{ $prefix }}_checkAndDuplicate() {
        let last = $("#{{ $prefix }}root").children().last();
        let lastinput = last.find("textarea")[0]
        if (lastinput.name.indexOf("user") != -1) {
            var newInput = last.clone();

            var index = parseInt($(lastinput).attr("id").match(/\d+/)[0]) + 1;

            $(lastinput).attr("id", "{{ $prefix }}model_" + index);
            $(lastinput).attr("name", "{{ $prefix }}model[]");
            $(lastinput).val("");
            $(lastinput).prev().text("Model:")

            $("#{{ $prefix }}root").append(newInput);

            adjustTextareaRows(newInput.get(0));
        }
    }
</script>

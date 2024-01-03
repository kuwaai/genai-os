@php
    $logs = App\Models\Logs::orderby('created_at', 'desc')->orderby('id', 'desc');

    if (session('start_date')) {
        $logs->where('created_at', '>=', session('start_date'));
    }
    if (session('end_date')) {
        $logs->where('created_at', '<=', session('end_date'));
    }
    if (session('action')) {
        $logs->where('action', 'like', '%' . session('action') . '%');
    }
    if (session('description')) {
        $logs->where('description', 'like', '%' . session('description') . '%');
    }
    if (session('user_id')) {
        $logs->where('user_id', 'like', '%' . session('user_id') . '%');
    }
    if (session('ip_address')) {
        $logs->where('ip_address', 'like', '%' . session('ip_address') . '%');
    }

    $logs = $logs->paginate(10, ['*'], 'page', session('page') ?? 1);
    $systemTime = now()->toIso8601String();
@endphp
<div class="bg-gray-600 w-full overflow-hidden flex flex-col p-2">
    <form id="filterForm" method="GET" action="{{ route('dashboard.home') }}" class="mb-4 flex space-x-2">
        <input class="text-black" type="text" name="tab" value="logs" hidden>

        <div class="grid grid-cols-6 gap-4">
            <div class="flex flex-col">
                <label for="start_date">{{__("dashboard.filter.StartDate")}}</label>
                <input class="text-black" type="datetime-local" id="start_date" name="start_date">
            </div>
            <div class="flex flex-col">
                <label for="end_date">{{__("dashboard.filter.EndDate")}}</label>
                <input class="text-black" type="datetime-local" id="end_date" name="end_date">
            </div>
    
            <div class="flex flex-col">
                <label for="action">{{__("dashboard.filter.Action")}}</label>
                <input class="text-black" type="text" id="action" name="action">
            </div>
            <div class="flex flex-col">
                <label for="description">{{__("dashboard.filter.Description")}}</label>
                <input class="text-black" type="text" id="description" name="description">
            </div>
            <div class="flex flex-col">
                <label for="user_id">{{__("dashboard.filter.UserID")}}</label>
                <input class="text-black" type="text" id="user_id" name="user_id">
            </div>
    
            <div class="flex flex-col">
                <label for="ip_address">{{__("dashboard.filter.IPAddress")}}</label>
                <input class="text-black" type="text" id="ip_address" name="ip_address">
            </div>
        </div>

        <button type="submit"
            class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-700"><i class="fa fa-filter" style="color: #ffffff;"></i></button>
    </form>

    <div class="flex flex-col overflow-y-auto scrollbar">
        @if ($logs->count())
        @foreach ($logs as $log)
            <div class="bg-gray-500 p-2 my-1 rounded-md">
                <p><span class="font-bold">{{ __('dashboard.colName.Action') }}</span> {{ $log->action }}</p>
                <p class="whitespace-pre"><span class="font-bold">{{ __('dashboard.colName.Description') }}</span> {{ $log->description }}</p>
                <p><span class="font-bold">{{ __('dashboard.colName.UserID') }}</span> {{ $log->user_id }}</p>
                <p><span class="font-bold">{{ __('dashboard.colName.IP') }}</span> {{ $log->ip_address }}</p>
                <p><span class="font-bold">{{ __('dashboard.colName.Timestamp') }}</span> <span
                        class="log-timestamp">{{ $log->created_at->format('Y/m/d H:i:s') }}</span></p>
            </div>
        @endforeach
        @else
        <p class="text-center">{{__("dashboard.msg.NoRecord")}}</p>
        @endif
    </div>

    <div class="mt-auto">
        <ul class="pagination">
            {{ $logs->onEachSide(3)->links('components.pagination', ['tab' => 'logs']) }}
        </ul>
    </div>

</div>
<script>
    $(document).ready(function() {
        var systemTime = "{{ $systemTime }}"; // Get the system time from Blade
        var clientTime = new Date();

        function updateTimeAgo() {
            $('.log-timestamp').each(function() {
                var currentTime = new Date(systemTime); // Convert system time to date object
                var timestamp = $(this).text().split("(")[0];
                var date = new Date(timestamp); // Convert log timestamp to date object
                currentTime.setTime((new Date() - clientTime) + currentTime
                    .getTime())
                var diffInSeconds = Math.floor((currentTime - date) / 1000); // Difference in seconds

                var suffix = '';

                if (diffInSeconds < 60) {
                    suffix = 'just now';
                } else if (diffInSeconds < 3600) {
                    var minutes = Math.floor(diffInSeconds / 60);
                    suffix = minutes + ' ' + (minutes === 1 ? 'minute' : 'minutes') + ' ago';
                } else if (diffInSeconds < 86400) {
                    var hours = Math.floor(diffInSeconds / 3600);
                    suffix = hours + ' ' + (hours === 1 ? 'hour' : 'hours') + ' ago';
                } else if (diffInSeconds < 259200) {
                    var days = Math.floor(diffInSeconds / 86400);
                    suffix = days + ' ' + (days === 1 ? 'day' : 'days') + ' ago';
                } else {
                    suffix = date.toLocaleDateString(); // If more than 3 days, show the date
                }

                $(this).text(timestamp + ' (' + suffix + ')');
            });
        }

        updateTimeAgo();

        setInterval(updateTimeAgo, 5000); // 60 seconds = 1 minute
    });
</script>

<x-app-layout>
    <div class="py-2 h-full">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 h-full">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
                <div class="p-6 text-gray-900 dark:text-gray-100 h-full">
                    <section class="flex flex-col h-full">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('AI Election') }}<span id="connect_flag" class="ml-2 text-green-500">{{ __('[Connecting...]') }}</span>
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __("Who'll be the winner with AI!") }}
                            </p>
                        </header>
                        <div class="flex-1" id="home_screen">
                            <div class="m-auto flex h-full">
                                <button
                                    class="my-auto ml-auto mr-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center">Play
                                    with Others</button>
                                <button
                                    class="my-auto mr-auto ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center">Play
                                    with AI</button>
                            </div>
                        </div>
                        <div class="flex-1 hidden" id="Queuing">
                            <div class="m-auto flex h-full">
                                <p>Waiting for players...</p>
                                <button class="my-auto mr-auto ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center">Stop</button>
                            </div>
                        </div>
                        <div class="flex-1 hidden" id="Game">
                            <div class="m-auto flex h-full">
                                <p>Game</p>
                                <button class="my-auto mr-auto ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center">Send</button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

<script>
    const socket = io(`${window.location.protocol}//${window.location.hostname}:3000`);
    socket.on("connect", ()=>{
        console.log("Connected!");
        $("#connect_flag").attr("class", "ml-2 text-green-500")
        $("#connect_flag").text("{{ __('[Connected]') }}")
    })
    socket.on("disconnect", ()=>{
        console.log("Disconnected!");
        $("#connect_flag").attr("class", "ml-2 text-red-500")
        $("#connect_flag").text("{{ __('[Disconnected]') }}")
    })
    socket.on("connect_error", ()=>{
        console.log("Disconnected!");
        $("#connect_flag").attr("class", "ml-2 text-red-500")
        $("#connect_flag").text("{{ __('[Cannot reach Server]') }}")
    })
</script>

</x-app-layout>

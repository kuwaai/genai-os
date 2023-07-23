<x-app-layout>
    <div class="py-2 h-full">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 h-full">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
                <div class="p-6 text-gray-900 dark:text-gray-100 h-full">
                    <section class="flex flex-col h-full">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('AI Election') }}<span id="connect_flag"
                                    class="ml-2 text-green-500">{{ __('[Connecting...]') }}</span>
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __("Who'll be the winner with AI!") }}
                            </p>
                        </header>
                        <div class="flex-1 border-gray-700 rounded border-2" id="Status">
                            <div class="m-auto flex h-full">
                                <p class="m-auto whitespace-pre">Please wait...</p>
                            </div>
                        </div>
                        <div class="flex-1 border-gray-700 rounded-700 rounded border-2 hidden" id="home_screen">
                            <div class="m-auto flex h-full">
                                <button onclick="socket.emit('Action', 'Queue')"
                                    class="my-auto ml-auto mr-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center">Play
                                    with Others</button>
                                <button onclick="socket.emit('Action', 'Play')"
                                    class="my-auto mr-auto ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center">Play
                                    with AI</button>
                            </div>
                        </div>
                        <div class="flex-1 border-gray-700 rounded border-2 hidden" id="Queuing">
                            <div class="m-auto flex h-full flex-col justify-center">
                                <p class="mx-auto mb-2">Waiting for players...</p>
                                <button onclick="socket.emit('Action', 'Lobby')"
                                    class="mx-auto bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center">Abort</button>
                            </div>
                        </div>
                        <div class="flex-1 border-gray-700 rounded-200 border-2 hidden" id="Game">
                            <div class="m-auto flex h-full flex-col">
                                <p class="text-center"><span class="text-red-500">10</span> days left before the
                                    election
                                    begin</p>
                                <span class="text-center mx-2">You can select different LLM and modify the prompt to
                                    affact the
                                    outcome speech</span>
                                <div class="flex-1 flex mb-2">
                                    <div class="flex-1 flex-col"></div>
                                    <div class="flex-1 flex-col flex border-gray-700 rounded-lg border-2">
                                        <p class="text-center">{{ request()->user()->name }}</p>
                                        <button id="llm_select" data-dropdown-toggle="llm_dropdown"
                                            class="flex-shrink-0 z-10 inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-500 bg-gray-100 border border-gray-700 rounded-300 hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-white dark:border-gray-600"
                                            type="button">
                                            <p>Please choose a LLM to use</p>
                                        </button>
                                        <div id="llm_dropdown"
                                            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-inherit dark:bg-gray-700">
                                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                                aria-labelledby="llm_select">
                                                @foreach (App\Models\LLMs::where('enabled', true)->orderby('order')->orderby('created_at')->get() as $LLM)
                                                    <li>
                                                        <button id="{{ 'llm_id_' . $LLM->id }}" type="button"
                                                            onclick='$("#llm_select").html($("#{{ 'llm_id_' . $LLM->id }}").html()); promptValidate();'
                                                            class="inline-flex w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white">
                                                            <img class="w-[20px] mr-2 rounded-full bg-black"
                                                                src="{{ asset(Storage::url($LLM->image)) }}">
                                                            <p target='{{ $LLM->id }}'>{{ $LLM->name }}</p>
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="flex-1 flex flex-col">
                                            <textarea onchange="promptValidate()" oninput="promptValidate()" id="prompt"
                                                class="flex-1 resize-none bg-transparent" placeholder="Prompt here"></textarea>
                                            <button id="last_llm"
                                                class="flex-shrink-0 inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-500 bg-gray-100 border border-gray-700 rounded-300 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:text-white dark:border-gray-600"
                                                type="button" disabled>
                                                <p>No LLM</p>
                                            </button>
                                            <div class="flex-1 flex relative">
                                                <button style="display: none;" onclick="preview()"
                                                    class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg text-center">Preview</button>
                                                <textarea id="outcome" class="resize-none bg-transparent w-full" placeholder="Outcome" readonly></textarea>
                                            </div>
                                        </div>
                                        <div class="mx-auto">
                                            <button style="display: none;" onclick="send()"
                                                class="my-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center">Send</button>
                                        </div>
                                    </div>
                                    <div class="flex-1 flex-col"></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        const socket = io(`${window.location.protocol}//${window.location.hostname}:{{ env('SocketIO_Port', 3000) }}`);
        socket.on("connect", () => {
            $("#connect_flag").attr("class", "ml-2 text-green-500")
            $("#connect_flag").text("{{ __('[Connected]') }}")
            socket.emit("auth",
                "{{ request()->user()->tokens()->where('name', 'API_Token')->first()->token }}"
            )
        })
        socket.on("disconnect", () => {
            $("#connect_flag").attr("class", "ml-2 text-red-500")
            $("#connect_flag").text("{{ __('[Disconnected]') }}")
            change("Status")
            $("#Status p").text("Sorry, You have been disconnected from the server, Please refresh")
        })
        socket.on("connect_error", () => {
            $("#connect_flag").attr("class", "ml-2 text-red-500")
            $("#connect_flag").text("{{ __('[Cannot reach Server]') }}")
            change("Status")
            $("#Status p").text(
                "Cannot reach the server, \nPlease refresh, If this still happening, \nYou can report to the operator of this website."
            )
        })

        socket.on("authed", () => {
            change("home_screen")
            socket.on("change", (data) => {
                if (data == "Lobby") {
                    change("home_screen")
                } else if (data == "Queue") {
                    change("Queuing")
                } else if (data == "Play") {
                    change("Game")
                }
            })

            socket.on("preview_result", (data) => {
                $("#outcome").val(data)
                $("button[onclick='preview()']").hide()
                $("button[onclick='send()']").show()
            })
        })
        var last_prompt = undefined;
        var last_id = undefined;

        function promptValidate() {
            if ($("#llm_select >p").attr("target") == undefined || $("#prompt").val() == "" || ($("#prompt").val() ==
                    last_prompt && $("#llm_select >p").attr("target") == last_id)) {
                $("button[onclick='preview()']").hide()
            } else {
                $("button[onclick='preview()']").show()
            }
        }

        function preview() {
            last_prompt = $("#prompt").val()
            last_id = $("#llm_select >p").attr("target")
            $("#last_llm").html($("#llm_id_" + last_id).html());
            socket.emit("preview", {
                "prompt": $("#prompt").val(),
                "llm_id": $("#llm_select >p").attr("target")
            })
        }

        function send() {
            socket.emit("send")
        }

        function change(id) {
            $("section.flex.flex-col.h-full >div.flex-1").addClass("hidden");
            $(`#${id}`).removeClass("hidden");
        }

        function PlayWithOther() {
            change("Queuing")
            socket.emit()
        }
    </script>

</x-app-layout>

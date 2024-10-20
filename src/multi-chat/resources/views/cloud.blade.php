<x-app-layout>
    @include('components.modal.confirm-modal')

    <script>
        function formatSize(bytes) {
            const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            let size = bytes;
            let unitIndex = 0;

            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }

            return size.toFixed(2) + " " + units[unitIndex];
        }

        function generatePathHtml(parent, path) {
            parent.empty();
            const parts = path.split('/').filter(Boolean);
            const Perm = {{ Auth::user()->hasPerm('tab_Manage') ? 'true' : 'false' }};
            const classes = Perm ? "text-blue-500 hover:underline cursor-pointer pr-2" : 'pr-2';
            const userId = {{ Auth::user()->id }};
            const $ul = $('<ul class="flex cloud-path"></ul>').append(
                `<li><span class="${classes}">/</span></li>`
            );

            let currentPath = '';

            parts.forEach((part, index) => {
                const adjustedPart = (parts[0] === 'homes' && index === 1 && part === String(userId)) ?
                    '{{ Auth::user()->name }}' :
                    part;

                currentPath += `/${adjustedPart}/`;
                const partPath = currentPath;
                const $partLi = $(`<li><span class="${classes}">${adjustedPart}/</span></li>`);
                if (Perm) $partLi.on('click', () => updatePath(partPath));
                $ul.append($partLi);
            });

            if (Perm) $ul.find('li:first').on('click', () => updatePath(''));
            parent.append($ul);
        }

        function updatePath(path) {
            client.listCloud(path)
                .then(response => populateFileList(response))
                .catch(console.error);
        }
        const categoryToIcon = {
            image: 'file-image',
            pdf: 'file-pdf',
            word: 'file-word',
            excel: 'file-excel',
            powerpoint: 'file-powerpoint',
            archive: 'file-archive',
            folder: 'folder',
            file: 'file-alt'
        };

        function populateFileList(data) {
            const fileList = $('#file-list');
            fileList.empty();
            data.result.explorer.forEach(item => {
                const div = $(`<div class="hover:bg-gray-300 w-[100px] max-w-[100px] min-w-[100px] md:w-[150px] md:max-w-[150px] md:min-w-[150px] m-1 dark:hover:bg-gray-700 text-center overflow-hidden flex flex-col justify-center border border-1 rounded-lg cursor-pointer border-gray-500 p-2" 
    title="${item.name}" data-isdir="${item.is_directory}"
    data-url="${data.result.query_path}${item.name}">
<i class="fas fa-${categoryToIcon[item.icon]} text-4xl mb-2"></i>
<span class="text-gray-500 dark:text-gray-300 text-xs line-clamp-1 max-w-full flex-1" style="word-wrap: break-word;">${item.name}</span>
</div>`);
                fileList.append(div);
            });

            const contextMenu = $('#context-menu');
            let selectedFile = null;

            $(document).off('contextmenu', '#file-list > div');
            $(document).off('click', '#file-list > div');
            $(document).off('touchstart', '#file-list > div');
            $('#open-file').off('click');
            $('#open-file-tab').off('click');
            $('#copy-file-url').off('click');
            $('#rename-file').off('click');
            $('#delete-file').off('click');
            $(document).off('touchend');

            $(document).on('contextmenu', '#file-list > div', function(event) {
                $('#open-file-tab').show()

                event.preventDefault();
                selectedFile = $(this);
                contextMenu.css({
                    display: 'block',
                    left: event.pageX + 'px',
                    top: event.pageY + 'px'
                });
                const isdir = $(this).data('isdir');
                if (isdir) {
                    $('#open-file-tab').hide()
                }
            });

            function cloud_open(obj) {
                const url = obj.data('url');
                const title = obj.prop('title');
                const isdir = obj.data('isdir');
                const publicUrl = `{{ Storage::url('root') }}${url}`;

                if (isdir) {
                    client.listCloud(url)
                        .then(response => populateFileList(response))
                        .catch(error => console.error('Error:', error));
                } else {
                    const extension = url.split('.').pop().toLowerCase();
                    let content;
                    const $contentWrapper = $('<div class="w-full h-full"></div>'); // Create a wrapper for the content

                    if ([
                            'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'bmp', 'tiff', 'tif', 'ico', 'heic', 'img', 'dds'
                        ].includes(extension)) {
                        const $img = $('<img>', {
                            class: 'w-full h-full object-fill',
                            alt: title
                        }).attr('src', publicUrl);
                        $contentWrapper.append($img);
                    } else if ([
                            'mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a', 'wma'
                        ].includes(extension)) {
                        const $audio = $('<audio>', {
                            controls: true,
                            autoplay: true,
                            class: 'w-full'
                        }).append(
                            $('<source>').attr('src', publicUrl).attr('type', `audio/${extension}`)
                        );
                        const $audioContainer = $('<div>', {
                                class: 'flex flex-col items-center justify-center w-full p-4 bg-gray-100 dark:bg-gray-700 rounded-lg shadow-md'
                            }).append($audio)
                            .append($('<span>', {
                                class: 'mt-2 text-gray-800 dark:text-gray-200',
                                text: title
                            }));
                        $contentWrapper.append($audioContainer);
                    } else if ([
                            'mp4', 'webm', 'ogv', 'mkv', 'mov', 'avi', '3gp'
                        ].includes(extension)) {
                        const $video = $('<video>', {
                            controls: true,
                            autoplay: true,
                            class: 'w-full h-full object-fill'
                        }).append(
                            $('<source>').attr('src', publicUrl).attr('type', `video/${extension}`)
                        );
                        $contentWrapper.append($video);
                    } else if (extension === 'pdf' || ['html', 'htm'].includes(extension)) {
                        const $iframe = $('<iframe>', {
                            src: publicUrl,
                            class: 'w-full h-full',
                            frameborder: 0
                        });
                        $contentWrapper.append($iframe);
                    } else if ([
                            'txt', 'json', 'log', 'sql', 'csv', 'xml', 'ini', 'md', 'conf', 'config', 'yml', 'yaml', 'sh',
                            'bash', 'bat',
                            'c', 'cpp', 'h', 'java', 'py', 'js', 'ts', 'php', 'rb', 'go', 'cs', 'swift', 'rs', 'kt',
                            'scala', 'rst', 'adoc',
                            'env', 'properties', 'manifest', 'plist', 'tex'
                        ].includes(extension)) {
                        // Fetch the content of the text file and display it
                        fetch(publicUrl)
                            .then(response => response.text())
                            .then(text => {
                                const $pre = $('<pre>', {
                                    class: 'w-full h-full p-4 overflow-auto bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200',
                                    text: text
                                });
                                $contentWrapper.append($pre);
                                createWindow(title, $contentWrapper.html());
                            })
                            .catch(error => console.error('Error fetching text file:', error));
                        return;
                    } else {
                        showConfirmationModal(
                            '{{ __('cloud.header.cannot_preview') }}',
                            `{{ __('cloud.label.cannot_preview') }}`,
                            () => {
                                window.location.href = `${publicUrl}`;
                            },
                            null,
                            '{{ __('cloud.button.preview') }}',
                            '{{ __('cloud.button.cancel') }}',
                        );
                        return;
                    }

                    createWindow(title, $contentWrapper.html());
                }
            }
            $(document).on('click', '#file-list > div', function() {
                contextMenu.hide();
                cloud_open($(this));
            });

            $('#open-file').on('click', function() {
                contextMenu.hide();
                cloud_open(selectedFile)
            });

            $('#open-file-tab').on('click', function() {
                const url = selectedFile.data('url');
                const baseUrl = window.location.origin;
                const fullUrl = baseUrl + '/storage/root' + url;
                window.open(fullUrl, '_blank')
                contextMenu.hide();
            });
            $('#copy-file-url').on('click', function() {
                const fileUrl = selectedFile.data('url');
                const baseUrl = window.location.origin;

                const fullUrl = baseUrl + '/storage/root' + fileUrl;

                var textArea = document.createElement("textarea");
                textArea.value = fullUrl;
                document.body.appendChild(textArea);
                textArea.select();

                try {
                    document.execCommand("copy");
                } catch (err) {
                    console.log("Copy not supported or failed: ", err);
                }

                document.body.removeChild(textArea);

                contextMenu.hide();
            });

            $('#rename-file').on('click', function() {
                const url = selectedFile.data('url');
                contextMenu.hide();
            });
            $('#delete-file').on('click', function() {
                const url = selectedFile.data('url');
                const title = selectedFile.prop('title');
                const currentPath = url.split('/').slice(0, -1).join('/');
                contextMenu.hide();

                showConfirmationModal(
                    `{{ __('cloud.header.confirm_delete') }}`,
                    `{{ __('cloud.label.about_to_delete') }}<span class="line-clamp-4" style="word-wrap:break-word">${title}</span> {{ __('cloud.label.delete_warning') }}`,
                    function() {
                        client.deleteCloud(url)
                            .then(function(response) {
                                if (response.status == 'success') {
                                    updatePath(currentPath);
                                } else {

                                }
                            })
                            .catch(console.error);
                    },
                    null,
                    '{{ __('cloud.button.delete') }}',
                    '{{ __('cloud.button.cancel') }}',
                );
            });


            $(document).on('click', function(event) {
                if (!contextMenu.is(event.target) && contextMenu.has(event.target).length === 0 && selectedFile && !
                    selectedFile.is(event.target) && selectedFile.has(event.target).length === 0) {
                    contextMenu.hide();
                }
            });

            $(document).on('touchstart', '#file-list > div', function(event) {
                selectedFile = $(this);
                setTimeout(() => {
                    contextMenu.css({
                        display: 'block',
                        left: event.originalEvent.touches[0].pageX + 'px',
                        top: event.originalEvent.touches[0].pageY + 'px'
                    });
                }, 500);
            });

            $(document).on('touchend', function() {
                contextMenu.hide();
            });
            generatePathHtml($('nav .cloud-path'), data.result.query_path)
        }

        function createWindow(windowName, contentTag) {
            const $window = $(`<div class="window bg-white border flex flex-col border-gray-400 shadow-lg w-96 h-72 absolute top-24 left-24">
    <!-- Title Bar -->
    <div class="title-bar bg-blue-600 text-white p-2 cursor-move flex justify-between items-center" id="titleBar">
        <span class="text-xs line-clamp-1 max-w-full flex-1" style="word-wrap: break-word; text-white">${windowName}</span>
        <div class="controls space-x-2">
            <button class="minimize px-2" title="Minimize"><i class="fas fa-window-minimize"></i></button>
            <button class="maximize px-2" title="Maximize"><i class="fas fa-window-maximize"></i></button>
            <button class="close px-2" title="Close"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <!-- Content Area -->
    <div class="content flex-1 p-1 overflow-hidden" id="contentArea">
        ${contentTag}
    </div>
</div>`);

            $('body').append($window);

            let isMinimized = false;
            let isMaximized = false;
            let originalSize = {
                width: $window.width(),
                height: $window.height()
            };
            let originalPosition = {
                top: $window.position().top,
                left: $window.position().left
            };

            $window.resizable({
                handles: "n, e, s, w, ne, se, sw, nw",
                minWidth: 300,
                minHeight: 150
            });
            $window.draggable({
                handle: ".title-bar",
                containment: "body"
            });

            $window.find(".minimize").on("click", function() {
                if (isMinimized) {
                    $window.css({
                        height: originalSize.height + 'px',
                        overflow: 'visible'
                    });
                    isMinimized = false;
                } else {
                    originalSize.height = $window.height();
                    $window.css({
                        height: '2rem',
                        overflow: 'hidden'
                    });
                    isMinimized = true;
                }
            });

            $window.find(".maximize").on("click", function() {
                if (isMaximized) {
                    $window.css({
                        width: originalSize.width + "px",
                        height: originalSize.height + "px",
                        top: originalPosition.top + "px",
                        left: originalPosition.left + "px"
                    });
                    isMaximized = false;
                } else {
                    originalSize = {
                        width: $window.width(),
                        height: $window.height()
                    };
                    originalPosition = {
                        top: $window.position().top,
                        left: $window.position().left
                    };
                    $window.css({
                        width: '100%',
                        height: '100%',
                        top: 0,
                        left: 0
                    });
                    isMaximized = true;
                }
            });

            $window.find(".close").on("click", function() {
                $window.remove();
            });

        }
        updatePath('/homes/' + {{ Auth::user()->id }});
    </script>
    <div class="py-2 h-full">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 h-full">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
                <div class="p-6 text-gray-900 dark:text-gray-100 h-full flex flex-col">
                    <nav class="mb-2">
                        <ul class="flex space-x-2 cloud-path">
                        </ul>
                    </nav>
                    <div id="drop-area"
                        class="hidden flex-grow border-2 border-dashed border-gray-400 rounded-lg p-6 flex flex-col items-center justify-center">
                        <p class="text-gray-600 mb-4">Drag & drop your files here or click to upload.</p>
                        <input type="file" id="file-upload" class="hidden" multiple>
                        <label for="file-upload"
                            class="cursor-pointer bg-blue-500 text-white px-4 py-2 rounded-lg">{{ __('cloud.interface.header') }}</label>
                    </div>
                    <div class="flex-grow border-2 border-gray-400 rounded-lg p-2 flex flex-col">
                        <div class="flex flex-wrap overflow-auto" id="file-list"></div>
                    </div>

                    <div id="context-menu"
                        class="hidden fixed z-50 bg-white border border-gray-300 rounded-lg shadow-lg p-2 dark:bg-gray-800 dark:border-gray-600">
                        <ul class="space-y-1">
                            <li id="open-file"
                                class="cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 px-2 py-1 rounded">
                                {{ __('cloud.button.open') }}</li>
                            <li id="open-file-tab"
                                class="cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 px-2 py-1 rounded">
                                {{ __('cloud.button.open_tab') }}</li>
                            <li id="copy-file-url"
                                class="cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 px-2 py-1 rounded">
                                {{ __('cloud.button.copy_link') }}</li>
                            <li id="rename-file"
                                class="cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 px-2 py-1 rounded">
                                {{ __('cloud.button.rename') }}</li>
                            <li id="delete-file"
                                class="cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 px-2 py-1 rounded">
                                {{ __('cloud.button.delete') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const fileUpload = document.getElementById('file-upload');
        const dropArea = document.getElementById('drop-area');

        fileUpload.addEventListener('change', handleFileUpload);

        dropArea.addEventListener('dragover', (event) => {
            event.preventDefault();
            dropArea.classList.add('border-blue-500');
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('border-blue-500');
        });

        dropArea.addEventListener('drop', (event) => {
            event.preventDefault();
            dropArea.classList.remove('border-blue-500');
            const files = event.dataTransfer.files;
            handleFiles(files);
        });

        document.addEventListener('dragenter', (event) => {
            if (event.dataTransfer.items.length > 0) {
                dropArea.classList.remove('hidden');
            }
        });

        document.addEventListener('dragleave', (event) => {
            if (!dropArea.contains(event.relatedTarget)) {
                dropArea.classList.add('hidden');
            }
        });

        function handleFileUpload(event) {
            const files = event.target.files;
            handleFiles(files);
        }

        function handleFiles(files) {
            for (const file of files) {
                const li = document.createElement('li');
                li.textContent = file.name;
                fileList.appendChild(li);
            }
        }
    </script>
</x-app-layout>

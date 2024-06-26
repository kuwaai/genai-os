set "EXECUTOR_ACCESS_CODE="doc_qa --exclude=web_qa""
pushd ..\..\..\src\multi-chat
php artisan model:config "web_qa" "Web QA" --image "..\..\windows\executors\docQA & webQA\webQA.png"
php artisan model:config "doc_qa" "Document QA" --image "..\..\windows\executors\docQA & webQA\docQA.png"
popd
pushd ..\..\..\src\executor\docqa
start /b "" "python" "docqa.py" "--access_code" "web_qa" "doc_qa" --model taide --mmr_k 2 --chunk_size 256 --chunk_overlap 64 --lang zh-tw
popd

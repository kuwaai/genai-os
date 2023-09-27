#!/bin/bash

pushd ../../model_workers/model_api_server/
bash ./build.sh
popd
pushd ../../model_workers/contextual_chinese_convert/
bash ./build.sh
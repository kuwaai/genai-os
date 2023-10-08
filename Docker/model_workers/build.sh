#!/bin/bash

pushd ../../model_workers/worker_framework/
bash ./build.sh
popd
pushd ../../model_workers/contextual_chinese_convert/
bash ./build.sh
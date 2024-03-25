#!/bin/bash

pushd worker_framework/
bash ./build.sh
popd
pushd contextual_chinese_convert/
bash ./build.sh
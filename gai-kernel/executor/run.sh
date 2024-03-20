#!/bin/bash

export LAYOUT_CONFIG="./layout.yaml"
export LLM_NAME="EXECUTOR"

exec worker-server

#!/bin/bash

set -xeu

DOC_LEN=200
CONCURRENT=${1:-512}
NUM_REQ=$(($CONCURRENT * 100))
MODEL="thenlper/gte-large-zh"
ENDPOINT="http://127.0.0.1:8181/embeddings"

TMP_FILE=/tmp/banchmark_infinity_req.json
DOC=$(tr -dc A-Za-z0-9 </dev/urandom | head -c $DOC_LEN)
echo -e "{\"model\":\"$MODEL\",\"input\":[\"$DOC\"]}" > $TMP_FILE
cat $TMP_FILE
ab -n $NUM_REQ -c $CONCURRENT -T application/json -p $TMP_FILE $ENDPOINT
rm -f $TMP_FILE
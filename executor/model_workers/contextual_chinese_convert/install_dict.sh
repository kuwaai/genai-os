#!/bin/bash

set -xeu

dict_filename=dictionary/TWPhrasesExtend.txt
config_filename=dictionary/s2twp.json

ocd2_filename=${dict_filename%.*}.ocd2
install_path=$(pip show opencc | awk '/Location/{print $2}')/opencc/clib/share/opencc

opencc_dict -i ${dict_filename} -o ${ocd2_filename} -f text -t ocd2

install -m 644 ${ocd2_filename} ${install_path} 
install -m 644 ${config_filename} ${install_path} 
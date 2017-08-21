#!/usr/bin/env bash

find \
    bin docs source/assets/css/ source/*.latte source/konference/ tests\
    -type f -print0 \
    | xargs -0 -L1 bash -c 'test "$(tail -c 1 "$0")" && echo "No new line at end of file $0" && exit 1'

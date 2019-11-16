#!/bin/bash

help_and_exit() {
    echo "Usage $0 <type: html|xhtml|xml>";
    exit 1;
}

if [ ! $1 ]; then
    help_and_exit
fi

type=$1

if [ -z "$PARSERS" -a -f "$type.txt" ]; then
    PARSERS=$(cat $type.txt)
fi

for parser in $PARSERS; do
    if [ -f "./wrappers/$parser.php" ]; then
        echo "parser:$parser"
        php -l -f ./wrappers/$parser.php
    fi
done
#!/bin/bash

TIMER_SCRIPT=./timer.sh
TEST_NUMBER=0

timeit () {
	# Redirect output through file
	# *************
	# PIPE_FILE="current_parse.pipe"
	# trap "rm -f $PIPE_FILE" EXIT TERM
	# mknod $PIPE_FILE p
	# tee <$PIPE_FILE log_file.txt &
	# exec 1>&-
	# exec 1>$PIPE_FILE

	PID_FILE="parser_$TEST_NUMBER.pid"
	./timer.sh $TEST_NUMBER &

	/usr/bin/time --format="real:%e	user:%U	sys:%S	max RSS:%M" $@ $TEST_NUMBER 2>&1
	#
	# && PID=$! && ((PID++)) && echo "$PID">$PID_FILE # && $TIMER_SCRIPT $TEST_NUMBER &
	# increase because php process is children of time process

	# killall -u USERNAME if something went wrong

	# just for info
	# fname - first 8 bytes of process file
	# lstart - date and time of start
	# `ps -eo pid,ppid,tty,stat,comm,args,etimes,rss,pmem --no-headers`
	# `ps -pid $PID -o comm=`
}

print_header() {
    parser=$1
    test_file=$2
    echo "******************************"
    echo -e "parser:$parser\tfile:$test_file"
}

help_and_exit() {
    echo "Usage $0 <type: html|xhtml|xml> <number of iterations>" >&2
    exit 1;
}

askYN() {
    local SURE
    if [ -n "$1" ] ; then
       read -n 1 -p "$1 (y/[any]): " SURE
    else
       read -n 1 SURE
    fi
    echo "" 1>&2
    if [ "$SURE" = "y" ] ; then
       return 0
    else
       return 1
    fi
}

handler_php() {
    print_header $1 $2
    # $1 - parser_name.php, $2 - test_file.html
    # $3 - type (html), $4 - iterations num
    timeit php -f ./wrappers/$1.php $2 $3 $4
}

if [ ! $2 ]; then
    help_and_exit
fi

type=$1
if [ ! -e "$type.txt" ]; then
    help_and_exit
fi


if [[ "$2" =~ ^[0-9]+$ ]]; then
    num_iterations=$2
else
    help_and_exit
fi


askYN "Скрипт начнёт анализ страниц .$type после подтверждения. Продолжить?: " || exit

if [ -z "$PARSERS" -a -f "$type.txt" ]; then
    PARSERS=$(cat $type.txt)
fi

testfiles=$(ls ../resources/test-docs/test_*.$type)

for parser in $PARSERS; do
    if [ -f "./wrappers/$parser.php" ]; then
        echo "==============="
        echo $type;
        echo "==============="

        for testfile in $testfiles; do
        	((TEST_NUMBER++))
            handler_php $parser $testfile $type $num_iterations
        done
    fi
done

echo -e "\n"
read -p "Press any key for continue..." -n 1
exit 0
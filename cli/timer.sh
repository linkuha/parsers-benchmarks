#!/bin/bash

TEST_NUMBER=$1
SLEEP_INTERVAL=5
TIME_LIMIT=300
PID_FILE="parser_$TEST_NUMBER.pid"

sleep 2
trap "rm -f $PID_FILE" EXIT TERM

while [ "$SECONDS" -le "$TIME_LIMIT" ]
do
	if [ ! -r "$PID_FILE" ]; then
		exit 0 # "process is not running."
	fi

	read PID < "$PID_FILE"
	if ! ps -p "$PID" > /dev/null 2>&1; then
		exit 0
	fi
	sleep $SLEEP_INTERVAL
done

read PID < "$PID_FILE"
if ps -p "$PID" > /dev/null 2>&1; then
	kill -TERM "$PID" > /dev/null 2>&1
else
	exit 0
fi
sleep 5
if ps -p "$PID" > /dev/null 2>&1; then
	kill -HUP "$PID" > /dev/null 2>&1
else
	exit 0
fi
sleep 5
if ps -p "$PID" > /dev/null 2>&1; then
	kill -KILL "$PID" > /dev/null 2>&1
else
	exit 0
fi

exit 0
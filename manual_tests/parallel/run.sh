#!/usr/bin/env bash

TIMEOUT=8

run_test() {
    timeout -s SIGKILL $TIMEOUT ./run_test.sh $1
    wait
    sleep 1
}

run_test wc_subscribe_one_level
run_test wc_subscribe_two_levels
run_test wc_presence_one_level
run_test wc_presence_two_levels
run_test wc_presence_and_subscribe_one_level

printf "\nDone\n"

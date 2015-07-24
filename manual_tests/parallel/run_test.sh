#!/usr/bin/env bash

PUBNUB_PATH=`pwd | sed -e "s/\/manual_tests\/parallel//g"`

export PUBNUB_PATH=$PUBNUB_PATH

php $1/sub.php & php $1/pub.php


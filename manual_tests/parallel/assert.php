<?php

function assertEquals($expected, $real) {
    if ($expected != $real) {
        trigger_error("assertEquals: '$real' is not equal to '$expected'", E_USER_WARNING);
    }
}

function assertNotEquals($expected, $real) {
    if ($expected == $real) {
        trigger_error("assertNotEquals: '$real' is equal to '$expected'", E_USER_WARNING);
    }
}
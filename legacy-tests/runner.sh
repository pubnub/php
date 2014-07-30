#!/bin/bash

function testDir
{
    cd $1

    for file in ./*
        do
            if ! [[ "$file" = *Test.php ]]
            then
                continue
            fi

            eval "phpunit ${file:2}"

            if [ $? -ne 0 ]
            then
                echo "Test $file failed"
                exit
            fi
        done

    cd ../
}

testDir ./unit/
testDir ./integration/

echo "All tests passed successfully"
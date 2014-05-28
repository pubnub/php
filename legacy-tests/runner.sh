#!/bin/bash

cd ./unit/

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

echo "All tests passed successfully"
#!/bin/sh

echo "\nCleaning up..."
rm -rf ../legacy
rm -rf ../composer
echo "=========================================="
echo "\nBuilding legacy lib..."
echo "=========================================="
php ./builder.php PHP52
echo "\nBuilding composer lib..."
echo "=========================================="
php ./builder.php PHP53
echo "\n=========================================="
echo "Done\n";

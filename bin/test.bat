@echo off

set rootDir="%~dp0/../../../.."
set packageDir="%~dp0/.."
set phpunit="vendor/phpunit/phpunit/phpunit"

cd %packageDir%

php %rootDir%/%phpunit%
pause
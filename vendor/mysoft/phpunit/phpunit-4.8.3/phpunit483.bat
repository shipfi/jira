@ECHO OFF
SET BIN_TARGET=%~dp0/phpunit-4.8.3.phar
php "%BIN_TARGET%" %*

@ECHO OFF
SET BIN_TARGET=%~dp0/../behat/behat/bin/behat
php "%BIN_TARGET%" %*

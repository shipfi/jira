@echo off

rem -------------------------------------------------------------
rem mysoft php framework init
rem -------------------------------------------------------------

@setlocal

if not exist ../vendor ( git clone git@git-whyd.mysoft.com.cn:whyd/vendor.git ../vendor )
php init
php requirements.php

@endlocal
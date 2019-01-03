@REM @Author: Eka Syahwan
@REM @Date:   2017-09-14 06:18:06
@REM @Last Modified by:   Eka Syahwan
@REM Modified time: 2018-04-26 08:49:14
@echo off
set PATH=%PATH%;C:\xampp\php
title Sendinbox Professional Update 26 April 2018
:runsendinbox
php sendinbox.php
pause
cls
goto runsendinbox
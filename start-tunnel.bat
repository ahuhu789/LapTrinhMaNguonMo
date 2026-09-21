@echo off
title MCN Platform - Live Server & Cloudflare Tunnel
echo ========================================================
echo   KHOI DONG SERVER MCN PLATFORM & TAO LINK PUBLIC HTTPS
echo ========================================================

REM Kiem tra va them duong dan PHP
set PATH=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe;%PATH%

echo [1/2] Dang khoi dong Laravel server tren cong 8000...
start /b php artisan serve --host=127.0.0.1 --port=8000

timeout /t 2 /nobreak >nul

echo [2/2] Dang khoi tao Cloudflare Tunnel cong khai...
echo --------------------------------------------------------
echo Hay tim dong co chu 'trycloudflare.com' de lay link web!
echo --------------------------------------------------------
cloudflared.exe tunnel --url http://127.0.0.1:8000

pause

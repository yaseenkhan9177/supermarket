@echo off
echo ========================================
echo Restarting Apache to Load intl Extension
echo ========================================
echo.

echo Stopping Apache...
E:\xampp\apache\bin\httpd.exe -k stop
timeout /t 5 /nobreak

echo.
echo Starting Apache...
E:\xampp\apache\bin\httpd.exe -k start
timeout /t 3 /nobreak

echo.
echo ========================================
echo Apache Restarted!
echo ========================================
echo.
echo Please refresh your browser and try again.
echo.
pause

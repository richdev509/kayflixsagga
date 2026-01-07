@echo off
echo Demarrage du Laravel Scheduler...
echo.
echo Ce scheduler lance automatiquement le traitement des analytics toutes les 2 minutes
echo Pour arreter: Ctrl+C
echo.

cd C:\laravelProject\ProjetStream\stream\BackendLaravel

:loop
php artisan schedule:run
timeout /t 60 /nobreak >nul
goto loop

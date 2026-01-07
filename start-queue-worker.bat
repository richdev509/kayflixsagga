@echo off
echo Demarrage du Queue Worker pour le traitement des analytics...
echo.
echo Ce worker traite les sessions de visionnage par lots toutes les 2 minutes
echo Pour arreter: Ctrl+C
echo.

cd C:\laravelProject\ProjetStream\stream\BackendLaravel
php artisan queue:work --queue=default --tries=3 --timeout=120

pause

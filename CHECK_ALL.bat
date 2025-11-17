@echo off
echo ========================================
echo NOTIFICATION SYSTEM - COMPLETE CHECK
echo ========================================
echo.

echo Running comprehensive verification...
echo.
php verify_notification_setup.php

echo.
echo.
echo Press any key to see detailed help...
pause > nul

echo.
echo ========================================
echo QUICK FIX STEPS
echo ========================================
echo.
echo IF you see "Users WITHOUT FCM tokens" above:
echo.
echo 1. Rebuild Flutter app:
echo    cd c:\Workspace\fboys
echo    flutter clean
echo    flutter pub get
echo    flutter run --verbose
echo.
echo 2. Watch logs for: "FCM Token saved successfully"
echo.
echo 3. Users must REINSTALL the app (uninstall old one first)
echo.
echo 4. Run this check again to verify
echo.
echo ========================================
echo.
pause

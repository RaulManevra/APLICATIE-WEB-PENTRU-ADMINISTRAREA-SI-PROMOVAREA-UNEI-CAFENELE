@echo off
setlocal enabledelayedexpansion
color 0F
title Database Management - %DB_NAME%

:: ------------------------------------------
:: CONFIGURATION (no quotes here)
:: ------------------------------------------
set AMPPS_PATH=C:\Program Files\Ampps
set MYSQL_BIN=%AMPPS_PATH%\mysql\bin
set DB_NAME=mazi_coffee
set DB_USER=root
set DB_PASS=mysql

set SQL_FILE=%AMPPS_PATH%\www\APLICATIE-WEB-PENTRU-ADMINISTRAREA-SI-PROMOVAREA-UNEI-CAFENELE\Baza de date\mazi_coffee.sql
set BACKUP_FOLDER=%AMPPS_PATH%\www\APLICATIE-WEB-PENTRU-ADMINISTRAREA-SI-PROMOVAREA-UNEI-CAFENELE\Baza de date\_backups
set LOG_FILE=%BACKUP_FOLDER%\update_log.txt
set LOCK_FILE=%BACKUP_FOLDER%\session.lock

if not exist "%BACKUP_FOLDER%" mkdir "%BACKUP_FOLDER%"

:: ------------------------------------------
:: ANSI COLORS SETUP
:: ------------------------------------------
for /F "tokens=1,2 delims=#" %%a in ('"prompt #$H#$E# & echo on & for %%b in (1) do rem"') do set "ESC=%%b"

set "CReset=%ESC%[0m"
set "CCyan=%ESC%[96m"
set "CGreen=%ESC%[92m"
set "CYellow=%ESC%[93m"
set "CRed=%ESC%[91m"
set "CMagenta=%ESC%[95m"
set "CWhite=%ESC%[97m"
set "CGray=%ESC%[90m"

:: Timestamp for backups
for /f "tokens=1-6 delims=/:. " %%a in ("%date% %time%") do (
    set TIMESTAMP=%%c-%%b-%%a_%%d-%%e-%%f
)

:: ------------------------------------------
:: LOGGING START
:: ------------------------------------------
if exist "%LOCK_FILE%" (
    echo.
    echo %CYellow%[WARN] Previous session terminated unexpectedly/crashed!%CReset%
    echo. >> "%LOG_FILE%"
    echo [WARN] Previous session terminated unexpectedly/crashed! >> "%LOG_FILE%"
)

echo. >> "%LOG_FILE%"
echo ====================================================================== >> "%LOG_FILE%"
echo   SESSION START: %DATE% %TIME% >> "%LOG_FILE%"
echo   DATABASE: %DB_NAME%  ^|  COMPUTER: %COMPUTERNAME% >> "%LOG_FILE%"
echo ====================================================================== >> "%LOG_FILE%"


echo SESSION_RUNNING > "%LOCK_FILE%"

:: ------------------------------------------
:: Check MySQL server & start if needed
:: ------------------------------------------
echo.
echo  %CGray%Checking MySQL server status...%CReset%
echo [INFO] Checking MySQL server status... >> "%LOG_FILE%"

tasklist /FI "IMAGENAME eq mysqld.exe" | find /I "mysqld.exe" >nul

if %ERRORLEVEL% neq 0 (
    echo  %CYellow%[WARN] MySQL is not running. Attempting to start...%CReset%
    echo [WARN] MySQL is not running. Attempting to start... >> "%LOG_FILE%"
    start "" "%MYSQL_BIN%\mysqld.exe"
    timeout /t 3 >nul

    tasklist /FI "IMAGENAME eq mysqld.exe" | find /I "mysqld.exe" >nul
    if !ERRORLEVEL! neq 0 (
        echo.
        echo  %CRed%[ERROR] MySQL failed to start!%CReset%
        echo [ERROR] MySQL failed to start! >> "%LOG_FILE%"
        echo  %CRed%Please check your Ampps installation.%CReset%
        if exist "%LOCK_FILE%" del "%LOCK_FILE%"
        pause
        exit /b 1
    ) else (
        echo  %CGreen%[INFO] MySQL started successfully.%CReset%
        echo [INFO] MySQL started successfully. >> "%LOG_FILE%"
    )
) else (
    echo  %CGreen%[INFO] MySQL is already running.%CReset%
    echo [INFO] MySQL is already running. >> "%LOG_FILE%"
)

timeout /t 1 >nul
cd /d "%MYSQL_BIN%"

:: ------------------------------------------
:: User menu
:: ------------------------------------------
:MENU
cls
echo.
echo %CCyan%==================================================%CReset%
echo   %CWhite%DATABASE MANAGEMENT SYSTEM%CReset%
echo   Target Database: %CMagenta%%DB_NAME%%CReset%
echo %CCyan%==================================================%CReset%
echo.
echo   %CWhite%1)%CReset% Import Database (%CGray%Recreate ^& Load%CReset%)
echo   %CWhite%2)%CReset% Export Database (%CGray%Backup ^& Save%CReset%)
echo   %CWhite%3)%CReset% Exit
echo.
echo %CCyan%==================================================%CReset%
set /p choice="  >> %CYellow%Choose option [1-3]:%CReset% "

if "%choice%"=="1" goto IMPORT
if "%choice%"=="2" goto EXPORT
if "%choice%"=="3" (
    echo [INFO] User exited menu. >> "%LOG_FILE%"
    echo [INFO] Session closed normally. >> "%LOG_FILE%"
    if exist "%LOCK_FILE%" del "%LOCK_FILE%"
    exit /b
)

echo.
echo  %CYellow%[WARN] Invalid choice. Please try again.%CReset%
echo [WARN] Invalid menu choice made. >> "%LOG_FILE%"
pause
goto MENU

:: ------------------------------------------
:: IMPORT MODE
:: ------------------------------------------
:IMPORT
cls
echo.
echo %CCyan%==================================================%CReset%
echo   %CGreen%IMPORT MODE%CReset%
echo %CCyan%==================================================%CReset%
echo [ACTION] IMPORT SELECTED >> "%LOG_FILE%"

if not exist "%SQL_FILE%" (
    echo.
    echo  %CRed%[ERROR] SQL file not found at:%CReset%
    echo  %SQL_FILE%
    echo [ERROR] SQL file not found at: %SQL_FILE% >> "%LOG_FILE%"
    echo.
    pause
    goto MENU
)

echo.
echo  %CCyan%[1/2]%CReset% Dropping and recreating database...
echo [INFO] Dropping and recreating database... >> "%LOG_FILE%"
mysql -u %DB_USER% -p%DB_PASS% -e "DROP DATABASE IF EXISTS %DB_NAME%; CREATE DATABASE %DB_NAME%;"
if %ERRORLEVEL% neq 0 (
    echo  %CRed%[ERROR] Failed to recreate database! Error Code: %ERRORLEVEL%%CReset%
    echo [ERROR] Failed to recreate database! Error Code: %ERRORLEVEL% >> "%LOG_FILE%"
    pause
    goto MENU
)

echo  %CCyan%[2/2]%CReset% Importing data from SQL file...
echo [INFO] Importing data from SQL file... >> "%LOG_FILE%"
mysql -u %DB_USER% -p%DB_PASS% %DB_NAME% < "%SQL_FILE%"
if %ERRORLEVEL% neq 0 (
    echo  %CRed%[ERROR] Import failed! Error Code: %ERRORLEVEL%%CReset%
    echo [ERROR] Import failed! Error Code: %ERRORLEVEL% >> "%LOG_FILE%"
    pause
    goto MENU
)

echo.
echo  %CGreen%[SUCCESS] Database imported successfully!%CReset%
echo ====================================== >> "%LOG_FILE%"
echo   IMPORT SUCCESS  ^|  %DATE% %TIME% >> "%LOG_FILE%"
echo ====================================== >> "%LOG_FILE%"
echo.
pause
goto MENU

:: ------------------------------------------
:: EXPORT MODE
:: ------------------------------------------
:EXPORT
cls
echo.
echo %CCyan%==================================================%CReset%
echo   %CGreen%EXPORT MODE%CReset%
echo %CCyan%==================================================%CReset%
echo [ACTION] EXPORT SELECTED >> "%LOG_FILE%"

echo.
echo  %CCyan%[1/2]%CReset% Backing up existing SQL file (if any)...
if exist "%SQL_FILE%" (
    echo [INFO] Backing up existing SQL file... >> "%LOG_FILE%"
    copy "%SQL_FILE%" "%BACKUP_FOLDER%\backup_%DB_NAME%_%TIMESTAMP%.sql" >nul
    echo  ... %CGreen%Backup created.%CReset%
) else (
    echo  ... %CGray%No existing file to backup.%CReset%
)

echo  %CCyan%[2/2]%CReset% Exporting database to SQL file...
echo [INFO] Exporting database to SQL file... >> "%LOG_FILE%"
mysqldump -u %DB_USER% -p%DB_PASS% %DB_NAME% > "%SQL_FILE%"
if %ERRORLEVEL% neq 0 (
    echo  %CRed%[ERROR] Export failed! Error Code: %ERRORLEVEL%%CReset%
    echo [ERROR] Export failed! Error Code: %ERRORLEVEL% >> "%LOG_FILE%"
    pause
    goto MENU
)

echo.
echo  %CGreen%[SUCCESS] Database exported successfully!%CReset%
echo ====================================== >> "%LOG_FILE%"
echo   EXPORT SUCCESS  ^|  %DATE% %TIME% >> "%LOG_FILE%"
echo ====================================== >> "%LOG_FILE%"
echo.
pause
goto MENU

endlocal

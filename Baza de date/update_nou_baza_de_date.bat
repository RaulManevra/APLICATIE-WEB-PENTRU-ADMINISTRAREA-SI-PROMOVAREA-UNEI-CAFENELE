@echo off
setlocal enabledelayedexpansion

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

if not exist "%BACKUP_FOLDER%" mkdir "%BACKUP_FOLDER%"

:: Timestamp for backups
for /f "tokens=1-6 delims=/:. " %%a in ("%date% %time%") do (
    set TIMESTAMP=%%c-%%b-%%a_%%d-%%e-%%f
)

echo ============================================== >> "%LOG_FILE%"
echo Run: %DATE% %TIME% >> "%LOG_FILE%"
echo ============================================== >> "%LOG_FILE%"

:: ------------------------------------------
:: Check MySQL server & start if needed
:: ------------------------------------------
echo Checking MySQL server...
tasklist /FI "IMAGENAME eq mysqld.exe" | find /I "mysqld.exe" >nul

if %ERRORLEVEL% neq 0 (
    echo Trying to start MySQL...
    echo Starting MySQL... >> "%LOG_FILE%"
    start "" "%MYSQL_BIN%\mysqld.exe"
    timeout /t 3 >nul

    tasklist /FI "IMAGENAME eq mysqld.exe" | find /I "mysqld.exe" >nul
    if %ERRORLEVEL% neq 0 (
        echo ERROR: MySQL not running! >> "%LOG_FILE%"
        echo MySQL failed to start.
        pause
        exit /b 1
    )
)

echo MySQL running. >> "%LOG_FILE%"
cd /d "%MYSQL_BIN%"

:: ------------------------------------------
:: User menu
:: ------------------------------------------
cls
echo ======================================
echo Database Management - %DB_NAME%
echo ======================================
echo 1) Import SQL (drop + recreate + import)
echo 2) Export SQL (dump DB to file)
echo 3) Exit
echo ======================================
set /p choice="Choose option: "

if "%choice%"=="1" goto IMPORT
if "%choice%"=="2" goto EXPORT
if "%choice%"=="3" exit /b

echo Invalid choice.
pause
exit /b

:: ------------------------------------------
:: IMPORT MODE
:: ------------------------------------------
:IMPORT
if not exist "%SQL_FILE%" (
    echo SQL FILE NOT FOUND: %SQL_FILE%
    echo SQL missing error. >> "%LOG_FILE%"
    pause
    exit /b 1
)

echo Backup before import...
mysqldump -u %DB_USER% -p%DB_PASS% %DB_NAME% > "%BACKUP_FOLDER%\importbackup_%DB_NAME%_%TIMESTAMP%.sql"

echo Dropping and recreating DB...
mysql -u %DB_USER% -p%DB_PASS% -e "DROP DATABASE IF EXISTS %DB_NAME%; CREATE DATABASE %DB_NAME%;"
if %ERRORLEVEL% neq 0 (
    echo ERROR: DB recreate failed! >> "%LOG_FILE%"
    pause
    exit /b 1
)

echo Importing from SQL_FILE...
mysql -u %DB_USER% -p%DB_PASS% %DB_NAME% < "%SQL_FILE%"
if %ERRORLEVEL% neq 0 (
    echo ERROR import! >> "%LOG_FILE%"
    pause
    exit /b 1
)

echo IMPORT SUCCESS >> "%LOG_FILE%"
echo Import operation completed.
pause
exit /b 0

:: ------------------------------------------
:: EXPORT MODE
:: ------------------------------------------
:EXPORT
echo Backup existing SQL file if any...
if exist "%SQL_FILE%" (
    copy "%SQL_FILE%" "%BACKUP_FOLDER%\exportbackup_%DB_NAME%_%TIMESTAMP%.sql" >nul
)

echo Exporting DB to %SQL_FILE%...
mysqldump -u %DB_USER% -p%DB_PASS% %DB_NAME% > "%SQL_FILE%"
if %ERRORLEVEL% neq 0 (
    echo ERROR export! >> "%LOG_FILE%"
    pause
    exit /b 1
)

echo EXPORT SUCCESS >> "%LOG_FILE%"
echo Export operation completed.
pause
exit /b 0

endlocal

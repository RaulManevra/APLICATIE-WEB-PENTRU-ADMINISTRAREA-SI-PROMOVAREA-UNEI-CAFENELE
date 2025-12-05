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

:: Create backup folder if missing (quote when used)
if not exist "%BACKUP_FOLDER%" (
    mkdir "%BACKUP_FOLDER%"
)

:: Timestamp for backup filenames (locale-dependent)
for /f "tokens=1-6 delims=/:. " %%a in ("%date% %time%") do (
    set TIMESTAMP=%%c-%%b-%%a_%%d-%%e-%%f
)

echo ============================================== >> "%LOG_FILE%"
echo Update run: %DATE% %TIME% >> "%LOG_FILE%"
echo ============================================== >> "%LOG_FILE%"

:: ------------------------------------------
:: Check MySQL server & start if not running
:: ------------------------------------------
echo Checking MySQL server...
tasklist /FI "IMAGENAME eq mysqld.exe" | find /I "mysqld.exe" >nul

if %ERRORLEVEL% neq 0 (
    echo MySQL not running. Attempting to start it...
    echo MySQL not running. Attempting to start it... >> "%LOG_FILE%"

    start "" "%AMPPS_PATH%\mysql\bin\mysqld.exe"

    timeout /t 3 >nul

    tasklist /FI "IMAGENAME eq mysqld.exe" | find /I "mysqld.exe" >nul
    if %ERRORLEVEL% neq 0 (
        echo ERROR: Unable to start MySQL server! >> "%LOG_FILE%"
        echo Update aborted: MySQL not running.
        pause
        exit /b 1
    )
)

echo MySQL server running.
echo MySQL server OK. >> "%LOG_FILE%"

:: Move to MySQL binary directory (use /d and quote)
cd /d "%MYSQL_BIN%"

:: ------------------------------------------
:: Ensure SQL file exists
:: ------------------------------------------
if not exist "%SQL_FILE%" (
    echo ERROR: SQL file not found: %SQL_FILE%
    echo ERROR: SQL file not found: %SQL_FILE% >> "%LOG_FILE%"
    pause
    exit /b 1
)

:: ------------------------------------------
:: Backup current DB
:: ------------------------------------------
echo Creating backup...
echo Backing up current database... >> "%LOG_FILE%"

mysqldump -u %DB_USER% -p%DB_PASS% %DB_NAME% > "%BACKUP_FOLDER%\backup_%DB_NAME%_%TIMESTAMP%.sql"

if %ERRORLEVEL% neq 0 (
    echo WARNING: Failed to create backup! >> "%LOG_FILE%"
    echo WARNING: Backup failed. Continuing with update...
) else (
    echo Backup saved as backup_%DB_NAME%_%TIMESTAMP%.sql >> "%LOG_FILE%"
)

:: ------------------------------------------
:: Reset DB
:: ------------------------------------------
echo Resetting database...
echo Dropping and recreating %DB_NAME%... >> "%LOG_FILE%"

mysql -u %DB_USER% -p%DB_PASS% -e "DROP DATABASE IF EXISTS %DB_NAME%; CREATE DATABASE %DB_NAME%;"

if %ERRORLEVEL% neq 0 (
    echo ERROR: Failed to recreate database! >> "%LOG_FILE%"
    echo Update failed.
    pause
    exit /b 1
)

:: ------------------------------------------
:: Import latest SQL
:: ------------------------------------------
echo Importing updated SQL...
echo Importing %SQL_FILE% into %DB_NAME%... >> "%LOG_FILE%"

mysql -u %DB_USER% -p%DB_PASS% %DB_NAME% < "%SQL_FILE%"

if %ERRORLEVEL% neq 0 (
    echo ERROR: SQL import failed! >> "%LOG_FILE%"
    echo Update failed.
    pause
    exit /b 1
)

echo Update successful! >> "%LOG_FILE%"
echo Database update completed.
pause
endlocal

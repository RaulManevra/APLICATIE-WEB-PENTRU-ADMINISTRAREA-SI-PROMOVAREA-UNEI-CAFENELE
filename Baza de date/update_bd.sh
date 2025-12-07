#!/bin/bash

# Configuration
DB_NAME="mazi_coffee"
DB_USER="root"
DB_PASS="mysql"

# AMPPS Path detection (MacOS)
AMPPS_PATH="/Applications/AMPPS"
MYSQL_BIN="$AMPPS_PATH/apps/mysql/bin"

# Project paths (adjust relative to script location if needed, assuming valid structure)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SQL_FILE="$SCRIPT_DIR/mazi_coffee.sql"
BACKUP_FOLDER="$SCRIPT_DIR/_backups"
LOG_FILE="$BACKUP_FOLDER/update_log.txt"

# Ensure backup folder exists
mkdir -p "$BACKUP_FOLDER"

# Colors
RESTORE='\033[0m'
CYAN='\033[0;36m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
RED='\033[0;31m'
WHITE='\033[1;37m'
GRAY='\033[0;90m'

# Logging function
log() {
    local level="$1"
    local message="$2"
    local timestamp=$(date "+%Y-%m-%d %H:%M:%S")
    echo "[$timestamp] [$level] $message" >> "$LOG_FILE"
}

# Check MySQL Status
check_mysql() {
    log "INFO" "Checking MySQL server status..."
    if ! pgrep -f "mysqld" > /dev/null; then
        echo -e "${YELLOW}[WARN] MySQL is not running. Attempting to start...${RESTORE}"
        log "WARN" "MySQL is not running. Attempting to start..."
        "$AMPPS_PATH/mysql/bin/mysqld" --daemonize
        sleep 3
        if ! pgrep -f "mysqld" > /dev/null; then
            echo -e "${RED}[ERROR] MySQL failed to start!${RESTORE}"
            echo -e "${RED}Please check AMPPS manually.${RESTORE}"
            log "ERROR" "MySQL failed to start!"
            exit 1
        else
            echo -e "${GREEN}[INFO] MySQL started successfully.${RESTORE}"
            log "INFO" "MySQL started successfully."
        fi
    else
        echo -e "${GREEN}[INFO] MySQL is already running.${RESTORE}"
        log "INFO" "MySQL is already running."
    fi
}

check_mysql

while true; do
    clear
    echo -e "${CYAN}==================================================${RESTORE}"
    echo -e "  ${WHITE}DATABASE MANAGEMENT SYSTEM (MacOS)${RESTORE}"
    echo -e "  Target Database: ${CYAN}$DB_NAME${RESTORE}"
    echo -e "${CYAN}==================================================${RESTORE}"
    echo
    echo -e "  ${WHITE}1)${RESTORE} Import Database (${GRAY}Recreate & Load${RESTORE})"
    echo -e "  ${WHITE}2)${RESTORE} Export Database (${GRAY}Backup & Save${RESTORE})"
    echo -e "  ${WHITE}3)${RESTORE} List Backups"
    echo -e "  ${WHITE}4)${RESTORE} Exit"
    echo
    echo -e "${CYAN}==================================================${RESTORE}"
    read -p "  >> Choose option [1-4]: " choice

    case $choice in
        1)
            # Import
            clear
            echo -e "${CYAN}==================================================${RESTORE}"
            echo -e "  ${GREEN}IMPORT MODE${RESTORE}"
            echo -e "${CYAN}==================================================${RESTORE}"
            log "ACTION" "IMPORT SELECTED"

            if [ ! -f "$SQL_FILE" ]; then
                echo -e "${RED}[ERROR] SQL file not found at:${RESTORE}"
                echo "$SQL_FILE"
                log "ERROR" "SQL file not found at: $SQL_FILE"
                read -p "Press ENTER to continue..."
                continue
            fi

            echo -e "${CYAN}[1/2]${RESTORE} Dropping and recreating database..."
            log "INFO" "Dropping and recreating database..."
            "$MYSQL_BIN/mysql" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS \`$DB_NAME\`; CREATE DATABASE \`$DB_NAME\`;"
            if [ $? -ne 0 ]; then
                 echo -e "${RED}[ERROR] Failed to recreate database!${RESTORE}"
                 log "ERROR" "Failed to recreate database!"
                 read -p "Press ENTER to continue..."
                 continue
            fi

            echo -e "${CYAN}[2/2]${RESTORE} Importing data from SQL file..."
            log "INFO" "Importing data from SQL file..."
            "$MYSQL_BIN/mysql" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_FILE"
            if [ $? -ne 0 ]; then
                 echo -e "${RED}[ERROR] Import failed!${RESTORE}"
                 log "ERROR" "Import failed!"
                 read -p "Press ENTER to continue..."
                 continue
            fi

            echo
            echo -e "${GREEN}[SUCCESS] Database imported successfully!${RESTORE}"
            log "INFO" "Database imported successfully!"
            read -p "Press ENTER to continue..."
            ;;
        2)
            # Export
            clear
            echo -e "${CYAN}==================================================${RESTORE}"
            echo -e "  ${GREEN}EXPORT MODE${RESTORE}"
            echo -e "${CYAN}==================================================${RESTORE}"
            log "ACTION" "EXPORT SELECTED"

            echo -e "${CYAN}[1/2]${RESTORE} Backing up existing SQL file..."
            if [ -f "$SQL_FILE" ]; then
                log "INFO" "Backing up existing SQL file..."
                TIMESTAMP=$(date "+%Y-%m-%d_%H-%M-%S")
                cp "$SQL_FILE" "$BACKUP_FOLDER/backup_${DB_NAME}_${TIMESTAMP}.sql"
                echo -e " ... ${GREEN}Backup created.${RESTORE}"
            else
                echo -e " ... ${GRAY}No existing file to backup.${RESTORE}"
            fi

            echo -e "${CYAN}[2/2]${RESTORE} Exporting database to SQL file..."
            log "INFO" "Exporting database to SQL file..."
            "$MYSQL_BIN/mysqldump" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$SQL_FILE"

            if [ $? -ne 0 ]; then
                echo -e "${RED}[ERROR] Export failed!${RESTORE}"
                log "ERROR" "Export failed!"
                read -p "Press ENTER to continue..."
                continue
            fi

            echo
            echo -e "${GREEN}[SUCCESS] Database exported successfully!${RESTORE}"
            log "INFO" "Database exported successfully!"
            read -p "Press ENTER to continue..."
            ;;
        3)
            # List Backups
            clear
            echo
            echo -e "${CYAN}==================================================${RESTORE}"
            echo -e "  ${WHITE}EXISTING BACKUPS${RESTORE}"
            echo -e "${CYAN}==================================================${RESTORE}"
            echo
            if [ -z "$(ls -A "$BACKUP_FOLDER"/*.sql 2>/dev/null)" ]; then
                 echo -e "${YELLOW}No backups found.${RESTORE}"
            else
                 ls -lt "$BACKUP_FOLDER"/*.sql | awk '{print $NF}' | xargs -n 1 basename
            fi
            log "INFO" "Listed backups."
            echo
            read -p "Press ENTER to continue..."
            ;;
        4)
            log "INFO" "User exited menu."
            exit 0
            ;;
        *)
            echo -e "${YELLOW}[WARN] Invalid choice.${RESTORE}"
            log "WARN" "Invalid choice."
            sleep 1
            ;;
    esac
done

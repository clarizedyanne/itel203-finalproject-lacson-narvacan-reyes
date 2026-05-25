@echo off
SET BACKUP_DIR=C:\xampp\htdocs\olis_coffee\backups
SET YEAR=%date:~10,4%
SET MONTH=%date:~4,2%
SET DAY=%date:~7,2%
SET FILENAME=backup_%YEAR%_%MONTH%_%DAY%.sql

IF NOT EXIST "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

C:\xampp\mysql\bin\mysqldump.exe --user=root --password= olis_coffee > "%BACKUP_DIR%\%FILENAME%"

echo [%date% %time%] Backup saved: %FILENAME% >> "%BACKUP_DIR%\backup_log.txt"
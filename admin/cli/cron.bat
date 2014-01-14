@echo off
::10,4-4,2-7,2 for production
C:/xampp/php/php.exe -f c:/xampp/htdocs/moodle/admin/cli/cron.php >> c:/xampp/htdocs/moodle/admin/cli/cron-logs/%date:~6,4%%date:~3,2%%date:~0,2%.log
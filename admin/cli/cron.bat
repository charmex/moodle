@echo off
php -f c:/xampp/htdocs/moodle/admin/cli/cron.php >> c:/xampp/htdocs/moodle/admin/cli/cron-logs/%date:~6,4%%date:~3,2%%date:~0,2%.log
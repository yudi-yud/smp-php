-- Fix MySQL Authentication for PHP/phpMyAdmin
-- Run this in MySQL console or command line:

-- 1. Connect to MySQL as root
-- Open terminal/command prompt and run:
-- mysql -u root -p

-- 2. Change the authentication plugin for root user
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_password';

-- 3. If you use phpmyadmin user, also change it:
ALTER USER 'pma'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_password';

-- 4. Flush privileges
FLUSH PRIVILEGES;

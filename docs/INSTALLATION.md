# StockSync — Installation Guide

Copyright © 2026 NIYIGENA Kevin.
Developed under the XEVENTION brand.

## 1. Overview

This guide explains how to install and configure StockSync
on a Linux server using Apache, PHP, and MySQL/MariaDB.

These instructions are intended for a fresh installation.
Do not recreate or import the database if you already have
a working StockSync database containing important data.

## 2. Requirements

- Linux operating system (Kali Linux, Debian, or Ubuntu)
- Apache HTTP Server
- PHP with PDO MySQL support
- MySQL or MariaDB
- A modern web browser

StockSync uses PHP, MySQL/MariaDB, Bootstrap, Bootstrap Icons,
and Chart.js.

## 3. Install the Server Packages

On a Debian-based system, update the package index:

    sudo apt update

Install Apache, MariaDB, PHP, and the PHP database extension:

    sudo apt install apache2 mariadb-server php libapache2-mod-php php-mysql

Enable the Apache rewrite module:

    sudo a2enmod rewrite

Start and enable the services:

    sudo systemctl enable --now apache2
    sudo systemctl enable --now mariadb

## 4. Place the Project Files

Place the StockSync project in your web server directory.

Example project location:

    /var/www/StockSync

The Apache document root should point to the project's
public directory:

    /var/www/StockSync/public

Do not expose the project root as the public document root.

## 5. Configure Apache

Create an Apache virtual host:

    sudo nano /etc/apache2/sites-available/stocksync.conf

Example configuration:

    <VirtualHost *:80>
        ServerName stocksync.local
        DocumentRoot /var/www/StockSync/public

        <Directory /var/www/StockSync/public>
            AllowOverride All
            Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/stocksync_error.log
        CustomLog ${APACHE_LOG_DIR}/stocksync_access.log combined
    </VirtualHost>

Enable the site and reload Apache:

    sudo a2ensite stocksync.conf
    sudo systemctl reload apache2

For local development, add this entry to the system hosts file:

    127.0.0.1 stocksync.local

Edit the hosts file with:

    sudo nano /etc/hosts

## 6. Configure the Database

Open the MariaDB administrative console:

    sudo mariadb

For a completely new installation, create a database:

    CREATE DATABASE stocksync
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

Create a dedicated database user, replacing the example
password with a new, strong password:

    CREATE USER 'stocksync_user'@'localhost'
    IDENTIFIED BY 'REPLACE_WITH_A_STRONG_PASSWORD';

Grant the user access to the StockSync database:

    GRANT ALL PRIVILEGES ON stocksync.*
    TO 'stocksync_user'@'localhost';

    FLUSH PRIVILEGES;

    EXIT;

If the database already exists, do not create another one
or import the schema again without first checking its contents.

## 7. Import the Database Schema

For a fresh installation, import the supplied database schema:

    mariadb -u stocksync_user -p stocksync < /var/www/StockSync/database.sql

Enter the database user's password when prompted.

Do not run this import against an existing production database
without first reviewing the SQL file and making a backup.

## 8. Configure StockSync

Create your local configuration from the supplied example:

    cp app/config/config.example.php app/config/config.php

Open the new configuration file:

    nano app/config/config.php

Review the database settings:

    DB_HOST
    DB_NAME
    DB_USER
    DB_PASS
    DB_CHARSET

Set these values to match the database and user created above.

Review the application settings as well:

    APP_NAME
    APP_URL
    ENVIRONMENT

For the example local installation, APP_URL should match:

    http://stocksync.local

Keep database credentials private. Never publish real passwords
in source code repositories, screenshots, or documentation.

## 9. File Permissions

Apache must be able to read the application files.

Review ownership and permissions before changing them.
Do not make the entire project directory world-writable.

If the application has upload or storage directories,
grant write access only to the directories that require it.

## 10. Verify the Installation

Check Apache:

    sudo systemctl status apache2

Check MariaDB:

    sudo systemctl status mariadb

Check PHP:

    php -v

Open the application in a browser:

    http://stocksync.local

If the application does not load, inspect the Apache error log:

    sudo tail -n 50 /var/log/apache2/stocksync_error.log

Also review the PHP and application configuration.

## 11. Security Checklist

Before deploying StockSync on a public server:

- Use strong, unique database credentials.
- Set ENVIRONMENT to production.
- Disable public PHP error display.
- Use HTTPS.
- Keep database credentials outside public directories.
- Restrict database access to authorized users.
- Back up the database regularly.
- Review file and directory permissions.
- Review third-party component licenses.
- Test authentication and authorization.
- Never publish local backups or sensitive configuration files.

## 12. Important Notes

- The public directory is the intended web document root.
- The project uses its own application autoloader.
- Do not assume Composer installation is required.
- The application configuration currently uses PHP constants,
  not a .env file.
- Email delivery for password-reset workflows must be configured
  separately if it is not already integrated.

---

StockSync
Copyright © 2026 NIYIGENA Kevin.
Developed under the XEVENTION brand.
All rights reserved.

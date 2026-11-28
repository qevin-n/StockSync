# StockSync

StockSync is a web-based inventory and stock management application built with PHP, MySQL/MariaDB, and Bootstrap 5. It helps businesses manage products, suppliers, customers, purchases, sales, and stock movements through a centralized dashboard.

## Features

- **Business separation:** Business-owned records are scoped using business identifiers.
- **Role-based access control:** Admin, Manager, and Staff roles with different access levels.
- **Authentication:** Registration, login, session management, and password-reset token handling.
- **Product management:** Product records, SKUs, pricing, stock levels, and low-stock information.
- **Categories:** Organize products into categories.
- **Suppliers and customers:** Maintain business contact records.
- **Purchases:** Record supplier purchases and manage purchase status.
- **Sales:** Record customer sales and track payment status.
- **Stock movements:** Record inventory changes associated with stock operations.
- **Dashboard:** View inventory statistics, sales and purchase summaries, and recent activity.
- **Responsive interface:** Bootstrap-based layout with navigation and dashboard components.

## Technology Stack

- PHP 8+
- MySQL / MariaDB
- Bootstrap 5.3
- Bootstrap Icons
- Chart.js
- Vanilla JavaScript
- Apache or Nginx

## Requirements

- PHP 8.0 or later
- MySQL 5.7+ or a compatible MariaDB release
- PHP PDO MySQL extension
- PHP sessions enabled
- Apache with URL rewriting or an appropriately configured Nginx server

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/qevin-n/StockSync.git
   cd StockSync
   ```

2. Create a database and database user. Grant the user access to the StockSync database.

3. Import the schema into a new, empty database:

   ```bash
   mysql -u your_db_user -p your_database_name < database.sql
   ```

4. Create your local configuration:

   ```bash
   cp app/config/config.example.php app/config/config.php
   ```

5. Edit `app/config/config.php` with your database credentials and application settings. Set `APP_URL` to your installation URL. Never publish real credentials.

6. Configure your web server so that its document root points to the project's `public/` directory.

7. Configure URL rewriting. Example Nginx rule:

   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

8. Set appropriate permissions for application and runtime directories.

9. Open StockSync in your browser and register the first business account.

See the [Installation Guide](docs/INSTALLATION.md) for complete instructions.

## Roles

- **Admin:** Administrative access, including user management and restricted deletion operations.
- **Manager:** Access to operational features such as products, purchases, sales, suppliers, customers, and reports, subject to controller permissions.
- **Staff:** Access to permitted operational tasks, including product viewing and sales creation.

Access is enforced through server-side authorization checks.

## Password Reset

StockSync includes password-reset token generation, storage, validation, and expiry handling.

Email delivery is not yet integrated. A mail transport must be configured and connected before password-reset emails can be sent.

## Security Notes

- Passwords are handled using PHP password-hashing functions.
- Database access uses PDO prepared statements.
- CSRF protection is implemented for supported state-changing forms.
- Role restrictions are enforced by application middleware and controllers.
- Business-owned data queries commonly use `business_id` scoping.
- Local configuration files and runtime data should not be committed.

Review the application and deployment configuration before using it with real business data. This overview does not constitute a security guarantee.

## Documentation

- [Installation Guide](docs/INSTALLATION.md)
- [User Guide](docs/USER_GUIDE.md)
- [Developer Guide](docs/DEVELOPER_GUIDE.md)
- [Database Documentation](docs/DATABASE.md)
- [Third-Party Licenses](docs/THIRD_PARTY_LICENSES.md)

## Copyright and Licensing

Copyright © 2026 NIYIGENA Kevin.

Developed under the XEVENTION brand.

StockSync is proprietary software. All rights reserved. Unauthorized copying, redistribution, modification, or commercial use requires prior authorization from the applicable rights holder.

Third-party components remain subject to their respective licenses. See [Third-Party Licenses](docs/THIRD_PARTY_LICENSES.md).

# StockSync — Developer Guide

Copyright © 2026 NIYIGENA Kevin.
Developed under the XEVENTION brand.

## 1. Introduction

This document provides technical guidance for developers
working on StockSync.

It describes the project structure, configuration,
database access, routing, security considerations,
and recommended development practices.

## 2. Technology Stack

StockSync uses:

- PHP for application logic.
- MySQL/MariaDB for persistent data storage.
- PDO for database connectivity.
- Bootstrap for interface styling.
- Bootstrap Icons for interface icons.
- Chart.js for data visualization.
- Apache HTTP Server for the documented deployment setup.

The project uses a custom application autoloader.
Composer is not currently required by the project.

## 3. Project Structure

Important project directories and files include:

    StockSync/
    ├── app/
    │   ├── config/
    │   │   ├── config.php
    │   │   └── database.php
    │   ├── controllers/
    │   ├── helpers/
    │   ├── middleware/
    │   ├── models/
    │   └── views/
    │       ├── auth/
    │       ├── layouts/
    │       └── ...
    ├── docs/
    ├── public/
    │   └── assets/
    ├── routes/
    │   └── web.php
    ├── vendor/
    │   └── autoload.php
    ├── database.sql
    ├── LICENSE
    ├── NOTICE
    └── README.md

The exact contents may change as development continues.

### Main directories

- app/config: application and database configuration.
- app/controllers: request-handling controllers.
- app/helpers: reusable application helper functions.
- app/middleware: request middleware.
- app/models: database and business data operations.
- app/views: user interface templates.
- public: web-accessible entry point and public assets.
- routes: application route definitions.
- vendor: custom autoloading support.
- docs: project documentation.

## 4. Application Configuration

The main configuration file is:

    app/config/config.php

It defines database connection settings, application
identity, URL configuration, session settings, upload
restrictions, and environment configuration.

Important configuration constants include:

- DB_HOST
- DB_NAME
- DB_USER
- DB_PASS
- DB_CHARSET
- APP_NAME
- APP_URL
- APP_ROOT
- PUBLIC_URL
- SESSION_NAME
- SESSION_LIFETIME
- ENVIRONMENT

Do not commit real database passwords or other secrets
to a public repository.

The current configuration uses PHP constants rather
than environment variables loaded from a .env file.

## 5. Database Connection

Database connection logic is located in:

    app/config/database.php

The application uses PDO.

The connection configuration enables exception-based
error handling and associative fetch results.
Emulated prepared statements are disabled.

Use prepared statements for database operations.
Avoid building SQL queries by concatenating untrusted
user input.

## 6. Routing

Application routes are defined in:

    routes/web.php

Routes connect HTTP methods and URL paths to application
actions.

Examples of route groups include:

- Authentication.
- Dashboard.
- Products and categories.
- Suppliers and customers.
- Purchases and sales.
- Inventory and stock movements.
- Reports.
- User management.
- Profile and settings.

When adding a route:

1. Identify the correct application module.
2. Choose the appropriate HTTP method.
3. Connect the route to the intended controller action.
4. Apply the required authentication and authorization checks.
5. Validate incoming data.
6. Test successful and unsuccessful requests.

Use POST for state-changing operations rather than
performing changes through ordinary GET requests.

## 7. Models and Controllers

Controllers handle application requests and coordinate
the required operations.

Models handle application data and database operations.

Keep responsibilities separated:

- Controllers should coordinate application workflows.
- Models should manage data operations.
- Views should present information.
- Helpers should provide reusable functionality.

Avoid placing large amounts of database logic directly
inside view templates.

## 8. Views and Layouts

Views are stored under:

    app/views/

Shared application layouts are located in:

    app/views/layouts/

Authentication pages use the authentication layout.
Authenticated application pages use the main application
layout.

When modifying shared layouts, check all pages that
depend on them.

Escape dynamic output before displaying user-controlled
or database-provided text in HTML.

The project provides the e() helper for HTML escaping.

## 9. Security Practices

Security must be considered whenever application
functionality is added or changed.

### Authentication

Use the application's established authentication
mechanisms. Passwords should be handled with PHP's
password hashing and verification functions.

### Authorization

Verify that users can access the requested resource
and perform the requested operation.

Do not rely only on hiding interface controls.

### CSRF protection

Use the application's CSRF token helpers for
state-changing form submissions.

Relevant helpers include:

- csrf_token()
- csrf_field()

### SQL injection prevention

Use PDO prepared statements with bound parameters.

Do not insert untrusted input directly into SQL strings.

### Output escaping

Use e() when rendering dynamic text in HTML contexts.

### Business data isolation

StockSync uses business identifiers to separate
business records.

Preserve the correct business scope in queries and
data operations. Never assume that an authenticated
user may access every business record.

### Configuration protection

Keep database credentials and private configuration
outside public web-accessible directories.

Review server configuration before deployment.

## 10. Database Changes

The database schema is supplied in:

    database.sql

Before changing the schema:

1. Back up the existing database.
2. Inspect the affected tables and relationships.
3. Plan how existing records will be preserved.
4. Apply the change in a development environment first.
5. Test related application modules.
6. Document the schema change.

Do not casually re-import database.sql into an existing
database containing important records.

Document significant schema changes in the database
documentation and changelog.

## 11. Frontend Development

Public frontend assets are located under:

    public/assets/

The application uses Bootstrap, Bootstrap Icons,
and Chart.js through external CDN references.

When changing interface styles:

- Preserve responsive behavior.
- Check shared layouts and components.
- Keep colors and typography consistent.
- Test forms, navigation, tables, and charts.
- Check both desktop and smaller screen sizes.

## 12. Development and Testing Workflow

Before changing application code:

1. Inspect the existing implementation.
2. Identify affected files and dependencies.
3. Back up important files before risky edits.
4. Make focused changes.
5. Run PHP syntax checks on modified PHP files.
6. Test the affected application workflow.
7. Review application and server logs.
8. Update documentation when behavior changes.

Example PHP syntax check:

    php -l app/controllers/ExampleController.php

Replace the example path with the actual modified file.

Never assume that a successful syntax check proves
that an application workflow works correctly.

## 13. Error Diagnosis

For Apache deployments, inspect the configured
Apache error log.

For the local StockSync virtual host:

    sudo tail -n 50 /var/log/apache2/stocksync_error.log

Check the database service when database connectivity
fails:

    sudo systemctl status mariadb

Review PHP errors and application logs using the
configured development or production settings.

Avoid displaying sensitive error details to public users.

## 14. Contribution Guidelines

Developers working on StockSync should:

- Preserve existing application behavior.
- Follow the established project structure.
- Use clear and consistent naming.
- Validate all incoming data.
- Apply authentication and authorization checks.
- Protect business data boundaries.
- Avoid committing credentials and local backups.
- Test changes before deployment.
- Update relevant documentation.

## 15. Project Ownership and Third-Party Software

StockSync is proprietary software.

Copyright © 2026 NIYIGENA Kevin.
Developed under the XEVENTION brand.

XEVENTION is a brand name used in connection with
the project and is not represented as a registered
legal entity by this documentation.

Third-party technologies and components remain
subject to their respective licenses.

Refer to LICENSE, NOTICE, and
THIRD_PARTY_LICENSES.md for additional information.

---

StockSync
Developer Guide
All rights reserved.

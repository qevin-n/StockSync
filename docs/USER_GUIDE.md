# StockSync — User Guide

Copyright © 2026 NIYIGENA Kevin.
Developed under the XEVENTION brand.

## 1. Introduction

StockSync is an inventory and stock management application
designed to help businesses manage products, suppliers,
customers, purchases, sales, and stock records.

This guide introduces the main application modules and
explains their intended use.

## 2. Accessing StockSync

Open StockSync using the application URL provided by your
system administrator.

For the local development configuration:

    http://stocksync.local

Users must sign in with their registered account.

## 3. Account Access

### Login

Enter your registered email address and password to access
your business workspace.

### Registration

Use the registration page to create an account and establish
a business workspace, where registration is enabled.

### Password Recovery

Use the forgot-password option if you cannot access your account.

Password recovery requires the application's email delivery
functionality to be configured.

### Profile

The profile section allows users to access their account
information and available profile settings.

## 4. Dashboard

The dashboard provides an overview of business activity.

It may display information such as:

- Product and inventory statistics.
- Sales and purchase summaries.
- Stock-related indicators.
- Sales chart and business activity.

Use the dashboard as a starting point for monitoring
business operations.

## 5. Product Management

The Products module is used to maintain the business product
catalogue.

Users with the required permissions can:

- View registered products.
- Add new products.
- View product details.
- Edit product information.
- Delete products.

Product records may include:

- Product name and SKU.
- Category.
- Description and unit.
- Purchase and selling prices.
- Available quantity.
- Minimum stock level.
- Supplier association.
- Product status.

Use unique SKUs for products within the same business.

## 6. Category Management

Categories help organize products into meaningful groups.

Users with the required permissions can:

- View categories.
- Create categories.
- Update category information.
- Delete categories.

## 7. Supplier Management

The Suppliers module stores information about businesses
or individuals that supply products.

Supplier records can include:

- Company name.
- Contact person.
- Email address.
- Phone number.
- Address.
- Website.
- Notes.
- Active or inactive status.

Use supplier records to keep purchasing information organized.

## 8. Customer Management

The Customers module stores customer information.

Customer records can include:

- Customer name.
- Email address.
- Phone number.
- Address.
- Notes.

Customer records can be associated with sales transactions.

## 9. Purchase Management

The Purchases module records incoming stock purchases.

A purchase can contain:

- Supplier information.
- Purchase number and date.
- Purchased products and quantities.
- Unit prices and item totals.
- Tax and discount amounts.
- Final purchase amount.
- Purchase status.
- Notes.

The application supports purchase statuses including:

- Pending
- Received
- Cancelled

Where available, use the receive action to record a completed
purchase and the cancel action to cancel an eligible purchase.

Review purchase details before changing a purchase status.

## 10. Sales Management

The Sales module records sales transactions.

A sale can contain:

- Customer information.
- Sale number and date.
- Products and quantities.
- Unit prices and item totals.
- Tax and discount amounts.
- Final sale amount.
- Sale status.
- Payment status.
- Notes.

Sale statuses include:

- Completed
- Pending
- Cancelled

Payment statuses include:

- Paid
- Unpaid
- Partial

Users can view sale details and access the invoice route
where available.

Verify transaction information before completing a sale.

## 11. Inventory Management

The Inventory module provides access to stock information.

Users can review inventory records and stock movements.

Stock movement types include:

- In — stock entering inventory.
- Out — stock leaving inventory.
- Adjustment — a recorded stock correction.

The stock adjustment function should be used carefully.
Record an appropriate explanation for adjustments where
the application provides a notes field.

## 12. Reports

The Reports module provides access to business reports,
including:

- General reports.
- Sales reports.
- Purchase reports.
- Stock reports.

Use reports to review business activity and support
inventory and operational decisions.

## 13. User Management

The Users module provides account administration features
for users with the required permissions.

Available routes include:

- User listing.
- User creation.
- User details.
- User editing.
- User deletion.
- Profile management.

The application includes Admin, Manager, and Staff role
records. Available actions depend on the permissions
implemented for each role.

## 14. Settings

The Settings module provides access to the application's
available business or system configuration options.

Review settings carefully before saving changes.

## 15. Good Operating Practices

- Keep product information accurate.
- Use unique product SKUs within each business.
- Maintain current supplier and customer information.
- Review purchase and sales details before confirming actions.
- Record stock adjustments accurately.
- Review stock levels regularly.
- Protect account credentials.
- Sign out when finished using a shared computer.
- Keep regular database backups.

## 16. Troubleshooting

### Cannot sign in

Check the email address and password. If necessary,
use the password recovery feature.

### A page does not load

Check the application URL, web server status, and
application logs.

### Database connection error

Check the database service and the database settings
in the application configuration.

### Missing or unexpected stock information

Review the related product, purchase, sale, and stock
movement records.

For technical assistance, contact the project maintainer.

---

StockSync
Copyright © 2026 NIYIGENA Kevin.
Developed under the XEVENTION brand.
All rights reserved.

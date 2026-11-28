# StockSync — Database Documentation

Copyright © 2026 NIYIGENA Kevin.
Developed under the XEVENTION brand.

## 1. Overview

StockSync uses MySQL/MariaDB for persistent application data.

The database schema is defined in:

    database.sql

The schema contains 13 tables:

1. businesses
2. roles
3. users
4. password_resets
5. categories
6. suppliers
7. products
8. customers
9. purchases
10. purchase_items
11. sales
12. sale_items
13. stock_movements

All tables use the InnoDB storage engine and utf8mb4
character encoding with utf8mb4_unicode_ci collation.

## 2. Database Design

The database is organized around business workspaces.

A business can have multiple users and maintain its own
products, categories, suppliers, customers, purchases,
sales, and stock movement records.

The roles table defines the available role records.

Purchase and sales transactions use separate item tables
to represent the individual products in each transaction.

## 3. Table Definitions

### 3.1 businesses

Stores business workspace information.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| name | VARCHAR(150) | Business name |
| slug | VARCHAR(150) | Unique business identifier |
| email | VARCHAR(150) | Business email |
| phone | VARCHAR(30) | Business phone |
| address | TEXT | Business address |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

The slug has a unique constraint.

### 3.2 roles

Stores application role definitions.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| name | VARCHAR(50) | Role identifier |
| display_name | VARCHAR(50) | Human-readable role name |
| created_at | TIMESTAMP | Creation timestamp |

The schema initially inserts these roles:

- admin — Administrator
- manager — Manager
- staff — Staff

### 3.3 users

Stores user accounts associated with businesses.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| business_id | INT | Associated business |
| role_id | INT | Assigned role |
| name | VARCHAR(100) | User name |
| email | VARCHAR(150) | User email |
| password | VARCHAR(255) | Password hash |
| is_active | TINYINT(1) | Account status |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

Constraints:

- business_id references businesses.id.
- role_id references roles.id.
- Email is unique within each business.

The database does not enforce a globally unique email
across all businesses.

### 3.4 password_resets

Stores password recovery records.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| email | VARCHAR(150) | Account email |
| token | VARCHAR(255) | Reset token |
| expires_at | TIMESTAMP | Token expiration time |
| created_at | TIMESTAMP | Record creation time |

An index named idx_token is defined on token.

The table does not declare a foreign key to users.

### 3.5 categories

Stores product categories for each business.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| business_id | INT | Associated business |
| name | VARCHAR(100) | Category name |
| description | TEXT | Category description |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

business_id references businesses.id.

### 3.6 suppliers

Stores supplier contact and business information.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| business_id | INT | Associated business |
| company_name | VARCHAR(150) | Supplier company |
| contact_person | VARCHAR(100) | Contact person |
| email | VARCHAR(150) | Email address |
| phone | VARCHAR(30) | Phone number |
| address | TEXT | Supplier address |
| website | VARCHAR(255) | Website |
| notes | TEXT | Additional notes |
| status | ENUM | active or inactive |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

business_id references businesses.id.

### 3.7 products

Stores product catalogue and inventory information.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| business_id | INT | Associated business |
| category_id | INT | Optional category |
| sku | VARCHAR(50) | Stock keeping unit |
| name | VARCHAR(150) | Product name |
| description | TEXT | Product description |
| unit | VARCHAR(30) | Product unit |
| purchase_price | DECIMAL(12,2) | Purchase price |
| selling_price | DECIMAL(12,2) | Selling price |
| quantity | INT | Current quantity |
| min_stock_level | INT | Minimum stock threshold |
| supplier_id | INT | Optional supplier |
| status | ENUM | active or inactive |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

Constraints:

- business_id references businesses.id.
- category_id references categories.id.
- supplier_id references suppliers.id.
- SKU is unique within each business.

Price fields use DECIMAL(12,2) for monetary values.

### 3.8 customers

Stores customer information.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| business_id | INT | Associated business |
| name | VARCHAR(100) | Customer name |
| email | VARCHAR(150) | Email address |
| phone | VARCHAR(30) | Phone number |
| address | TEXT | Customer address |
| notes | TEXT | Additional notes |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

business_id references businesses.id.

### 3.9 purchases

Stores purchase transaction headers.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| business_id | INT | Associated business |
| supplier_id | INT | Optional supplier |
| purchase_number | VARCHAR(50) | Purchase reference |
| purchase_date | DATE | Purchase date |
| total_amount | DECIMAL(12,2) | Total before adjustments |
| tax_amount | DECIMAL(12,2) | Tax amount |
| discount_amount | DECIMAL(12,2) | Discount amount |
| final_amount | DECIMAL(12,2) | Final purchase amount |
| status | ENUM | pending, received, cancelled |
| notes | TEXT | Additional notes |
| created_by | INT | Creating user |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

Foreign keys:

- business_id references businesses.id.
- supplier_id references suppliers.id.
- created_by references users.id.

### 3.10 purchase_items

Stores individual products included in purchases.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| purchase_id | INT | Parent purchase |
| product_id | INT | Purchased product |
| quantity | INT | Purchased quantity |
| unit_price | DECIMAL(12,2) | Price per unit |
| total_price | DECIMAL(12,2) | Item total |
| created_at | TIMESTAMP | Creation timestamp |

Foreign keys:

- purchase_id references purchases.id.
- product_id references products.id.

Deleting a purchase cascades to its purchase items.
Deleting a referenced product is restricted while
purchase items reference it.

### 3.11 sales

Stores sales transaction headers.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| business_id | INT | Associated business |
| customer_id | INT | Optional customer |
| sale_number | VARCHAR(50) | Sale reference |
| sale_date | DATE | Sale date |
| total_amount | DECIMAL(12,2) | Total before adjustments |
| tax_amount | DECIMAL(12,2) | Tax amount |
| discount_amount | DECIMAL(12,2) | Discount amount |
| final_amount | DECIMAL(12,2) | Final sale amount |
| status | ENUM | completed, pending, cancelled |
| payment_status | ENUM | paid, unpaid, partial |
| notes | TEXT | Additional notes |
| created_by | INT | Creating user |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

Foreign keys:

- business_id references businesses.id.
- customer_id references customers.id.
- created_by references users.id.

### 3.12 sale_items

Stores individual products included in sales.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| sale_id | INT | Parent sale |
| product_id | INT | Sold product |
| quantity | INT | Sold quantity |
| unit_price | DECIMAL(12,2) | Price per unit |
| total_price | DECIMAL(12,2) | Item total |
| created_at | TIMESTAMP | Creation timestamp |

Foreign keys:

- sale_id references sales.id.
- product_id references products.id.

Deleting a sale cascades to its sale items.
Deleting a referenced product is restricted while
sale items reference it.

### 3.13 stock_movements

Stores records of stock movement activity.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| business_id | INT | Associated business |
| product_id | INT | Related product |
| type | ENUM | in, out, adjustment |
| quantity | INT | Movement quantity |
| reference_type | VARCHAR(50) | Reference category |
| reference_id | INT | Related record identifier |
| notes | TEXT | Movement explanation |
| created_by | INT | Recording user |
| created_at | TIMESTAMP | Creation timestamp |

Foreign keys:

- business_id references businesses.id.
- product_id references products.id.
- created_by references users.id.

The reference_type and reference_id fields do not
declare foreign-key constraints in the supplied schema.

## 4. Relationships and Foreign Keys

The main relationships are:

- businesses 1-to-many users.
- businesses 1-to-many categories.
- businesses 1-to-many suppliers.
- businesses 1-to-many products.
- businesses 1-to-many customers.
- businesses 1-to-many purchases.
- businesses 1-to-many sales.
- businesses 1-to-many stock_movements.
- roles 1-to-many users.
- categories 1-to-many products.
- suppliers 1-to-many products.
- suppliers 1-to-many purchases.
- customers 1-to-many sales.
- users 1-to-many purchases.
- users 1-to-many sales.
- users 1-to-many stock_movements.
- purchases 1-to-many purchase_items.
- sales 1-to-many sale_items.
- products 1-to-many purchase_items.
- products 1-to-many sale_items.
- products 1-to-many stock_movements.

## 5. Foreign-Key Deletion Rules

The schema defines three deletion behaviors.

### ON DELETE CASCADE

Deleting a referenced parent automatically deletes
the dependent records.

Examples:

- Deleting a business cascades to its directly
  dependent business records.
- Deleting a purchase cascades to purchase_items.
- Deleting a sale cascades to sale_items.
- Deleting a product cascades to stock_movements.

### ON DELETE SET NULL

Deleting a referenced record sets the dependent
foreign-key value to NULL.

Examples:

- Deleting a category sets products.category_id to NULL.
- Deleting a supplier sets products.supplier_id and
  purchases.supplier_id to NULL.
- Deleting a customer sets sales.customer_id to NULL.
- Deleting a user sets created_by to NULL in purchases,
  sales, and stock_movements.

### ON DELETE RESTRICT

Prevents deletion while dependent records exist.

Examples:

- A role cannot be deleted while users reference it.
- A product cannot be deleted while purchase_items
  or sale_items reference it.

## 6. Business Data Separation

Most business-owned records contain business_id.

Application queries must preserve business boundaries.

Developers should verify that:

- A user accesses records belonging to their business.
- A product selected for a purchase belongs to the
  purchase's business.
- A product selected for a sale belongs to the
  sale's business.
- A supplier or customer belongs to the same business
  as the transaction using it.
- Stock movements use the correct business and product.

The supplied foreign keys do not independently enforce
all these cross-business consistency rules.

Application-level validation is therefore important.

## 7. Transaction Data Organization

### Purchases

The purchases table stores the transaction header.

The purchase_items table stores the individual products,
quantities, unit prices, and item totals.

### Sales

The sales table stores the transaction header.

The sale_items table stores the individual products,
quantities, unit prices, and item totals.

### Inventory

The products table stores the current quantity.

The stock_movements table records stock movement events.

Developers should inspect the application logic to
understand exactly when product quantities are updated
and how purchase and sale status changes affect inventory.

## 8. Indexes and Unique Constraints

The supplied schema explicitly defines:

- Primary keys on all 13 tables.
- Unique businesses.slug.
- Unique users (business_id, email).
- Unique products (business_id, sku).
- Index password_resets.token.

InnoDB also creates indexes needed for foreign-key
constraints where required.

## 9. Database Connection

The database connection is configured in:

    app/config/config.php

The PDO connection implementation is located in:

    app/config/database.php

The application uses the configured database host,
database name, username, and password.

Keep credentials private.

## 10. Backup and Maintenance

Before modifying the database:

1. Create a backup.
2. Verify that the backup is readable.
3. Test schema changes in a development environment.
4. Preserve important existing records.
5. Check application compatibility after changes.

Example database backup command:

    mariadb-dump -u stocksync_user -p stocksync > stocksync_backup.sql

Store backups securely. Do not commit database backups
containing real business or user information to a public
source code repository.

## 11. Schema Maintenance

When adding or changing tables:

- Update database.sql.
- Review primary and foreign keys.
- Review deletion behavior.
- Consider business data isolation.
- Check related models and controllers.
- Test affected application workflows.
- Update this document.
- Record significant changes in CHANGELOG.md.

## 12. Important Implementation Notes

- Monetary fields use DECIMAL(12,2).
- Product and transaction quantities use INT.
- Product SKUs are unique per business.
- User emails are unique per business.
- password_resets has no declared user foreign key.
- Stock movement references are stored as reference_type
  and reference_id without a declared foreign key.
- Cross-business consistency requires application checks.
- The supplied schema does not define every possible
  business rule or transaction workflow.

---

StockSync
Database Documentation
Copyright © 2026 NIYIGENA Kevin.
Developed under the XEVENTION brand.
All rights reserved.

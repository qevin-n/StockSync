<?php
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProductController;
use App\Controllers\CategoryController;
use App\Controllers\SupplierController;
use App\Controllers\CustomerController;
use App\Controllers\PurchaseController;
use App\Controllers\SaleController;
use App\Controllers\InventoryController;
use App\Controllers\ReportController;
use App\Controllers\UserController;
use App\Controllers\SettingController;

return [
    // Auth
    ['GET|POST', '/login', [AuthController::class, 'login']],
    ['GET|POST', '/register', [AuthController::class, 'register']],
    ['GET|POST', '/forgot-password', [AuthController::class, 'forgotPassword']],
    ['GET|POST', '/reset-password', [AuthController::class, 'resetPassword']],
    ['GET', '/logout', [AuthController::class, 'logout']],

    // Dashboard
    ['GET', '/dashboard', [DashboardController::class, 'index']],
    ['GET', '/', [DashboardController::class, 'index']],

    // Products
    ['GET', '/products', [ProductController::class, 'index']],
    ['GET|POST', '/products/create', [ProductController::class, 'create']],
    ['GET', '/products/{id}', [ProductController::class, 'show']],
    ['GET|POST', '/products/{id}/edit', [ProductController::class, 'edit']],
    ['POST', '/products/{id}/delete', [ProductController::class, 'delete']],

    // Categories
    ['GET', '/categories', [CategoryController::class, 'index']],
    ['POST', '/categories/create', [CategoryController::class, 'create']],
    ['POST', '/categories/{id}/update', [CategoryController::class, 'update']],
    ['POST', '/categories/{id}/delete', [CategoryController::class, 'delete']],

    // Suppliers
    ['GET', '/suppliers', [SupplierController::class, 'index']],
    ['GET|POST', '/suppliers/create', [SupplierController::class, 'create']],
    ['GET', '/suppliers/{id}', [SupplierController::class, 'show']],
    ['GET|POST', '/suppliers/{id}/edit', [SupplierController::class, 'edit']],
    ['POST', '/suppliers/{id}/delete', [SupplierController::class, 'delete']],

    // Customers
    ['GET', '/customers', [CustomerController::class, 'index']],
    ['GET|POST', '/customers/create', [CustomerController::class, 'create']],
    ['GET', '/customers/{id}', [CustomerController::class, 'show']],
    ['GET|POST', '/customers/{id}/edit', [CustomerController::class, 'edit']],
    ['POST', '/customers/{id}/delete', [CustomerController::class, 'delete']],

    // Purchases
    ['GET', '/purchases', [PurchaseController::class, 'index']],
    ['GET|POST', '/purchases/create', [PurchaseController::class, 'create']],
    ['GET', '/purchases/{id}', [PurchaseController::class, 'show']],
    ['POST', '/purchases/{id}/receive', [PurchaseController::class, 'receive']],
    ['POST', '/purchases/{id}/cancel', [PurchaseController::class, 'cancel']],

    // Sales
    ['GET', '/sales', [SaleController::class, 'index']],
    ['GET|POST', '/sales/create', [SaleController::class, 'create']],
    ['GET', '/sales/{id}', [SaleController::class, 'show']],
    ['GET', '/sales/{id}/invoice', [SaleController::class, 'invoice']],

    // Inventory
    ['GET', '/inventory', [InventoryController::class, 'index']],
    ['GET', '/inventory/stock-movements', [InventoryController::class, 'stockMovements']],
    ['POST', '/inventory/{id}/adjust', [InventoryController::class, 'adjust']],

    // Reports
    ['GET', '/reports', [ReportController::class, 'index']],
    ['GET', '/reports/sales', [ReportController::class, 'sales']],
    ['GET', '/reports/purchases', [ReportController::class, 'purchases']],
    ['GET', '/reports/stock', [ReportController::class, 'stock']],

    // Users
    ['GET', '/users', [UserController::class, 'index']],
    ['GET|POST', '/users/create', [UserController::class, 'create']],
    ['GET', '/users/{id}', [UserController::class, 'show']],
    ['GET|POST', '/users/{id}/edit', [UserController::class, 'edit']],
    ['POST', '/users/{id}/delete', [UserController::class, 'delete']],
    ['GET|POST', '/profile', [UserController::class, 'profile']],

    // Settings
    ['GET|POST', '/settings', [SettingController::class, 'index']],
];

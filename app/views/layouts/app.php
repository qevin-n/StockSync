<?php
/*
==============================================================================
                    STOCKSYNC | THE CREATOR'S NOTE
==============================================================================

  CREATED BY       : NIYIGENA Kevin
  PROJECT          : StockSync
  CLASSIFICATION   : Developer documentation
  PURPOSE          : Inventory and business management

  THE IDEA
  --------
  Every business deserves a clearer picture of what it owns, what it sells,
  what it purchases, and what it needs.

  THE MISSION
  -----------
  Make stock management more organized, reliable, understandable, and
  accessible. Turn everyday business operations into information that
  people can actually use to make better decisions.

  THE CREATOR'S INTENTION
  -----------------------
  This project is more than a collection of PHP files and database tables.
  It is an opportunity to turn knowledge into something useful, to solve
  practical problems through technology, and to keep learning by building.

  THE DESIGN PHILOSOPHY
  --------------------
  Clarity over clutter.
  Reliability over shortcuts.
  Useful features over unnecessary complexity.
  Progress through continuous improvement.

  A NOTE TO FUTURE DEVELOPERS
  ---------------------------
  Respect the work that came before you. Understand the logic before changing
  it. Improve the system without compromising its reliability.

  Built with curiosity. Improved with persistence. Designed with purpose.

  "Good software should make difficult work feel simpler."

==============================================================================
  This is a source-code comment, not a security or confidentiality boundary.
==============================================================================
*/


$currentUser = (new \App\Models\User())->findById((int) ($_SESSION['user_id'] ?? 0));
$currentBusiness = $currentUser ? (new \App\Models\Business())->findById($currentUser['business_id']) : null;
$unreadCount = (new \App\Models\Notification())->getUnreadCount((int) ($_SESSION['business_id'] ?? 0), (int) ($_SESSION['user_id'] ?? 0));
$currency = $currentBusiness['currency'] ?? '$';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>StockSync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/main.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="/dashboard" class="brand-link">
                <i class="bi bi-box-seam-fill"></i>
                <span class="brand-text">StockSync</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="/dashboard" class="nav-link<?= current_route('/dashboard') ? ' active' : '' ?>">
                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
            </a>
            <a href="/products" class="nav-link<?= current_route('/products') ? ' active' : '' ?>">
                <i class="bi bi-box"></i><span>Products</span>
            </a>
            <a href="/categories" class="nav-link<?= current_route('/categories') ? ' active' : '' ?>">
                <i class="bi bi-tags"></i><span>Categories</span>
            </a>
            <a href="/inventory" class="nav-link<?= current_route('/inventory') ? ' active' : '' ?>">
                <i class="bi bi-boxes"></i><span>Inventory</span>
            </a>
            <a href="/sales" class="nav-link<?= current_route('/sales') ? ' active' : '' ?>">
                <i class="bi bi-cart"></i><span>Sales</span>
            </a>
            <a href="/purchases" class="nav-link<?= current_route('/purchases') ? ' active' : '' ?>">
                <i class="bi bi-bag"></i><span>Purchases</span>
            </a>
            <a href="/customers" class="nav-link<?= current_route('/customers') ? ' active' : '' ?>">
                <i class="bi bi-people"></i><span>Customers</span>
            </a>
            <a href="/suppliers" class="nav-link<?= current_route('/suppliers') ? ' active' : '' ?>">
                <i class="bi bi-truck"></i><span>Suppliers</span>
            </a>
            <a href="/reports" class="nav-link<?= current_route('/reports') ? ' active' : '' ?>">
                <i class="bi bi-graph-up"></i><span>Reports</span>
            </a>
            <?php if (is_admin()): ?>
            <a href="/users" class="nav-link<?= current_route('/users') ? ' active' : '' ?>">
                <i class="bi bi-person-gear"></i><span>Users</span>
            </a>
            <a href="/settings" class="nav-link<?= current_route('/settings') ? ' active' : '' ?>">
                <i class="bi bi-gear"></i><span>Settings</span>
            </a>
            <?php endif; ?>
        </nav>

    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <?php if (!empty($currentBusiness['name'])): ?>
                <div class="company-identity">
                    <span class="company-identity-icon">
                        <i class="bi bi-building"></i>
                    </span>
                    <span class="company-identity-details">
                        <span class="company-identity-label">BUSINESS WORKSPACE</span>
                        <span class="company-identity-name"><?= e($currentBusiness['name']) ?></span>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="#" class="nav-icon position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem;"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" style="width: 280px; max-height: 400px; overflow-y: auto;">
                    <li class="dropdown-header">Notifications</li>
                    <li><hr class="dropdown-divider"></li>
                    <?php
                    $notifications = (new \App\Models\Notification())->getUnread($currentUser['business_id'] ?? 0, $currentUser['id'] ?? 0, 10);
                    if (empty($notifications)): ?>
                        <li class="dropdown-item text-muted">No new notifications</li>
                    <?php else: foreach ($notifications as $n): ?>
                        <li class="dropdown-item">
                            <small class="fw-semibold"><?= e($n['title']) ?></small>
                            <div class="text-muted small"><?= e($n['message']) ?></div>
                        </li>
                    <?php endforeach; endif; ?>
                </ul>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center gap-2 text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="<?= $currentUser['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($currentUser['name'] ?? 'User') . '&background=2563EB&color=fff' ?>" class="rounded-circle" width="32" height="32" alt="Avatar">
                        <span class="d-none d-md-inline small fw-medium"><?= e($currentUser['name'] ?? 'User') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header"><?= e($currentUser['name'] ?? 'User') ?></li>
                        <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <?php if (is_admin()): ?>
                        <li><a class="dropdown-item" href="/settings"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php foreach (get_flash_messages() as $msg): ?>
            <div class="alert alert-<?= $msg['type'] ?> alert-dismissible fade show m-3" role="alert">
                <?= $msg['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>

        <!-- Page Content -->
        <main class="page-content">
            <?php if (isset($pageTitle)): ?>
                <div class="page-header d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0 fw-semibold"><?= e($pageTitle) ?></h4>

                </div>
            <?php endif; ?>
<?php
            if ($content instanceof \Closure) {
                $content();
            } else {
                echo $content ?? '';
            }
        ?>
        </main>

        <footer class="app-footer">
            <span>&copy; <?= date('Y') ?> StockSync. All rights reserved.</span>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
<?php if (!empty($chartScripts)) echo $chartScripts; ?>
</body>
</html>

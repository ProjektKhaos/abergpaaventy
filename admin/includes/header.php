<?php
// admin/includes/header.php – gemensam header för adminpanelen Ⓐ Style

// Starta adminmiljön om sidan inte redan gjort det.
if (!isset($auth, $admin_modules)) {
    require_once __DIR__ . '/../bootstrap.php';
}

// Aktiv sida för navigation
$current_page = basename($_SERVER['PHP_SELF']);
$active_module = admin_active_module($current_page, $admin_modules);
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= isset($page_title) ? e($page_title) . ' – Admin' : 'Admin – Hasse i Thailand' ?></title>
  <link rel="stylesheet" href="<?= url('admin/assets/admin.css') ?>">
</head>
<body>

  <header class="admin-header">
    <span class="admin-header__logo">🇹🇭 Admin – <span>Thailand-bloggen</span></span>
    <nav>
      <ul class="admin-nav">
        <?php foreach ($admin_modules as $key => $module): ?>
        <li><a href="<?= e($module['url']) ?>"
               class="<?= $active_module === $key ? 'active' : '' ?>">
          <?= e($module['label']) ?>
        </a></li>
        <?php endforeach; ?>
        <li><a href="<?= url() ?>" target="_blank">↗ Sidan</a></li>
        <li><a href="<?= url('admin/logout.php') ?>">Logga ut</a></li>
      </ul>
    </nav>
  </header>

  <main class="admin-main">

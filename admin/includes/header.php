<?php
// admin/includes/header.php – gemensam header för adminpanelen Ⓐ Style

// Starta session och kontrollera inloggning
if (!defined('NO_AUTH')) {
    require_once __DIR__ . '/../../app/config.php';
    require_once __DIR__ . '/../../app/db.php';
    require_once __DIR__ . '/../../app/helpers.php';
    require_once __DIR__ . '/../../app/Auth.php';

    $auth = new Auth($pdo);
    $auth->require_login();
}

// Aktiv sida för navigation
$current_page = basename($_SERVER['PHP_SELF']);
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
        <li><a href="<?= url('admin/dashboard.php') ?>"
               class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
          Dashboard
        </a></li>
        <li><a href="<?= url('admin/posts.php') ?>"
               class="<?= in_array($current_page, ['posts.php','post_edit.php']) ? 'active' : '' ?>">
          Inlägg
        </a></li>
        <li><a href="<?= url('admin/media.php') ?>"
               class="<?= $current_page === 'media.php' ? 'active' : '' ?>">
          Media
        </a></li>
        <li><a href="<?= url() ?>" target="_blank">↗ Sidan</a></li>
        <li><a href="<?= url('admin/logout.php') ?>">Logga ut</a></li>
      </ul>
    </nav>
  </header>

  <main class="admin-main">

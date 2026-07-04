<?php
// admin/login.php – inloggningssida för admin Ⓐ Style

define('NO_AUTH', true);

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/Auth.php';

session_start();

$auth = new Auth($pdo);

// Redan inloggad → skicka till dashboard
if ($auth->check()) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$error = '';

// Hantera inloggningsformulär
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($auth->login($username, $password)) {
        header('Location: ' . url('admin/dashboard.php'));
        exit;
    } else {
        $error = 'Fel användarnamn eller lösenord.';
    }
}
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Logga in – Admin</title>
  <link rel="stylesheet" href="<?= url('admin/assets/admin.css') ?>">
</head>
<body>

  <div class="login-wrap">
    <div class="login-box">

      <div class="login-box__logo">🇹🇭 <span>Admin</span></div>

      <?php if ($error): ?>
        <div class="alert alert--error"><?= e($error) ?></div>
      <?php endif; ?>

      <!-- Inloggningsformulär med CSRF-skydd -->
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" id="csrf_token">

        <div class="form-group">
          <label for="username">Användarnamn</label>
          <input
            type="text"
            id="username"
            name="username"
            class="form-control"
            autocomplete="username"
            value="<?= e($_POST['username'] ?? '') ?>"
            required
          >
        </div>

        <div class="form-group">
          <label for="password">Lösenord</label>
          <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            autocomplete="current-password"
            required
          >
        </div>

        <button type="submit" class="btn btn--primary" style="width:100%;">
          Logga in
        </button>
      </form>

    </div>
  </div>

</body>
</html>

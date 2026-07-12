<?php
// admin/index.php – ingång till adminpanelen Ⓐ Style

define('NO_AUTH', true);

require_once __DIR__ . '/bootstrap.php';

$target = $auth->check() ? 'admin/dashboard.php' : 'admin/login.php';

header('Location: ' . url($target));
exit;

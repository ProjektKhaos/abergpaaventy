<?php
// admin/logout.php – loggar ut admin och redirectar Ⓐ Style

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/db.php';

$auth = new Auth($pdo);
$auth->logout();

header('Location: ' . url('admin/login.php'));
exit;

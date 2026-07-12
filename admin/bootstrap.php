<?php
// bootstrap.php – gemensam startpunkt för adminpanelen Ⓐ Style

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Admin.php';
require_once __DIR__ . '/../app/Post.php';
require_once __DIR__ . '/../app/Media.php';
require_once __DIR__ . '/../app/Category.php';
require_once __DIR__ . '/../app/Tag.php';
require_once __DIR__ . '/../app/Settings.php';
require_once __DIR__ . '/../app/Services/ServiceResult.php';
require_once __DIR__ . '/../app/Services/MediaService.php';
require_once __DIR__ . '/../app/Services/PostService.php';

$auth = $auth ?? new Auth($pdo);

if (!defined('NO_AUTH')) {
    $auth->require_login();
}

$admin_modules = admin_modules();

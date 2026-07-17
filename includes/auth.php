<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['admin_id'])) {
    redirect('/kadai09_auth/admin/login.php');
}

$timeout = 1800; // タイムアウトまでの秒数（30分）
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    $_SESSION = [];
    session_destroy();
    redirect('/kadai09_auth/admin/login.php');
}
$_SESSION['last_activity'] = time();

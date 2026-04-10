<?php
define('ROOT', __DIR__);
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: /install/');
    exit;
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$page = trim($_GET['page'] ?? 'home');
$page = preg_replace('/[^a-z0-9_-]/', '', $page);

$allowed = ['home','ürünler','hizmetler','hakkımızda','teklif','iletisim','kvkk','gizlilik','cerez','urun'];
if (!in_array($page, $allowed)) $page = '404';

$pageFile = __DIR__ . '/pages/' . $page . '.php';
if (!file_exists($pageFile)) { $page = '404'; $pageFile = __DIR__ . '/pages/404.php'; }

require $pageFile;

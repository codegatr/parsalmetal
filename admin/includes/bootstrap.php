<?php
// Admin sayfalarının tepesine eklenir - HTML çıktısından ÖNCE
if (!defined('ROOT')) define('ROOT', dirname(__DIR__, 2));
require_once ROOT . '/config.php';
require_once ROOT . '/includes/db.php';
require_once ROOT . '/includes/functions.php';
require_once ROOT . '/includes/auth.php';
requireLogin();

$admin    = currentAdmin();
$pdo      = getDB();
$flash    = getFlash();
$siteTitle = getSetting('site_title', 'Parsal Metal');

$currentMod = basename($_SERVER['PHP_SELF'], '.php');

// Okunmamış sayılar (sidebar için)
try {
    $newQuotes   = (int)$pdo->query('SELECT COUNT(*) FROM ' . p() . 'quotes WHERE status=\'new\'')->fetchColumn();
    $newContacts = (int)$pdo->query('SELECT COUNT(*) FROM ' . p() . 'contacts WHERE is_read=0')->fetchColumn();
} catch (Exception $e) {
    $newQuotes = $newContacts = 0;
}

<?php
if (!defined('ROOT')) define('ROOT', dirname(__DIR__, 2));
if (session_status() === PHP_SESSION_NONE) session_start();
require_once ROOT . '/config.php';
require_once ROOT . '/includes/db.php';
require_once ROOT . '/includes/functions.php';
require_once ROOT . '/includes/auth.php';
requireLogin();

$admin     = currentAdmin();
$pdo       = getDB();
$flash     = getFlash();
$siteTitle = getSetting('site_title', 'Parsal Metal');

try {
    $newQuotes   = (int)$pdo->query('SELECT COUNT(*) FROM ' . p() . 'quotes WHERE status=\'new\'')->fetchColumn();
    $newContacts = (int)$pdo->query('SELECT COUNT(*) FROM ' . p() . 'contacts WHERE is_read=0')->fetchColumn();
} catch (Exception $e) {
    $newQuotes = $newContacts = 0;
}

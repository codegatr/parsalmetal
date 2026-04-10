<?php
if (!defined('DB_HOST')) {
    $cfg = dirname(__DIR__) . '/config.php';
    if (file_exists($cfg)) require_once $cfg;
    else die('Kurulum tamamlanmamış. <a href="/install/">Kurulumu başlatın</a>.');
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

function prefix(): string {
    return defined('DB_PREFIX') ? DB_PREFIX : 'prs_';
}

function p(): string { return prefix(); }

<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . (defined('SITE_URL') ? SITE_URL : '') . '/admin/?action=login&ref=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function doLogin(string $username, string $password): bool {
    require_once __DIR__ . '/db.php';
    $pdo = getDB();
    $st  = $pdo->prepare('SELECT * FROM ' . p() . 'users WHERE username=? LIMIT 1');
    $st->execute([$username]);
    $user = $st->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id']       = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_name']     = $user['full_name'];
        $_SESSION['admin_role']     = $user['role'];
        $pdo->prepare('UPDATE ' . p() . 'users SET last_login=NOW() WHERE id=?')->execute([$user['id']]);
        return true;
    }
    return false;
}

function doLogout(): void {
    session_destroy();
    header('Location: /admin/');
    exit;
}

function currentAdmin(): array {
    return [
        'id'       => $_SESSION['admin_id']       ?? 0,
        'username' => $_SESSION['admin_username'] ?? '',
        'name'     => $_SESSION['admin_name']     ?? '',
        'role'     => $_SESSION['admin_role']     ?? '',
    ];
}

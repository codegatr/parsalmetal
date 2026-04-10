<?php
if (!function_exists('getDB')) require_once __DIR__ . '/db.php';

/* ---------- AYAR ---------- */
function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        try {
            $pdo = getDB();
            $st  = $pdo->prepare('SELECT setting_value FROM ' . p() . 'settings WHERE setting_key=?');
            $st->execute([$key]);
            $cache[$key] = $st->fetchColumn() ?? $default;
        } catch (Exception $e) {
            return $default;
        }
    }
    return (string)($cache[$key] ?? $default);
}

function saveSetting(string $key, string $value): void {
    $pdo = getDB();
    $pdo->prepare('INSERT INTO ' . p() . 'settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')
        ->execute([$key, $value]);
}

/* ---------- SLUG ---------- */
function slugify(string $text): string {
    $tr = ['ş'=>'s','Ş'=>'s','ı'=>'i','İ'=>'i','ğ'=>'g','Ğ'=>'g','ü'=>'u','Ü'=>'u','ö'=>'o','Ö'=>'o','ç'=>'c','Ç'=>'c'];
    $text = strtr($text, $tr);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return trim($text, '-');
}

/* ---------- GÜVENLİK ---------- */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function csrf(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(): bool {
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ---------- UPLOAD ---------- */
function uploadImage(array $file, string $dir, int $maxW = 1920) {
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!in_array($file['type'], $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid('img_') . '.' . strtolower($ext);
    $uploadDir = dirname(__DIR__) . '/assets/uploads/' . trim($dir, '/') . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $dest = $uploadDir . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;
    return '/assets/uploads/' . trim($dir, '/') . '/' . $name;
}

/* ---------- SAYFALAMA ---------- */
function paginate(int $total, int $perPage, int $current, string $url): string {
    $pages = (int)ceil($total / $perPage);
    if ($pages <= 1) return '';
    $html = '<div class="pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        $active = ($i === $current) ? ' active' : '';
        $html .= "<a href='{$url}page={$i}' class='page-btn{$active}'>{$i}</a>";
    }
    $html .= '</div>';
    return $html;
}

/* ---------- ZAMAN ---------- */
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)    return $diff . ' saniye önce';
    if ($diff < 3600)  return (int)($diff/60) . ' dakika önce';
    if ($diff < 86400) return (int)($diff/3600) . ' saat önce';
    return (int)($diff/86400) . ' gün önce';
}

/* ---------- HATA ---------- */
function flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

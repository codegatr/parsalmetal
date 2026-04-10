<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/config.php';
require_once ROOT . '/includes/db.php';
require_once ROOT . '/includes/functions.php';
require_once ROOT . '/includes/auth.php';

if (isLoggedIn()) { header('Location: /admin/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if ($u && $p && doLogin($u, $p)) {
        header('Location: /admin/dashboard.php');
        exit;
    }
    $error = 'Kullanici adi veya sifre hatali.';
}
$siteTitle = getSetting('site_title','Parsal Metal');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Girisi - <?= htmlspecialchars($siteTitle) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#0f1117;min-height:100vh;display:flex;align-items:center;justify-content:center}
.wrap{width:100%;max-width:400px;padding:20px}
.box{background:#1a1d24;border:1px solid #2a2d36;border-radius:16px;padding:40px}
.logo{text-align:center;margin-bottom:32px}
.logo-icon{width:52px;height:52px;background:#c0392b;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px}
.logo h1{font-size:20px;font-weight:800;color:#fff}
.logo p{font-size:12px;color:#555;margin-top:4px}
.form-group{margin-bottom:18px}
label{display:block;font-size:12px;font-weight:600;color:#888;margin-bottom:7px}
input{width:100%;padding:12px 14px;background:#111318;border:1.5px solid #2a2d36;border-radius:8px;color:#fff;font-size:14px;outline:none;transition:.2s}
input:focus{border-color:#c0392b;box-shadow:0 0 0 3px rgba(192,57,43,.15)}
.btn{width:100%;padding:13px;background:#c0392b;color:#fff;font-size:15px;font-weight:700;border:none;border-radius:8px;cursor:pointer;transition:.2s;margin-top:4px}
.btn:hover{background:#96281b}
.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:8px;padding:12px;color:#ef4444;font-size:13px;margin-bottom:20px;text-align:center}
.footer{text-align:center;margin-top:20px;font-size:12px;color:#333}
</style>
</head>
<body>
<div class="wrap">
  <div class="box">
    <div class="logo">
      <div class="logo-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><polygon points="12 2 22 19 2 19"/></svg>
      </div>
      <h1><?= htmlspecialchars($siteTitle) ?></h1>
      <p>Yonetim Paneli</p>
    </div>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Kullanici Adi</label>
        <input type="text" name="username" autofocus required autocomplete="username">
      </div>
      <div class="form-group">
        <label>Sifre</label>
        <input type="password" name="password" required autocomplete="current-password">
      </div>
      <button class="btn" type="submit">Giris Yap</button>
    </form>
  </div>
  <p class="footer">CODEGA &bull; Parsal CMS</p>
</div>
</body>
</html>

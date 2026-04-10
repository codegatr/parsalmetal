<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/config.php';
$pageTitle = 'Dashboard';
require_once ROOT . '/admin/includes/sidebar.php';
require_once ROOT . '/includes/db.php';
require_once ROOT . '/includes/functions.php';
$pdo = getDB();

$counts = [
  'products'  => $pdo->query('SELECT COUNT(*) FROM ' . p() . 'products WHERE is_active=1')->fetchColumn(),
  'quotes'    => $pdo->query('SELECT COUNT(*) FROM ' . p() . 'quotes')->fetchColumn(),
  'contacts'  => $pdo->query('SELECT COUNT(*) FROM ' . p() . 'contacts')->fetchColumn(),
  'new_quotes'=> $pdo->query('SELECT COUNT(*) FROM ' . p() . 'quotes WHERE status=\'new\'')->fetchColumn(),
];
$latestQuotes   = $pdo->query('SELECT * FROM ' . p() . 'quotes ORDER BY created_at DESC LIMIT 5')->fetchAll();
$latestContacts = $pdo->query('SELECT * FROM ' . p() . 'contacts ORDER BY created_at DESC LIMIT 5')->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>">
</head>
<body>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
  <div class="page-actions">
    <h1>Hos Geldiniz, <?= htmlspecialchars($admin['name'] ?: $admin['username']) ?> 👋</h1>
    <a href="/" target="_blank" class="btn btn-secondary">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Siteyi Gor
    </a>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon red">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
      </div>
      <div><div class="stat-num"><?= $counts['products'] ?></div><div class="stat-lbl">Aktif Urun</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon blue">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
      </div>
      <div><div class="stat-num"><?= $counts['quotes'] ?></div><div class="stat-lbl">Teklif Talebi</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
      </div>
      <div><div class="stat-num"><?= $counts['contacts'] ?></div><div class="stat-lbl">Iletisim Mesaji</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon yellow">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 110 20 10 10 0 010-20zm0 6v4l3 3"/></svg>
      </div>
      <div><div class="stat-num"><?= $counts['new_quotes'] ?></div><div class="stat-lbl">Yeni Teklif</div></div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <!-- Son Talepler -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">Son Teklif Talepleri</div>
        <a href="/admin/talepler.php" class="btn btn-sm btn-secondary">Tumunu Gor</a>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Ad</th><th>Telefon</th><th>Tarih</th><th>Durum</th></tr></thead>
          <tbody>
            <?php foreach ($latestQuotes as $q): ?>
            <tr>
              <td><strong><?= htmlspecialchars($q['name']) ?></strong><br><span style="font-size:11px;color:#888"><?= htmlspecialchars($q['company'] ?? '') ?></span></td>
              <td><?= htmlspecialchars($q['phone']) ?></td>
              <td style="font-size:12px;color:#888"><?= date('d.m.Y H:i', strtotime($q['created_at'])) ?></td>
              <td><span class="badge badge-<?= $q['status'] ?>"><?= ['new'=>'Yeni','read'=>'Okundu','replied'=>'Yanitlandi','closed'=>'Kapali'][$q['status']] ?? $q['status'] ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($latestQuotes)): ?>
            <tr><td colspan="4" style="text-align:center;color:#999;padding:24px">Henuz teklif talebi yok.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Son Mesajlar -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">Son Iletisim Mesajlari</div>
        <a href="/admin/mesajlar.php" class="btn btn-sm btn-secondary">Tumunu Gor</a>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Ad</th><th>Konu</th><th>Tarih</th></tr></thead>
          <tbody>
            <?php foreach ($latestContacts as $c): ?>
            <tr>
              <td><?= htmlspecialchars($c['name']) ?></td>
              <td><?= htmlspecialchars($c['subject'] ?? '-') ?></td>
              <td style="font-size:12px;color:#888"><?= date('d.m.Y', strtotime($c['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($latestContacts)): ?>
            <tr><td colspan="3" style="text-align:center;color:#999;padding:24px">Henuz mesaj yok.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Hizli Erisim -->
  <div style="margin-top:24px">
    <h2 style="font-size:15px;font-weight:700;margin-bottom:16px">Hizli Erisim</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px">
      <?php $shortcuts = [
        ['href'=>'/admin/slider.php','icon'=>'M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3M8 21H5a2 2 0 01-2-2v-3m18 0v3a2 2 0 01-2 2h-3','label'=>'Slider Ekle'],
        ['href'=>'/admin/urunler.php?action=add','icon'=>'M12 4v16m8-8H4','label'=>'Urun Ekle'],
        ['href'=>'/admin/hizmetler.php?action=add','icon'=>'M12 4v16m8-8H4','label'=>'Hizmet Ekle'],
        ['href'=>'/admin/ayarlar.php','icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0','label'=>'Ayarlar'],
        ['href'=>'/admin/guncelleme.php','icon'=>'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9','label'=>'Guncelleme'],
        ['href'=>'/admin/talepler.php','icon'=>'M8 10h.01M12 10h.01M16 10h.01M9 16H5','label'=>'Talepler'],
      ]; foreach ($shortcuts as $s): ?>
      <a href="<?= $s['href'] ?>" style="padding:18px 16px;background:#fff;border:1px solid #e2e5ea;border-radius:10px;display:flex;align-items:center;gap:10px;font-size:13px;font-weight:600;color:#1a1a2e;transition:.2s" onmouseover="this.style.borderColor='#c0392b'" onmouseout="this.style.borderColor='#e2e5ea'">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round"><path d="<?= $s['icon'] ?>"/></svg>
        <?= $s['label'] ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</main>
<?php require ROOT . '/admin/includes/footer.php'; ?>

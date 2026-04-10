<?php
require_once ROOT . '/includes/header.php';
$pdo = getDB();
$pg  = $pdo->query("SELECT * FROM " . p() . "pages WHERE slug='gizlilik'")->fetch();
$pageMetaTitle = ($pg['meta_title'] ?? '') ?: 'Gizlilik Politikasi - ' . getSetting('site_title');
$pageMetaDesc  = $pg['meta_description'] ?? '';
?>
<div class="page-hero">
  <div class="container">
    <div class="breadcrumb" style="margin-bottom:16px">
      <a href="/">Ana Sayfa</a><span style="margin:0 8px;opacity:.4">/</span><span><?= htmlspecialchars($pg['title'] ?? 'Gizlilik Politikasi') ?></span>
    </div>
    <h1><?= htmlspecialchars($pg['title'] ?? 'Gizlilik Politikasi') ?></h1>
  </div>
</div>
<section class="section">
  <div class="container">
    <div class="page-content">
      <?= $pg['content'] ?? '<p>Icerik henuz eklenmemistir.</p>' ?>
    </div>
    <div style="margin-top:40px;padding:20px;background:#f8f8f8;border-radius:10px;font-size:13px;color:#888">
      Son guncelleme: <?= isset($pg['updated_at']) ? date('d.m.Y', strtotime($pg['updated_at'])) : date('d.m.Y') ?>
    </div>
  </div>
</section>
<?php require_once ROOT . '/includes/footer.php'; ?>

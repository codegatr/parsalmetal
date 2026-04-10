<?php
require_once ROOT . '/includes/header.php';
$pdo = getDB();
$pg  = $pdo->query("SELECT * FROM " . p() . "pages WHERE slug='kvkk'")->fetch();
$pageMetaTitle = ($pg['meta_title'] ?? '') ?: 'KVKK Aydinlatma Metni - ' . getSetting('site_title');
$pageMetaDesc  = $pg['meta_description'] ?? '';
?>
    <h1><?= htmlspecialchars($pg['title'] ?? 'KVKK Aydinlatma Metni') ?></h1>
  </div>
</div>
<section class="section" style="margin-top:var(--header-h)">
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

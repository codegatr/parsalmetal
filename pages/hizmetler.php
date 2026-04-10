<?php
require_once ROOT . '/includes/header.php';
$pdo = getDB();

$slug = $_GET['slug'] ?? '';
$icons = [
  'settings'=>'<circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 0 1 21 12a10 10 0 0 1-1.93 7.07"/>',
  'tool'=>'<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
  'layers'=>'<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
  'headphones'=>'<path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3z"/><path d="M3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>',
  'star'=>'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
  'shield'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
  'award'=>'<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>',
  'truck'=>'<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
];

if ($slug) {
    $st = $pdo->prepare('SELECT * FROM ' . p() . 'services WHERE slug=? AND is_active=1');
    $st->execute([$slug]);
    $service = $st->fetch();
    if (!$service) { header('Location: /?page=hizmetler'); exit; }
    $pageMetaTitle = htmlspecialchars($service['name']) . ' - ' . getSetting('site_title');
    $pageMetaDesc  = htmlspecialchars($service['short_desc'] ?? '');
} else {
    $services = $pdo->query('SELECT * FROM ' . p() . 'services WHERE is_active=1 ORDER BY sort_order')->fetchAll();
    $pageMetaTitle = 'Hizmetlerimiz - ' . getSetting('site_title');
    $pageMetaDesc  = 'Metal ve aluminyum hizmetlerimizi inceleyin.';
}
?>
    <h1><?= $slug ? htmlspecialchars($service['name']) : 'Hizmetlerimiz' ?></h1>
    <p><?= $slug ? htmlspecialchars($service['short_desc'] ?? '') : 'Profesyonel metal ve aluminyum cozumleri.' ?></p>
  </div>
</div>

<section class="section">
  <div class="container">
    <?php if ($slug): ?>
    <div class="about-grid">
      <div>
        <?php if ($service['image']): ?>
        <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" style="width:100%;border-radius:16px;max-height:400px;object-fit:cover">
        <?php else: ?>
        <div style="width:100%;height:400px;border-radius:16px;background:linear-gradient(135deg,#1a1a1a,#2c3e50);display:flex;align-items:center;justify-content:center">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="1"><path d="<?= $icons[$service['icon']] ?? $icons['settings'] ?>"/></svg>
        </div>
        <?php endif; ?>
      </div>
      <div>
        <div class="section-label">Hizmet Detayi</div>
        <h2 class="section-title"><?= htmlspecialchars($service['name']) ?></h2>
        <div class="page-content"><?= $service['description'] ?: '<p>' . htmlspecialchars($service['short_desc']) . '</p>' ?></div>
        <a href="/?page=teklif" class="btn-main" style="margin-top:28px">Teklif Al</a>
      </div>
    </div>
    <?php else: ?>
    <div class="services-grid" style="grid-template-columns:repeat(2,1fr);gap:28px">
      <?php foreach ($services as $s): ?>
      <div class="service-card" style="text-align:left;padding:36px">
        <div class="service-icon" style="margin:0 0 20px">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $icons[$s['icon']] ?? $icons['settings'] ?>"/></svg>
        </div>
        <h3 class="service-name"><?= htmlspecialchars($s['name']) ?></h3>
        <p class="service-desc"><?= htmlspecialchars($s['short_desc']) ?></p>
        <a href="/?page=hizmetler&slug=<?= htmlspecialchars($s['slug']) ?>" class="service-link" style="margin-top:20px">
          Detaylar
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>
      <?php endforeach; ?>
      <?php if (empty($services)): ?>
      <div style="grid-column:1/-1;text-align:center;padding:60px;color:#999">Admin panelinden hizmet ekleyebilirsiniz.</div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once ROOT . '/includes/footer.php'; ?>

<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/includes/header.php';
$pdo = getDB();

$slug     = trim($_GET['slug'] ?? '');
$services = $pdo->query('SELECT * FROM ' . p() . 'services WHERE is_active=1 ORDER BY sort_order')->fetchAll();
$service  = null;

if ($slug) {
    foreach ($services as $s) { if ($s['slug'] === $slug) { $service = $s; break; } }
    if (!$service) { header('Location: /?page=hizmetler'); exit; }
    $pageMetaTitle = htmlspecialchars($service['name']) . ' - ' . getSetting('site_title');
    $pageMetaDesc  = htmlspecialchars($service['short_desc'] ?? '');
}

$phone = getSetting('site_phone', '');
$phoneClean = preg_replace('/\D/', '', $phone);
?>

<style>
/* Hizmet detay */
.hizmet-intro p  { font-size:16px; line-height:1.9; color:#444; margin-bottom:24px; }
.hizmet-liste    { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin:16px 0 28px; }
.hizmet-item     { background:#f8f9fa; border-radius:12px; padding:20px; border-left:3px solid #c0392b; }
.hizmet-item h4  { font-size:14px; font-weight:700; color:#1a1a2e; margin-bottom:8px; }
.hizmet-item p   { font-size:13px; color:#666; line-height:1.7; margin:0; }
.hizmet-tablo    { width:100%; border-collapse:collapse; margin:12px 0 24px; font-size:13px; }
.hizmet-tablo th { background:#1a1a2e; color:#fff; padding:10px 14px; text-align:left; font-weight:600; }
.hizmet-tablo td { padding:9px 14px; border-bottom:1px solid #eee; color:#555; }
.hizmet-tablo tr:nth-child(even) td { background:#f8f9fa; }
.hizmet-tablo tr:last-child td { border-bottom:none; }
.hizmet-avantaj  { list-style:none; padding:0; margin:12px 0; display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.hizmet-avantaj li { background:#fff; border:1px solid #eaedf0; border-radius:10px; padding:12px 14px; font-size:13px; color:#555; line-height:1.5; }
.hizmet-avantaj li strong { color:#1a1a2e; display:block; margin-bottom:2px; font-size:13px; }
.page-content h3 { font-size:17px; font-weight:700; color:#1a1a2e; margin:28px 0 12px; padding-bottom:8px; border-bottom:2px solid #f0f0f0; }
.service-sidebar { position:sticky; top:calc(var(--header-h) + 24px); display:flex; flex-direction:column; gap:16px; }
@media(max-width:900px) { .hizmet-detay-wrap { grid-template-columns:1fr !important; } .service-sidebar { position:static; } }
@media(max-width:640px) { .hizmet-liste, .hizmet-avantaj { grid-template-columns:1fr; } .hizmet-hero { height:260px !important; } .hizmet-hero h1 { font-size:22px !important; } .hizmet-hero-text { left:20px !important; right:20px !important; bottom:20px !important; } }
</style>

<section class="section">
  <div class="container">
    <?php if ($slug && $service): ?>

    <!-- HERO -->
    <?php if ($service['image']): ?>
    <div class="hizmet-hero" style="width:100%;height:400px;border-radius:20px;overflow:hidden;margin-bottom:48px;position:relative">
      <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" style="width:100%;height:100%;object-fit:cover">
      <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.75) 0%,rgba(0,0,0,.1) 55%,transparent 100%)"></div>
      <div class="hizmet-hero-text" style="position:absolute;bottom:36px;left:44px;right:44px">
        <div style="font-size:11px;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px">Hizmet Detayı</div>
        <h1 style="font-size:34px;font-weight:800;color:#fff;margin:0 0 8px;line-height:1.2"><?= htmlspecialchars($service['name']) ?></h1>
        <p style="color:rgba(255,255,255,.78);font-size:15px;margin:0;max-width:600px"><?= htmlspecialchars($service['short_desc']) ?></p>
      </div>
    </div>
    <?php else: ?>
    <div style="margin-bottom:32px;padding-bottom:24px;border-bottom:2px solid #f0f0f0">
      <div class="section-label">Hizmet Detayı</div>
      <h1 class="section-title"><?= htmlspecialchars($service['name']) ?></h1>
      <p class="section-desc"><?= htmlspecialchars($service['short_desc']) ?></p>
    </div>
    <?php endif; ?>

    <!-- İÇERİK + SIDEBAR -->
    <div class="hizmet-detay-wrap" style="display:grid;grid-template-columns:1fr 300px;gap:48px;align-items:start">

      <!-- Sol: İçerik -->
      <div class="page-content">
        <?= $service['description'] ?: '<p>' . htmlspecialchars($service['short_desc']) . '</p>' ?>
      </div>

      <!-- Sağ: Sidebar -->
      <div class="service-sidebar">
        <!-- Teklif Kutusu -->
        <div style="background:linear-gradient(145deg,#c0392b 0%,#96281b 100%);border-radius:16px;padding:28px;color:#fff">
          <div style="width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
          </div>
          <h3 style="font-size:17px;font-weight:700;margin:0 0 8px">Ücretsiz Teklif Alın</h3>
          <p style="font-size:13px;color:rgba(255,255,255,.8);line-height:1.6;margin:0 0 20px">
            Uzman ekibimiz en kısa sürede sizinle iletişime geçecektir.
          </p>
          <a href="/?page=teklif&service=<?= urlencode($service['name']) ?>"
             style="display:block;background:#fff;color:#c0392b;text-align:center;padding:12px;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none;margin-bottom:10px">
            Hemen Teklif Al →
          </a>
          <?php if ($phone): ?>
          <a href="tel:<?= $phoneClean ?>"
             style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;border:1px solid rgba(255,255,255,.3);border-radius:10px;font-size:13px;color:#fff;text-decoration:none">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.81 19.79 19.79 0 0117.7 2a2 2 0 012.28 2v3"/></svg>
            <?= htmlspecialchars($phone) ?>
          </a>
          <?php endif; ?>
        </div>

        <!-- Diğer Hizmetler -->
        <div style="background:#f8f9fa;border-radius:16px;padding:24px">
          <h4 style="font-size:12px;font-weight:700;color:#999;margin:0 0 16px;text-transform:uppercase;letter-spacing:1.5px">Diğer Hizmetler</h4>
          <?php foreach ($services as $s): ?>
          <?php if ($s['slug'] === $slug) continue; ?>
          <a href="/?page=hizmetler&slug=<?= htmlspecialchars($s['slug']) ?>"
             style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #eee;text-decoration:none;color:#444;font-size:13px;font-weight:500;transition:color .2s"
             onmouseover="this.style.color='#c0392b'" onmouseout="this.style.color='#444'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            <?= htmlspecialchars($s['name']) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <?php else: ?>
    <!-- HİZMET LİSTESİ -->
    <div style="text-align:center;margin-bottom:48px">
      <div class="section-label">Hizmetlerimiz</div>
      <h1 class="section-title">Profesyonel <span>Çözümler</span></h1>
      <p class="section-desc" style="max-width:560px;margin:0 auto">Alüminyum doğrama ve dış cephe sistemleri alanında Konya'nın güvenilir çözüm ortağı.</p>
    </div>
    <div class="services-grid" style="grid-template-columns:repeat(2,1fr);gap:24px">
      <?php foreach ($services as $s): ?>
      <div class="service-card">
        <?php if (!empty($s['image'])): ?>
        <div class="service-img" style="background-image:url('<?= htmlspecialchars($s['image']) ?>')"></div>
        <?php endif; ?>
        <div class="service-body">
          <h3 class="service-name"><?= htmlspecialchars($s['name']) ?></h3>
          <p class="service-desc"><?= htmlspecialchars($s['short_desc']) ?></p>
          <a href="/?page=hizmetler&slug=<?= htmlspecialchars($s['slug']) ?>" class="service-link" style="margin-top:16px">
            Detaylar
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once ROOT . '/includes/footer.php'; ?>

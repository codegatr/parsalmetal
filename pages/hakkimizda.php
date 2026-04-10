<?php
require_once ROOT . '/includes/header.php';
$pdo = getDB();
$pg  = $pdo->query('SELECT * FROM ' . p() . 'pages WHERE slug=\'hakkimizda\'')->fetch();
$stats = $pdo->query('SELECT * FROM ' . p() . 'stats ORDER BY sort_order')->fetchAll();
$certs = $pdo->query('SELECT * FROM ' . p() . 'certificates WHERE is_active=1 ORDER BY sort_order')->fetchAll();
$pageMetaTitle = ($pg['meta_title'] ?? '') ?: 'Hakkımızda - ' . getSetting('site_title');
$pageMetaDesc  = ($pg['meta_description'] ?? '') ?: '';
?>
    <h1><?= htmlspecialchars($pg['title'] ?? 'Hakkımızda') ?></h1>
    <p>Parsal Metal Alüminyum ailesiyle tanınabilirsiniz.</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="about-grid">
      <div class="about-img-wrap">
        <?php $img = getSetting('about_image',''); ?>
        <?php if ($img): ?>
        <img class="about-img" src="<?= htmlspecialchars($img) ?>" alt="Hakkımızda">
        <?php else: ?>
        <div class="about-img-placeholder">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="1"><polygon points="12 2 22 19 2 19"/></svg>
        </div>
        <?php endif; ?>
        <div class="about-badge">
          <span class="num"><?= getSetting('founded_year','25') ?>+</span>
          <span class="lbl">YIL<br>DENEYIM</span>
        </div>
      </div>
      <div>
        <div class="section-label">Kurumsal</div>
        <h2 class="section-title">Parsal Metal <span>Kimdir?</span></h2>
        <div class="page-content">
          <?= $pg['content'] ?? '<p>Metal ve alüminyum sektöründe onlarca yıllık deneyime sahip firmamiz, müşterilerine en kaliteli urun ve hizmetleri sunmaktadır.</p>' ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($stats): ?>
<div class="stats-bar"><div class="stats-inner">
<?php foreach ($stats as $s): ?>
<div class="stat-item">
  <div class="stat-value" data-target="<?= preg_replace('/\D/','',$s['value']) ?>" data-suffix="<?= preg_replace('/\d/','',$s['value']) ?>"><?= htmlspecialchars($s['value']) ?></div>
  <div class="stat-label"><?= htmlspecialchars($s['label']) ?></div>
</div>
<?php endforeach; ?>
</div></div>
<?php endif; ?>

<!-- Mission & Vision -->
<section class="section section-alt">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px">
      <?php
      $mv = [
        ['title'=>'Misyonumuz','icon'=>'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z','text'=>getSetting('mission_text','Müşterilerimize en yüksek kalitede metal ve alüminyum urun ve hizmetleri sunarak sektörün öne çıkan firmasi olmak.')],
        ['title'=>'Vizyonumuz','icon'=>'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z M12 9a3 3 0 100 6 3 3 0 000-6z','text'=>getSetting('vision_text','Yenilikci üretim yöntemleri ve müşteri odaklı yaklasimimizla global pazarda tanınmış bir marka olmak.')],
      ];
      foreach ($mv as $item): ?>
      <div style="background:#fff;border-radius:16px;padding:36px;border:1px solid #eaedf0">
        <div style="width:56px;height:56px;background:rgba(192,57,43,.1);border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:20px">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $item['icon'] ?>"/></svg>
        </div>
        <h3 style="font-size:20px;font-weight:700;margin-bottom:12px"><?= $item['title'] ?></h3>
        <p style="color:#6b7280;line-height:1.7"><?= htmlspecialchars($item['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($certs): ?>
<section class="section">
  <div class="container">
    <div class="section-header center">
      <div class="section-label">Belgelendigimiz</div>
      <h2 class="section-title">Sertifika &amp; <span>Belgelerimiz</span></h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:20px">
      <?php foreach ($certs as $c): ?>
      <div style="text-align:center;padding:24px;background:#f8f8f8;border-radius:12px;border:1px solid #eee">
        <?php if ($c['image']): ?>
        <img src="<?= htmlspecialchars($c['image']) ?>" alt="<?= htmlspecialchars($c['name']) ?>" style="height:80px;object-fit:contain;margin:0 auto 12px">
        <?php endif; ?>
        <p style="font-size:13px;font-weight:600;color:#333"><?= htmlspecialchars($c['name']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once ROOT . '/includes/footer.php'; ?>

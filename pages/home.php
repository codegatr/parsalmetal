<?php
// Meta değerleri header'dan ÖNCE set et
if (!isset($pdo)) { require_once ROOT . '/includes/db.php'; }
if (!function_exists('getSetting')) { require_once ROOT . '/includes/functions.php'; }

$pageMetaTitle = getSetting('meta_title', getSetting('site_title') . ' - Metal ve Alüminyum Çözümleri');
$pageMetaDesc  = getSetting('meta_description', '');

require_once ROOT . '/includes/header.php';
$pdo = getDB();

// Tüm verileri çek
$slides   = $pdo->query('SELECT * FROM ' . p() . 'slider WHERE is_active=1 ORDER BY sort_order')->fetchAll();
$stats    = $pdo->query('SELECT * FROM ' . p() . 'stats ORDER BY sort_order')->fetchAll();
$services = $pdo->query('SELECT * FROM ' . p() . 'services WHERE is_active=1 ORDER BY sort_order LIMIT 4')->fetchAll();
$products = $pdo->query('SELECT pr.*, c.name as cat_name FROM ' . p() . 'products pr LEFT JOIN ' . p() . 'categories c ON c.id=pr.category_id WHERE pr.is_active=1 ORDER BY pr.is_featured DESC, pr.sort_order LIMIT 6')->fetchAll();
$cats     = $pdo->query('SELECT * FROM ' . p() . 'categories WHERE is_active=1 ORDER BY sort_order')->fetchAll();
$refs     = $pdo->query('SELECT * FROM ' . p() . 'references WHERE is_active=1 ORDER BY sort_order')->fetchAll();
$certs    = $pdo->query('SELECT * FROM ' . p() . 'certificates WHERE is_active=1 ORDER BY sort_order')->fetchAll();

// Ayarlar
$sitePhone   = getSetting('site_phone', '');
$siteEmail   = getSetting('site_email', '');
$siteAddress = getSetting('site_address', '');
$siteSlogan  = getSetting('site_slogan', 'Metal ve Alüminyum Çözümleri');
$aboutImg    = getSetting('about_image', '');
$aboutShort  = getSetting('about_short', 'Metal ve alüminyum sektöründe yıllardır edindigi deneyimle müşterilerine en yüksek kalitede urun ve hizmetler sunmaktadır.');
$foundedYear = getSetting('founded_year', '25');
$whatsapp    = getSetting('site_whatsapp', preg_replace('/\D/', '', $sitePhone));

// Hakkımızda liste maddeleri (DB'den, yoksa default)
$featuresRaw = getSetting('about_features', '');
if ($featuresRaw) {
    $aboutFeatures = array_filter(array_map('trim', explode("\n", $featuresRaw)));
} else {
    $aboutFeatures = [
        'Alumil, Linea Rossa ve Alfore yetkili üreticisi',
        '20 yılı aşkın deneyim ve 850+ tamamlanan proje',
        'Konya genelinde ücretsiz ölçüm ve keşif hizmeti',
        'Projeye özel tasarım ve anahtar teslim uygulama',
        'Montaj sonrası satış desteği ve periyodik bakım',
    ];
}

// Neden biz maddeleri (DB'den, yoksa default)
$whyRaw = getSetting('why_us_items', '');
if ($whyRaw) {
    $whyDecoded = json_decode($whyRaw, true);
}
$whyItems = (!empty($whyDecoded) && is_array($whyDecoded)) ? $whyDecoded : [
    ['num'=>'01','title'=>'Yetkili Üretici',     'desc'=>'Alumil, Linea Rossa ve Alfore markalari yetkili üreticisi olarak orijinal sistem ve malzeme güvencesi sunuyoruz.'],
    ['num'=>'02','title'=>'Ücretsiz Keşif',      'desc'=>'Konya genelinde ücretsiz ölcüm ve keşif hizmeti veriyoruz. Yerinde inceleme ile en doğru çözümü belirliyoruz.'],
    ['num'=>'03','title'=>'Zamaninda Teslimat',  'desc'=>'Proje baslangicinda belirlenen teslimat tarihlerine sadik kalarak sürpriz gecikmeler yasatmiyoruz.'],
    ['num'=>'04','title'=>'Uygun Fiyat',         'desc'=>'Fabrikasyon üretim avantajimizla rekabetci fiyatlar sunuyor, kaliteden ödün vermeden bütcenize saygi gösteriyoruz.'],
    ['num'=>'05','title'=>'Satis Sonrasi Destek','desc'=>'Montaj sonrasinda da yaninizdayiz. Periyodik bakim, onarim ve yedek parca temin hizmetleriyle her zaman ulasabilirsiniz.'],
    ['num'=>'06','title'=>'850+ Referans',        'desc'=>'Konya genelinde konuttan ticariye, hastaneden okula 850+ basarili projemiz sektördeki güvenilirligimizin kaniti.'],
];

$icons = [
    'settings'   => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 0 1 21 12a10 10 0 0 1-1.93 7.07M4.93 4.93A10 10 0 0 0 3 12a10 10 0 0 0 1.93 7.07"/></svg>',
    'tool'       => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>',
    'layers'     => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>',
    'headphones' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/></svg>',
    'star'       => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    'shield'     => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    'award'      => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>',
    'truck'      => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
];
?>

<!-- ==================== HERO SLIDER ==================== -->
<div class="hero-slider">
  <div class="slider-track">
    <?php if ($slides): foreach ($slides as $i => $s): ?>
    <div class="slide<?= $i === 0 ? ' active' : '' ?>">
      <div class="slide-bg" style="<?= $s['image'] ? 'background-image:url(' . htmlspecialchars($s['image']) . ')' : 'background:linear-gradient(135deg,#1a1a1a 0%,#2c3e50 60%,#1a1a1a 100%)' ?>"></div>
      <div class="slide-overlay"></div>
      <div class="slide-content">
        <div class="slide-eyebrow"><?= htmlspecialchars($siteSlogan) ?></div>
        <h1 class="slide-title"><?= $s['title'] ?></h1>
        <p class="slide-sub"><?= htmlspecialchars($s['subtitle']) ?></p>
        <div class="slide-btns">
          <a href="<?= htmlspecialchars($s['button_url'] ?? '/?page=ürünler') ?>" class="slide-btn-primary">
            <?= htmlspecialchars($s['button_text'] ?? 'Keşfet') ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <a href="/?page=teklif" class="slide-btn-secondary">Teklif Al</a>
        </div>
      </div>
    </div>
    <?php endforeach; else: ?>
    <div class="slide active" style="background:linear-gradient(135deg,#1a1a1a 0%,#2c3e50 100%)">
      <div class="slide-overlay"></div>
      <div class="slide-content">
        <div class="slide-eyebrow"><?= htmlspecialchars($siteSlogan) ?></div>
        <h1 class="slide-title">Metalin Gucu,<br>Alüminyumun Zarafeti</h1>
        <p class="slide-sub">Yüksek kaliteli metal ve alüminyum çözümleriyle projelerinize deger katiyoruz.</p>
        <div class="slide-btns">
          <a href="/?page=ürünler" class="slide-btn-primary">Ürünlerimizi Keşfet
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <a href="/?page=teklif" class="slide-btn-secondary">Teklif Al</a>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <?php if (count($slides) > 1): ?>
  <button class="slider-prev" aria-label="Onceki">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
  </button>
  <button class="slider-next" aria-label="Sonraki">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
  </button>
  <div class="slider-dots">
    <?php for ($i = 0; $i < count($slides); $i++): ?>
    <button class="slider-dot<?= $i === 0 ? ' active' : '' ?>" data-idx="<?= $i ?>" aria-label="Slayt <?= $i+1 ?>"></button>
    <?php endfor; ?>
  </div>
  <div class="slider-counter"><span>1</span>/<?= count($slides) ?></div>
  <?php endif; ?>

  <!-- Scroll indicator -->
  <div class="scroll-indicator">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 13 12 18 17 13"/><polyline points="7 6 12 11 17 6"/></svg>
  </div>
</div>

<!-- ==================== STATS BAR ==================== -->
<?php if ($stats): ?>
<div class="stats-bar">
  <div class="stats-inner">
    <?php foreach ($stats as $st): ?>
    <div class="stat-item">
      <div class="stat-value"
           data-target="<?= preg_replace('/\D/', '', $st['value']) ?>"
           data-suffix="<?= htmlspecialchars(preg_replace('/\d/', '', $st['value'])) ?>">
        <?= htmlspecialchars($st['value']) ?>
      </div>
      <div class="stat-label"><?= htmlspecialchars($st['label']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ==================== HAKKIMIZDA ==================== -->
<section class="section">
  <div class="container">
    <div class="about-grid">
      <div class="about-img-wrap">
        <?php if ($aboutImg): ?>
        <img class="about-img" src="<?= htmlspecialchars($aboutImg) ?>" alt="Hakkımızda">
        <?php else: ?>
        <div class="about-img-placeholder">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.12)" stroke-width="1">
            <polygon points="12 2 22 19 2 19"/>
            <line x1="12" y1="8" x2="12" y2="13"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
        </div>
        <?php endif; ?>
        <div class="about-badge">
          <span class="num"><?= htmlspecialchars($foundedYear) ?>+</span>
          <span class="lbl">YIL<br>DENEYIM</span>
        </div>
      </div>
      <div>
        <div class="section-label">Hakkımızda</div>
        <h2 class="section-title">Sektörün <span>Guvenilir</span> Partneri</h2>
        <p class="section-desc"><?= nl2br(htmlspecialchars($aboutShort)) ?></p>
        <ul class="about-list">
          <?php foreach ($aboutFeatures as $feat): ?>
          <li><?= htmlspecialchars($feat) ?></li>
          <?php endforeach; ?>
        </ul>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:28px">
          <a href="/?page=hakkimizda" class="btn-main">
            Daha Fazla Bilgi
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <a href="/?page=teklif" class="btn-outline">Teklif Al</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==================== HİZMETLER ==================== -->
<?php if ($services): ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-header center">
      <div class="section-label">Hizmetlerimiz</div>
      <h2 class="section-title">Ne <span>Yapiyoruz?</span></h2>
      <p class="section-desc">Geniş hizmet yelpazamizla metal ve alüminyum ihtiyaclari icin tek durak noktaniz.</p>
    </div>
    <div class="services-grid">
      <?php foreach ($services as $srv): ?>
      <div class="service-card">
        <?php if (!empty($srv['image'])): ?>
        <div class="service-img" style="background-image:url('<?= htmlspecialchars($srv['image']) ?>')"></div>
        <?php else: ?>
        <div class="service-icon"><?= $icons[$srv['icon']] ?? $icons['settings'] ?></div>
        <?php endif; ?>
        <div class="service-body">
          <div class="service-name"><?= htmlspecialchars($srv['name']) ?></div>
          <p class="service-desc"><?= htmlspecialchars($srv['short_desc']) ?></p>
          <a href="/?page=hizmetler&slug=<?= htmlspecialchars($srv['slug']) ?>" class="service-link">
            Detaylar
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ==================== ÜRÜNLER ==================== -->
<section class="section">
  <div class="container">
    <div class="section-header" style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:16px">
      <div>
        <div class="section-label">Ürünlerimiz</div>
        <h2 class="section-title" style="margin-bottom:8px">Öne Çıkan <span>Ürünler</span></h2>
        <p class="section-desc">Geniş urun portfoyumuzden ihtiyacınıza uygun secimi yapabilirsiniz.</p>
      </div>
      <?php if ($products): ?>
      <a href="/?page=ürünler" class="btn-outline" style="margin-top:0;flex-shrink:0">Tum Ürünler →</a>
      <?php endif; ?>
    </div>

    <?php if ($cats): ?>
    <div class="products-filter">
      <button class="filter-btn active" data-cat="all">Tümü</button>
      <?php foreach ($cats as $c): ?>
      <button class="filter-btn" data-cat="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="products-grid">
      <?php foreach ($products as $pr): ?>
      <div class="product-card" data-cat="<?= $pr['category_id'] ?>">
        <div class="product-img">
          <?php if ($pr['image']): ?>
          <img src="<?= htmlspecialchars($pr['image']) ?>" alt="<?= htmlspecialchars($pr['name']) ?>" loading="lazy">
          <?php else: ?>
          <div class="product-img-placeholder">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
          <?php endif; ?>
          <?php if ($pr['is_featured']): ?>
          <span class="product-badge">Öne Çıkan</span>
          <?php endif; ?>
        </div>
        <div class="product-body">
          <div class="product-cat"><?= htmlspecialchars($pr['cat_name'] ?? '') ?></div>
          <h3 class="product-name"><?= htmlspecialchars($pr['name']) ?></h3>
          <p class="product-desc"><?= htmlspecialchars($pr['short_desc'] ?? '') ?></p>
          <div class="product-footer">
            <a href="/?page=urun&slug=<?= htmlspecialchars($pr['slug']) ?>" class="product-link">
              Detay
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="/?page=teklif&product=<?= urlencode($pr['name']) ?>" class="product-cta">Teklif Al</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($products)): ?>
      <div style="grid-column:1/-1;text-align:center;padding:64px;color:#bbb">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ddd" stroke-width="1.5" style="margin:0 auto 16px"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
        <p style="font-size:15px">Henuz urun eklenmemis.</p>
        <p style="font-size:13px;margin-top:6px">Admin panelinden urun ekleyebilirsiniz.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ==================== NEDEN BİZ ==================== -->
<section class="section section-dark">
  <div class="container">
    <div class="section-header center">
      <div class="section-label" style="color:#e74c3c;justify-content:center;display:flex">Neden Biz?</div>
      <h2 class="section-title" style="color:#fff">Fark Yaratan <span>Nedenler</span></h2>
      <p class="section-desc" style="color:rgba(255,255,255,.55);margin:0 auto">Sektörde fark yaratmamizi saglayan degerlerimiz ve calisma anlayisimiz.</p>
    </div>
    <div class="why-grid">
      <?php foreach ($whyItems as $w): ?>
      <div class="why-card">
        <div class="why-num"><?= htmlspecialchars($w['num']) ?></div>
        <h3 class="why-title"><?= htmlspecialchars($w['title']) ?></h3>
        <p class="why-desc"><?= htmlspecialchars($w['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ==================== TEKLİF CTA ==================== -->
<section class="quote-section">
  <div class="container">
    <div class="quote-inner">
      <div class="quote-text">
        <h2>Projeniz Icin Ucretsiz Teklif Alın</h2>
        <p>Uzman ekibimiz en kısa sürede sizinle iletişime geçecektir.</p>
      </div>
      <a href="/?page=teklif" class="btn-quote-white">Hemen Teklif Alın →</a>
    </div>
  </div>
</section>

<!-- ==================== SERTİFİKALAR ==================== -->
<?php if ($certs): ?>
<section class="section section-alt" style="padding:64px 0">
  <div class="container">
    <div class="section-header center" style="margin-bottom:40px">
      <div class="section-label">Belge ve Sertifikalar</div>
      <h2 class="section-title">Kalite <span>Belgelendirmesi</span></h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:20px;justify-items:center">
      <?php foreach ($certs as $c): ?>
      <div style="text-align:center;padding:24px 16px;background:#fff;border-radius:12px;border:1px solid #eaedf0;width:100%;transition:.3s" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow=''">
        <?php if ($c['image']): ?>
        <img src="<?= htmlspecialchars($c['image']) ?>" alt="<?= htmlspecialchars($c['name']) ?>" style="height:70px;width:auto;object-fit:contain;margin:0 auto 12px">
        <?php else: ?>
        <div style="width:70px;height:70px;background:rgba(192,57,43,.08);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <?php endif; ?>
        <p style="font-size:13px;font-weight:600;color:#333;line-height:1.3"><?= htmlspecialchars($c['name']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ==================== REFERANSLAR ==================== -->
<?php if ($refs): ?>
<section class="section" style="padding:64px 0">
  <div class="container">
    <div class="section-header center" style="margin-bottom:40px">
      <div class="section-label">Referanslarimiz</div>
      <h2 class="section-title">Bize Guvenen <span>Firmalar</span></h2>
    </div>
  </div>
  <div class="refs-track">
    <div class="refs-inner">
      <?php foreach (array_merge($refs, $refs) as $ref): ?>
      <div class="ref-item">
        <?php if ($ref['logo']): ?>
        <img class="ref-logo" src="<?= htmlspecialchars($ref['logo']) ?>" alt="<?= htmlspecialchars($ref['name']) ?>">
        <?php else: ?>
        <span class="ref-name"><?= htmlspecialchars($ref['name']) ?></span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ==================== HIZ ILETISIM SERIDI ==================== -->
<?php if ($sitePhone || $siteEmail || $siteAddress): ?>
<section style="background:#1a1a1a;padding:40px 0">
  <div class="container">
    <div class="iletisim-serit" style="display:grid;grid-template-columns:<?= ($sitePhone && $siteEmail && $siteAddress) ? 'repeat(3,1fr)' : 'repeat(2,1fr)' ?>;gap:24px;align-items:center">
      <?php if ($sitePhone): ?>
      <a href="tel:<?= preg_replace('/\D/','',$sitePhone) ?>" style="display:flex;align-items:center;gap:14px;text-decoration:none;padding:16px;border-radius:10px;transition:.2s" onmouseover="this.style.background='rgba(255,255,255,.05)'" onmouseout="this.style.background=''">
        <div style="width:44px;height:44px;background:rgba(192,57,43,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.81 19.79 19.79 0 0117.7 2a2 2 0 012.28 2v3"/></svg>
        </div>
        <div>
          <div style="font-size:11px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Telefon</div>
          <div style="font-size:15px;font-weight:600;color:#fff"><?= htmlspecialchars($sitePhone) ?></div>
        </div>
      </a>
      <?php endif; ?>
      <?php if ($siteEmail): ?>
      <a href="mailto:<?= htmlspecialchars($siteEmail) ?>" style="display:flex;align-items:center;gap:14px;text-decoration:none;padding:16px;border-radius:10px;transition:.2s" onmouseover="this.style.background='rgba(255,255,255,.05)'" onmouseout="this.style.background=''">
        <div style="width:44px;height:44px;background:rgba(192,57,43,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <div>
          <div style="font-size:11px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">E-posta</div>
          <div style="font-size:15px;font-weight:600;color:#fff"><?= htmlspecialchars($siteEmail) ?></div>
        </div>
      </a>
      <?php endif; ?>
      <?php if ($siteAddress): ?>
      <div style="display:flex;align-items:center;gap:14px;padding:16px">
        <div style="width:44px;height:44px;background:rgba(192,57,43,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div>
          <div style="font-size:11px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Adres</div>
          <div style="font-size:14px;font-weight:500;color:rgba(255,255,255,.8);line-height:1.4"><?= nl2br(htmlspecialchars($siteAddress)) ?></div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ==================== WHATSAPP BUTONU ==================== -->
<?php if ($whatsapp): ?>
<a href="https://wa.me/<?= preg_replace('/\D/','',$whatsapp) ?>?text=Merhaba, bilgi almak istiyorum." target="_blank" rel="noopener"
   title="WhatsApp ile yaz"
   style="position:fixed;bottom:calc(88px + env(safe-area-inset-bottom));right:max(24px, env(safe-area-inset-right));z-index:998;width:52px;height:52px;border-radius:50%;background:#25d366;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(37,211,102,.45);transition:.3s;text-decoration:none"
   onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 6px 28px rgba(37,211,102,.6)'"
   onmouseout="this.style.transform='';this.style.boxShadow='0 4px 20px rgba(37,211,102,.45)'">
  <svg width="26" height="26" viewBox="0 0 24 24" fill="white">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
  </svg>
</a>
<?php endif; ?>

<?php require_once ROOT . '/includes/footer.php'; ?>

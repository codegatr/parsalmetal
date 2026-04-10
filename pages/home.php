<?php
require_once ROOT . '/includes/header.php';
$pdo = getDB();

// Slider
$slides = $pdo->query('SELECT * FROM ' . p() . 'slider WHERE is_active=1 ORDER BY sort_order')->fetchAll();

// Stats
$stats = $pdo->query('SELECT * FROM ' . p() . 'stats ORDER BY sort_order')->fetchAll();

// Services
$services = $pdo->query('SELECT * FROM ' . p() . 'services WHERE is_active=1 ORDER BY sort_order LIMIT 4')->fetchAll();

// Featured Products
$products = $pdo->query('SELECT pr.*, c.name as cat_name FROM ' . p() . 'products pr LEFT JOIN ' . p() . 'categories c ON c.id=pr.category_id WHERE pr.is_active=1 ORDER BY pr.is_featured DESC, pr.sort_order LIMIT 6')->fetchAll();

// Categories for filter
$cats = $pdo->query('SELECT * FROM ' . p() . 'categories WHERE is_active=1 ORDER BY sort_order')->fetchAll();

// References
$refs = $pdo->query('SELECT * FROM ' . p() . 'references WHERE is_active=1 ORDER BY sort_order')->fetchAll();

// About content
$aboutPage = $pdo->query('SELECT * FROM ' . p() . 'pages WHERE slug=\'hakkimizda\'')->fetch();

$pageMetaTitle = getSetting('meta_title', getSetting('site_title') . ' - Metal ve Aluminyum Cozumleri');
$pageMetaDesc  = getSetting('meta_description', '');

// Icon SVGs
$icons = [
  'settings'   => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 0 1 21 12a10 10 0 0 1-1.93 7.07M4.93 4.93A10 10 0 0 0 3 12a10 10 0 0 0 1.93 7.07"/><path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72 1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>',
  'tool'       => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>',
  'layers'     => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>',
  'headphones' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/></svg>',
  'star'       => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
  'truck'      => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
  'shield'     => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
  'award'      => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>',
];
?>

<!-- HERO SLIDER -->
<div class="hero-slider">
  <div class="slider-track">
    <?php if ($slides): foreach ($slides as $i => $s): ?>
    <div class="slide">
      <div class="slide-bg" style="<?= $s['image'] ? 'background-image:url(' . htmlspecialchars($s['image']) . ')' : 'background:linear-gradient(135deg,#1a1a1a 0%,#2c3e50 50%,#1a1a1a 100%)' ?>"></div>
      <div class="slide-overlay"></div>
      <div class="slide-content">
        <div class="slide-eyebrow">Metal &amp; Aluminyum Cozumleri</div>
        <h1 class="slide-title"><?= $s['title'] ?></h1>
        <p class="slide-sub"><?= htmlspecialchars($s['subtitle']) ?></p>
        <div class="slide-btns">
          <a href="<?= htmlspecialchars($s['button_url'] ?? '/?page=urunler') ?>" class="slide-btn-primary">
            <?= htmlspecialchars($s['button_text'] ?? 'Kesfet') ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <a href="/?page=teklif" class="slide-btn-secondary">Teklif Al</a>
        </div>
      </div>
    </div>
    <?php endforeach; else: ?>
    <div class="slide" style="background:linear-gradient(135deg,#1a1a1a 0%,#2c3e50 100%)">
      <div class="slide-overlay"></div>
      <div class="slide-content">
        <div class="slide-eyebrow">Metal &amp; Aluminyum Cozumleri</div>
        <h1 class="slide-title">Metalin Gucu,<br>Aluminyumun Zarafeti</h1>
        <p class="slide-sub">Yuksek kaliteli metal ve aluminyum cozumleriyle projelerinize deger katiyoruz.</p>
        <div class="slide-btns">
          <a href="/?page=urunler" class="slide-btn-primary">Urunlerimizi Kesfet
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
</div>

<!-- STATS BAR -->
<?php if ($stats): ?>
<div class="stats-bar">
  <div class="stats-inner">
    <?php foreach ($stats as $st): ?>
    <div class="stat-item">
      <div class="stat-value" data-target="<?= preg_replace('/\D/', '', $st['value']) ?>" data-suffix="<?= preg_replace('/\d/', '', $st['value']) ?>"><?= htmlspecialchars($st['value']) ?></div>
      <div class="stat-label"><?= htmlspecialchars($st['label']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ABOUT -->
<section class="section">
  <div class="container">
    <div class="about-grid">
      <div class="about-img-wrap">
        <?php $aboutImg = getSetting('about_image', ''); ?>
        <?php if ($aboutImg): ?>
        <img class="about-img" src="<?= htmlspecialchars($aboutImg) ?>" alt="Hakkimizda">
        <?php else: ?>
        <div class="about-img-placeholder">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="1"><polygon points="12 2 22 19 2 19"/><line x1="12" y1="8" x2="12" y2="13"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <?php endif; ?>
        <div class="about-badge">
          <span class="num"><?= getSetting('founded_year', '25') ?>+</span>
          <span class="lbl">YIL<br>DENEYIM</span>
        </div>
      </div>
      <div>
        <div class="section-label">Hakkimizda</div>
        <h2 class="section-title">Sektorun <span>Guvenilir</span> Partneri</h2>
        <div class="section-desc"><?= getSetting('about_short', 'Metal ve aluminyum sektorunde yillardir edindigi deneyimle musterilerine en yuksek kalitede urun ve hizmetler sunmaktadir.') ?></div>
        <ul class="about-list">
          <li>ISO 9001 Kalite Yonetim Sistemi Sertifikali</li>
          <li>Modern uretim tesisleri ve deneyimli kadro</li>
          <li>Her olcekte projeye ozel cozum</li>
          <li>Hizli teslimat ve guvenilir musteri destegi</li>
          <li>Turkiye genelinde hizmet agimiz</li>
        </ul>
        <a href="/?page=hakkimizda" class="btn-main">
          Daha Fazla Bilgi
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        <a href="/?page=teklif" class="btn-outline">Teklif Al</a>
      </div>
    </div>
  </div>
</section>

<!-- SERVICES -->
<?php if ($services): ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-header center">
      <div class="section-label">Hizmetlerimiz</div>
      <h2 class="section-title">Ne Yapiyoruz?</h2>
      <p class="section-desc">Genis hizmet yelpazamizla metal ve aluminyum ihtiyaclari icin tek durak noktaniz.</p>
    </div>
    <div class="services-grid">
      <?php foreach ($services as $srv): ?>
      <div class="service-card">
        <div class="service-icon">
          <?= $icons[$srv['icon']] ?? $icons['settings'] ?>
        </div>
        <div class="service-name"><?= htmlspecialchars($srv['name']) ?></div>
        <p class="service-desc"><?= htmlspecialchars($srv['short_desc']) ?></p>
        <a href="/?page=hizmetler&slug=<?= htmlspecialchars($srv['slug']) ?>" class="service-link">
          Detaylar
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- PRODUCTS -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-label">Urunlerimiz</div>
      <h2 class="section-title">One Cikan <span>Urunler</span></h2>
      <p class="section-desc">Genis urun portfoyumuzden ihtiyaciniza uygun metal ve aluminyum urunlerini kesfedebilirsiniz.</p>
    </div>
    <?php if ($cats): ?>
    <div class="products-filter">
      <button class="filter-btn active" data-cat="all">Tumü</button>
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
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
          <?php endif; ?>
          <?php if ($pr['is_featured']): ?>
          <span class="product-badge">One Cikan</span>
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
      <div style="grid-column:1/-1;text-align:center;padding:60px;color:#999">
        <p>Henuz urun eklenmemis. Admin panelinden urun ekleyebilirsiniz.</p>
      </div>
      <?php endif; ?>
    </div>
    <?php if ($products): ?>
    <div style="text-align:center;margin-top:48px">
      <a href="/?page=urunler" class="btn-outline">Tum Urunleri Gor</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- WHY US -->
<section class="section section-dark">
  <div class="container">
    <div class="section-header center">
      <div class="section-label" style="color:#e74c3c">Neden Biz?</div>
      <h2 class="section-title" style="color:#fff">Fark Yaratan <span>Nedenler</span></h2>
      <p class="section-desc" style="color:rgba(255,255,255,.6);margin:0 auto">Sektorde fark yaratmamizi saglayan degerlerimiz ve calısma anlayisimiz.</p>
    </div>
    <div class="why-grid">
      <?php
      $whyItems = [
        ['num'=>'01','title'=>'Kalite Guventesi','desc'=>'ISO 9001 sertifikali uretim surecleriyle her urunde tutarli ve yuksek kalite sunuyoruz.'],
        ['num'=>'02','title'=>'Uzman Kadro','desc'=>'Alaninda uzman muhendis ve teknisyen ekibimizle en karmasik projelere bile cozum uretiyoruz.'],
        ['num'=>'03','title'=>'Hizli Teslimat','desc'=>'Optimize edilmis lojistik agimiz sayesinde siparisleri zamaninda ve hasarsiz teslim ediyoruz.'],
        ['num'=>'04','title'=>'Rekabetci Fiyat','desc'=>'Sektordeki en rekabetci fiyatlarimizla butce dostu cozumler sunuyoruz.'],
        ['num'=>'05','title'=>'7/24 Destek','desc'=>'Musteri memnuniyetini on planda tutarak kesintisiz destek ve danismanlik hizmeti veriyoruz.'],
        ['num'=>'06','title'=>'Ozel Cozumler','desc'=>'Her projeye ozel tasarim ve imalat yaparak musterilerimizin benzersiz ihtiyaclarini karsiliyoruz.'],
      ];
      foreach ($whyItems as $w): ?>
      <div class="why-card">
        <div class="why-num"><?= $w['num'] ?></div>
        <h3 class="why-title"><?= $w['title'] ?></h3>
        <p class="why-desc"><?= $w['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- QUOTE CTA -->
<section class="quote-section">
  <div class="container">
    <div class="quote-inner">
      <div class="quote-text">
        <h2>Projeniz Icin Ucretsiz Teklif Alin</h2>
        <p>Uzman ekibimiz en kisa surede sizinle iletisime gececektir.</p>
      </div>
      <a href="/?page=teklif" class="btn-quote-white">
        Hemen Teklif Alin →
      </a>
    </div>
  </div>
</section>

<!-- REFERENCES -->
<?php if ($refs): ?>
<section class="section section-alt" style="padding:60px 0;">
  <div class="container">
    <div class="section-header center" style="margin-bottom:36px">
      <div class="section-label">Referanslarimiz</div>
      <h2 class="section-title">Guvendikleri <span>Firma</span></h2>
    </div>
  </div>
  <div class="refs-track">
    <div class="refs-inner" id="refsInner">
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

<?php require_once ROOT . '/includes/footer.php'; ?>

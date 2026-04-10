<?php
if (!defined('ROOT')) define('ROOT', dirname(__DIR__));
require_once ROOT . '/includes/db.php';
require_once ROOT . '/includes/functions.php';

$siteTitle   = getSetting('site_title',  'Parsal Metal Alüminyum');
$siteSlogan  = getSetting('site_slogan', 'Metal ve Alüminyum Çözümleri');
$sitePhone   = getSetting('site_phone',  '');
$siteEmail   = getSetting('site_email',  '');
$metaTitle   = getSetting('meta_title',  $siteTitle . ' - Metal ve Alüminyum');
$metaDesc    = getSetting('meta_description', 'Profesyonel metal ve alüminyum çözümleri.');
$siteUrl     = defined('SITE_URL') ? SITE_URL : '';
$cookieBar   = getSetting('cookie_bar', '1');

$currentPage = $_GET['page'] ?? 'home';

$nav = [
  'home'       => 'Ana Sayfa',
  'urunler'    => 'Ürünler',
  'hizmetler'  => 'Hizmetler',
  'hakkimizda' => 'Hakkımızda',
  'teklif'     => 'Teklif Al',
  'iletisim'   => 'İletişim',
];

// Override meta if page sets them
$pageMetaTitle = $pageMetaTitle ?? $metaTitle;
$pageMetaDesc  = $pageMetaDesc  ?? $metaDesc;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?= htmlspecialchars($pageMetaTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageMetaDesc) ?>">
<meta name="robots" content="index, follow">
<meta name="theme-color" content="#c0392b">
<meta property="og:title" content="<?= htmlspecialchars($pageMetaTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageMetaDesc) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= htmlspecialchars($siteUrl . $_SERVER['REQUEST_URI']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/main.css?v=<?= APP_VERSION ?? '1.0.0' ?>">
<link rel="icon" type="image/png" href="/assets/img/favicon.png">
</head>
<body>

<header class="site-header">
  <div class="container">
    <div class="header-inner">
      <a href="/" class="logo-wrap">
        <?php
        $logo = getSetting('site_logo', '');
        if ($logo): ?>
        <img class="logo-img" src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($siteTitle) ?>">
        <?php else: ?>
        <div style="width:44px;height:44px;background:#c0392b;border-radius:8px;display:flex;align-items:center;justify-content:center;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polygon points="12 2 22 19 2 19"/></svg>
        </div>
        <?php endif; ?>
        <div class="logo-text">
          <span class="name">PARSAL METAL</span>
          <span class="tagline"><?= htmlspecialchars($siteSlogan) ?></span>
        </div>
      </a>

      <nav class="main-nav">
        <?php foreach ($nav as $slug => $label): ?>
        <a href="<?= $slug === 'home' ? '/' : '/?page=' . $slug ?>"
           class="<?= $currentPage === $slug || ($slug === 'home' && $currentPage === 'home') ? 'active' : '' ?>">
          <?= $label ?>
        </a>
        <?php endforeach; ?>
      </nav>

      <div class="header-cta">
        <?php if ($sitePhone): ?>
        <a href="tel:<?= preg_replace('/\D/', '', $sitePhone) ?>" class="btn-phone">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.81 19.79 19.79 0 0117.7 2a2 2 0 012.28 2v3"/></svg>
          <?= htmlspecialchars($sitePhone) ?>
        </a>
        <?php endif; ?>
        <a href="/?page=teklif" class="btn-quote">Teklif Al</a>
      </div>

      <button class="hamburger" aria-label="Menü">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<nav class="mobile-nav">
  <?php foreach ($nav as $slug => $label): ?>
  <a href="<?= $slug === 'home' ? '/' : '/?page=' . $slug ?>">
    <?= $label ?>
  </a>
  <?php endforeach; ?>
  <?php if ($sitePhone): ?>
  <a href="tel:<?= preg_replace('/\D/', '', $sitePhone) ?>" style="color:var(--accent)">
    📞 <?= htmlspecialchars($sitePhone) ?>
  </a>
  <?php endif; ?>
  <div class="m-cta">
    <a href="/?page=teklif" class="btn-quote" style="display:block;text-align:center;padding:14px">Teklif Al</a>
  </div>
</nav>

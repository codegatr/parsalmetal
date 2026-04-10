<?php
if (!defined('ROOT')) define('ROOT', dirname(__DIR__));
require_once ROOT . '/includes/db.php';
require_once ROOT . '/includes/functions.php';

$siteTitle  = getSetting('site_title',  'Parsal Metal');
$siteSlogan = getSetting('site_slogan', 'Alüminyum Doğrama ve Dış Cephe Sistemleri');
$sitePhone  = getSetting('site_phone',  '');
$siteLogo   = getSetting('site_logo',   '');
$siteUrl    = defined('SITE_URL') ? SITE_URL : '';

$pageMetaTitle = $pageMetaTitle ?? getSetting('meta_title', $siteTitle);
$pageMetaDesc  = $pageMetaDesc  ?? getSetting('meta_description', '');

$currentPage = $_GET['page'] ?? 'home';

$nav = [
    'home'       => 'Ana Sayfa',
    'urunler'    => 'Ürünler',
    'hizmetler'  => 'Hizmetler',
    'hakkimizda' => 'Hakkımızda',
    'iletisim'   => 'İletişim',
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?= htmlspecialchars($pageMetaTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageMetaDesc) ?>">
<meta name="robots" content="index, follow">
<meta name="theme-color" content="#c0392b">
<meta property="og:title" content="<?= htmlspecialchars($pageMetaTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageMetaDesc) ?>">
<meta property="og:type" content="website">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/main.css?v=<?= defined('APP_VERSION') ? APP_VERSION : '1' ?>">
</head>
<body>

<header class="site-header" id="siteHeader">
  <div class="container">
    <div class="header-inner">

      <!-- Logo -->
      <a href="/" class="logo-wrap" aria-label="<?= htmlspecialchars($siteTitle) ?>">
        <?php if ($siteLogo): ?>
        <img src="<?= htmlspecialchars($siteLogo) ?>"
             alt="<?= htmlspecialchars($siteTitle) ?>"
             class="site-logo-img">
        <?php else: ?>
        <div class="logo-icon-wrap">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polygon points="12 2 22 19 2 19"/></svg>
        </div>
        <div class="logo-text">
          <span class="logo-name">PARSAL METAL</span>
          <span class="logo-tagline"><?= htmlspecialchars($siteSlogan) ?></span>
        </div>
        <?php endif; ?>
      </a>

      <!-- Nav -->
      <nav class="main-nav" role="navigation">
        <?php foreach ($nav as $slug => $label): ?>
        <?php $href = $slug === 'home' ? '/' : '/?page=' . $slug; ?>
        <?php $active = ($currentPage === $slug) || ($slug === 'home' && $currentPage === 'home'); ?>
        <a href="<?= $href ?>"
           class="nav-link<?= $active ? ' nav-link--active' : '' ?>">
          <?= $label ?>
        </a>
        <?php endforeach; ?>
      </nav>

      <!-- CTA -->
      <div class="header-actions">
        <?php if ($sitePhone): ?>
        <a href="tel:<?= preg_replace('/\D/', '', $sitePhone) ?>"
           class="header-phone">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.81 19.79 19.79 0 0117.7 2a2 2 0 012.28 2v3"/></svg>
          <?= htmlspecialchars($sitePhone) ?>
        </a>
        <?php endif; ?>
        <a href="/?page=teklif" class="header-cta-btn">Teklif Al</a>
      </div>

      <!-- Hamburger -->
      <button class="hamburger" id="hamburger" aria-label="Menü aç/kapat" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<!-- Mobile Nav -->
<div class="mobile-nav" id="mobileNav" role="dialog" aria-modal="true">
  <div class="mobile-nav-inner">
    <?php foreach ($nav as $slug => $label): ?>
    <a href="<?= $slug === 'home' ? '/' : '/?page=' . $slug ?>"
       class="mobile-nav-link">
      <?= $label ?>
    </a>
    <?php endforeach; ?>
    <div class="mobile-nav-footer">
      <?php if ($sitePhone): ?>
      <a href="tel:<?= preg_replace('/\D/', '', $sitePhone) ?>" class="mobile-phone-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.81 19.79 19.79 0 0117.7 2a2 2 0 012.28 2v3"/></svg>
        <?= htmlspecialchars($sitePhone) ?>
      </a>
      <?php endif; ?>
      <a href="/?page=teklif" class="header-cta-btn" style="display:block;text-align:center;margin-top:12px">Teklif Al</a>
    </div>
  </div>
</div>

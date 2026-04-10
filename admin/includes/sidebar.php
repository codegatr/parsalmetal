<?php
// Değişkenler admin_init.php'den gelir:
// $admin, $newQuotes, $newContacts, $siteTitle, $currentMod

$currentMod = $currentMod ?? basename($_SERVER['PHP_SELF'], '.php');

$menu = [
  ['mod'=>'dashboard',    'icon'=>'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z', 'label'=>'Dashboard'],
  ['sep'=>'ICERIK'],
  ['mod'=>'slider',       'icon'=>'M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3M8 21H5a2 2 0 01-2-2v-3m18 0v3a2 2 0 01-2 2h-3', 'label'=>'Slider'],
  ['mod'=>'ürünler',      'icon'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10', 'label'=>'Ürünler'],
  ['mod'=>'kategoriler',  'icon'=>'M4 6h16M4 12h16M4 18h7', 'label'=>'Kategoriler'],
  ['mod'=>'hizmetler',    'icon'=>'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z', 'label'=>'Hizmetler'],
  ['mod'=>'referanslar',  'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label'=>'Referanslar'],
  ['mod'=>'istatistikler','icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label'=>'Istatistikler'],
  ['mod'=>'sayfalar',     'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label'=>'Sayfalar'],
  ['sep'=>'TALEPLER'],
  ['mod'=>'talepler',     'icon'=>'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'label'=>'Teklif Talepleri', 'badge'=>$newQuotes ?? 0],
  ['mod'=>'mesajlar',     'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label'=>'İletişim Mesajlari', 'badge'=>$newContacts ?? 0],
  ['sep'=>'SISTEM'],
  ['mod'=>'ayarlar',      'icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label'=>'Site Ayarlari'],
  ['mod'=>'guncelleme',   'icon'=>'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'label'=>'Güncelleme'],
  ['mod'=>'kullanicilar', 'icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'label'=>'Kullanicilar'],
];
?>
<aside class="sidebar" id="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><polygon points="12 2 22 19 2 19"/></svg>
    </div>
    <div class="sb-logo-text">
      <div class="site-name">PARSAL METAL</div>
      <div class="panel-label">Admin Panel</div>
    </div>
  </div>
  <nav style="padding:12px 0;flex:1">
    <?php foreach ($menu as $item): ?>
      <?php if (isset($item['sep'])): ?>
      <div class="sb-section-label"><?= $item['sep'] ?></div>
      <?php else: ?>
      <ul class="sb-nav"><li>
        <a href="/admin/<?= $item['mod'] ?>.php"
           class="<?= $currentMod === $item['mod'] ? 'active' : '' ?>">
          <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="<?= $item['icon'] ?>"/>
          </svg>
          <?= $item['label'] ?>
          <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
          <span class="sb-badge"><?= (int)$item['badge'] ?></span>
          <?php endif; ?>
        </a>
      </li></ul>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>
  <div class="sb-bottom">
    <ul class="sb-nav"><li>
      <a href="/admin/logout.php" style="color:rgba(255,255,255,.4)">
        <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
        </svg>
        Çıkış Yap
      </a>
    </li></ul>
    <div style="padding:8px 14px;font-size:11px;color:rgba(255,255,255,.2)">
      v<?= defined('APP_VERSION') ? APP_VERSION : '?' ?> &bull; CODEGA
    </div>
  </div>
</aside>

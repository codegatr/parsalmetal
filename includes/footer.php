<?php
$siteTitle   = getSetting('site_title',  'Parsal Metal Alüminyum');
$sitePhone   = getSetting('site_phone',  '');
$siteEmail   = getSetting('site_email',  '');
$siteAddress = getSetting('site_address','');
$footerText  = getSetting('footer_text', '© ' . date('Y') . ' ' . $siteTitle);
$cookieBar   = getSetting('cookie_bar', '1');

// Services for footer
$pdo = getDB();
$services = $pdo->query('SELECT name, slug FROM ' . p() . 'services WHERE is_active=1 ORDER BY sort_order LIMIT 5')->fetchAll();
$products = $pdo->query('SELECT name, slug FROM ' . p() . 'products WHERE is_active=1 AND is_featured=1 ORDER BY sort_order LIMIT 5')->fetchAll();
?>

<footer class="site-footer">
  <div class="footer-top">
    <div class="container">
      <div class="footer-grid">

        <!-- Marka -->
        <div class="footer-brand">
          <a href="/" class="logo-wrap" style="display:inline-flex;margin-bottom:4px;">
            <?php $logo = getSetting('site_logo',''); ?>
            <?php if ($logo): ?>
            <img class="logo-img" src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($siteTitle) ?>" style="height:40px;">
            <?php else: ?>
            <div style="width:40px;height:40px;background:#c0392b;border-radius:8px;display:flex;align-items:center;justify-content:center;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polygon points="12 2 22 19 2 19"/></svg>
            </div>
            <?php endif; ?>
            <div class="logo-text" style="margin-left:10px">
              <span class="name">PARSAL METAL</span>
            </div>
          </a>
          <p class="footer-desc">Yüksek kaliteli metal ve alüminyum ürünleri ile sektörün güvenilir tedarikçisi. Projelerinize özel çözümler sunuyoruz.</p>
          <div class="footer-social">
            <?php
            $socials = [
              'facebook'  => getSetting('social_facebook',''),
              'instagram' => getSetting('social_instagram',''),
              'linkedin'  => getSetting('social_linkedin',''),
              'youtube'   => getSetting('social_youtube',''),
            ];
            $icons = [
              'facebook'  => '<path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>',
              'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>',
              'linkedin'  => '<path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',
              'youtube'   => '<path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/>',
            ];
            foreach ($socials as $name => $url):
              if (!$url) continue; ?>
            <a href="<?= htmlspecialchars($url) ?>" class="social-link" target="_blank" rel="noopener" title="<?= ucfirst($name) ?>">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $icons[$name] ?></svg>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Hızlı Linkler -->
        <div>
          <h4 class="footer-title">Hızlı Erişim</h4>
          <div class="footer-links">
            <a href="/">Ana Sayfa</a>
            <a href="/?page=hakkimizda">Hakkımızda</a>
            <a href="/?page=urunler">Ürünler</a>
            <a href="/?page=hizmetler">Hizmetler</a>
            <a href="/?page=teklif">Teklif Al</a>
            <a href="/?page=iletisim">İletişim</a>
          </div>
        </div>

        <!-- Hizmetler -->
        <div>
          <h4 class="footer-title">Hizmetlerimiz</h4>
          <div class="footer-links">
            <?php foreach ($services as $s): ?>
            <a href="/?page=hizmetler&slug=<?= htmlspecialchars($s['slug']) ?>"><?= htmlspecialchars($s['name']) ?></a>
            <?php endforeach; ?>
            <?php if (empty($services)): ?>
            <a href="/?page=hizmetler">Alüminyum İmalat</a>
            <a href="/?page=hizmetler">Metal İşleme</a>
            <a href="/?page=hizmetler">Yüzey İşlem</a>
            <?php endif; ?>
          </div>
        </div>

        <!-- İletişim -->
        <div>
          <h4 class="footer-title">İletişim</h4>
          <?php if ($siteAddress): ?>
          <div class="footer-contact-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span><?= nl2br(htmlspecialchars($siteAddress)) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($sitePhone): ?>
          <div class="footer-contact-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.81 19.79 19.79 0 0117.7 2a2 2 0 012.28 2v3"/></svg>
            <span><a href="tel:<?= preg_replace('/\D/','',$sitePhone) ?>" style="color:inherit"><?= htmlspecialchars($sitePhone) ?></a></span>
          </div>
          <?php endif; ?>
          <?php if ($siteEmail): ?>
          <div class="footer-contact-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <span><a href="mailto:<?= htmlspecialchars($siteEmail) ?>" style="color:inherit"><?= htmlspecialchars($siteEmail) ?></a></span>
          </div>
          <?php endif; ?>
          <a href="/?page=teklif" class="btn-main" style="margin-top:20px;font-size:13px;padding:11px 20px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.81 19.79 19.79 0 0117.7 2a2 2 0 012.28 2v3"/></svg>
            Teklif Talep Et
          </a>
        </div>

      </div>
    </div>
  </div>

  <div class="container">
    <div class="footer-bottom">
      <p class="footer-copy"><?= htmlspecialchars($footerText) ?></p>
      <div class="footer-legal">
        <a href="/?page=kvkk">KVKK</a>
        <a href="/?page=gizlilik">Gizlilik</a>
        <a href="/?page=cerez">Çerez Politikası</a>
      </div>
      <p class="footer-codega">Tasarım ve Güncelleme: <a href="https://codega.com.tr" target="_blank" rel="noopener">CODEGA</a></p>
    </div>
  </div>
</footer>

<?php if ($cookieBar === '1'): ?>
<div class="cookie-bar" id="cookieBar">
  <div class="cookie-inner">
    <p class="cookie-text">
      Bu web sitesi, size daha iyi bir deneyim sunmak için çerezler kullanmaktadır. 
      Devam ederek <a href="/?page=cerez">Çerez Politikamızı</a> kabul etmiş sayılırsınız.
    </p>
    <div class="cookie-btns">
      <button class="cookie-accept">Kabul Et</button>
      <button class="cookie-reject">Reddet</button>
    </div>
  </div>
</div>
<?php endif; ?>

<button class="back-to-top" aria-label="Yukarı çık" style="position:fixed;bottom:24px;right:24px;z-index:999;width:44px;height:44px;border-radius:50%;background:#c0392b;color:#fff;border:none;font-size:20px;display:none;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(192,57,43,.4);cursor:pointer;transition:.3s">↑</button>

<style>
.back-to-top.show{display:flex!important}
input.input-error,textarea.input-error,select.input-error{border-color:#c0392b!important;box-shadow:0 0 0 3px rgba(192,57,43,.1)!important}
</style>

<script src="/assets/js/main.js?v=<?= APP_VERSION ?? '1.0.0' ?>"></script>
</body>
</html>

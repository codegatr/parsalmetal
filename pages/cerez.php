<?php
require_once ROOT . '/includes/header.php';
$pdo = getDB();
$pg  = $pdo->query("SELECT * FROM " . p() . "pages WHERE slug='cerez'")->fetch();
$pageMetaTitle = ($pg['meta_title'] ?? '') ?: 'Çerez Politikası - ' . getSetting('site_title');
$pageMetaDesc  = $pg['meta_description'] ?? '';
$siteTitle = getSetting('site_title', 'Parsal Metal');
$updatedAt = $pg['updated_at'] ?? date('Y-m-d');
?>
<style>
.legal-wrap{max-width:860px;margin:60px auto;padding:0 24px 80px}
.legal-header{background:linear-gradient(135deg,#1a1a1a 0%,#2c3e50 100%);border-radius:16px;padding:48px;margin-bottom:32px;position:relative;overflow:hidden}
.legal-header::before{content:'';position:absolute;top:-40px;right:-40px;width:200px;height:200px;background:rgba(192,57,43,.15);border-radius:50%}
.legal-header-icon{width:56px;height:56px;background:rgba(192,57,43,.2);border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:20px}
.legal-header h1{font-size:32px;font-weight:800;color:#fff;margin-bottom:10px;position:relative}
.legal-header p{color:rgba(255,255,255,.6);font-size:15px;line-height:1.6;position:relative}
.legal-meta{display:flex;align-items:center;gap:20px;margin-top:20px;position:relative}
.legal-meta-item{display:flex;align-items:center;gap:6px;font-size:12px;color:rgba(255,255,255,.45)}
.legal-section{display:flex;gap:20px;padding:28px;background:#fff;border-radius:12px;border:1px solid #eaedf0;margin-bottom:16px;transition:.2s}
.legal-section:hover{border-color:#c0392b;box-shadow:0 4px 20px rgba(0,0,0,.06)}
.legal-section-icon{width:40px;height:40px;background:rgba(192,57,43,.08);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px}
.legal-section-title{font-size:16px;font-weight:700;color:#1a1a2e;margin-bottom:10px}
.legal-section-content{font-size:14px;color:#6b7280;line-height:1.8}
.legal-section-content ul{margin:8px 0 0 16px}
.legal-section-content li{margin-bottom:4px}
.legal-section-content strong{color:#374151}
.legal-db-content{background:#fff;border-radius:12px;border:1px solid #eaedf0;padding:32px;margin-bottom:16px}
.legal-db-content h2{font-size:20px;font-weight:700;margin-bottom:12px;color:#1a1a2e}
.legal-db-content h3{font-size:16px;font-weight:700;margin-bottom:8px;margin-top:20px;color:#1a1a2e}
.legal-db-content p{font-size:14px;color:#6b7280;line-height:1.8;margin-bottom:10px}
.legal-db-content ul{margin:8px 0 10px 20px}
.legal-db-content li{font-size:14px;color:#6b7280;line-height:1.7;margin-bottom:4px}
.legal-footer{background:#f8f9fa;border-radius:12px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
.legal-footer p{font-size:13px;color:#9ca3af}
.legal-contact-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:#c0392b;color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:.2s}
.legal-contact-btn:hover{background:#96281b}
@media(max-width:640px){.legal-wrap{padding:0 16px 60px}.legal-header{padding:32px 24px}.legal-header h1{font-size:24px}.legal-section{flex-direction:column;gap:12px}}
</style>

<div class="legal-wrap">
  <!-- Header -->
  <div class="legal-header">
    <div class="legal-header-icon">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 110 20 10 10 0 010-20z"/></svg>
    </div>
    <h1>Çerez Politikası</h1>
    <p><?= htmlspecialchars($siteTitle) ?> olarak kisisel verilerinizi ve gizliliginizi onemsiyor, yasal yukumluluklerimizi eksiksiz yerine getiriyoruz.</p>
    <div class="legal-meta">
      <div class="legal-meta-item">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Son guncelleme: <?= date('d.m.Y', strtotime($updatedAt)) ?>
      </div>
      <div class="legal-meta-item">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        6698 sayili KVKK kapsaminda
      </div>
    </div>
  </div>

  <!-- DB'deki özel içerik varsa göster -->
  <?php if (!empty($pg['content'])): ?>
  <div class="legal-db-content"><?= $pg['content'] ?></div>
  <?php else: ?>
  <!-- Varsayılan bölümler -->
  
        <div class="legal-section">
          <div class="legal-section-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 110 20 10 10 0 010-20z"/></svg>
          </div>
          <div class="legal-section-body">
            <h3 class="legal-section-title">Çerez Nedir?</h3>
            <div class="legal-section-content"><p>Çerezler, web sitelerinin tarayıcınıza yerleştirdiği küçük metin dosyalarıdır. Oturum bilgilerini hatırlamak, tercihleri kaydetmek ve site performansını ölçmek için kullanılır.</p></div>
          </div>
        </div>
        <div class="legal-section">
          <div class="legal-section-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
          </div>
          <div class="legal-section-body">
            <h3 class="legal-section-title">Çerez Türleri</h3>
            <div class="legal-section-content"><ul>
          <li><strong>Zorunlu Çerezler:</strong> Sitenin çalışması için gereklidir, devre dışı bırakılamaz. Oturum yönetimi, güvenlik ve form verileri için kullanılır.</li>
          <li><strong>Analitik Çerezler:</strong> Site trafiğini ve kullanım istatistiklerini ölçer. Sayfa görüntüleme, kullanıcı davranışı analizi için kullanılır.</li>
          <li><strong>Tercih Çerezleri:</strong> Dil, tema gibi tercihlerinizi hatırlar.</li>
          <li><strong>Pazarlama Çerezleri:</strong> Yalnızca açık onayınızla aktifleşir.</li>
        </ul></div>
          </div>
        </div>
        <div class="legal-section">
          <div class="legal-section-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <div class="legal-section-body">
            <h3 class="legal-section-title">Çerezleri Yönetme</h3>
            <div class="legal-section-content"><p>Tarayıcı ayarlarınızdan çerezleri yönetebilirsiniz:</p>
        <ul>
          <li><strong>Chrome:</strong> Ayarlar → Gizlilik ve güvenlik → Çerezler</li>
          <li><strong>Firefox:</strong> Ayarlar → Gizlilik ve Güvenlik → Çerezler</li>
          <li><strong>Safari:</strong> Tercihler → Gizlilik → Çerezleri Yönet</li>
          <li><strong>Edge:</strong> Ayarlar → Çerezler ve site izinleri</li>
        </ul>
        <p style="margin-top:8px">Zorunlu çerezlerin engellenmesi site işlevselliğini olumsuz etkileyebilir.</p></div>
          </div>
        </div>
        <div class="legal-section">
          <div class="legal-section-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <div class="legal-section-body">
            <h3 class="legal-section-title">KVKK Uyumu</h3>
            <div class="legal-section-content"><p>Çerez politikamız, 6698 sayılı KVKK ve AB Genel Veri Koruma Tüzüğü (GDPR) ile uyumludur. Analitik ve pazarlama çerezleri için açık onayınız alınmaktadır.</p></div>
          </div>
        </div>
  <?php endif; ?>

  <!-- Footer -->
  <div class="legal-footer">
    <p>Sorulariniz icin bizimle iletisime gecebilirsiniz.</p>
    <a href="/?page=iletisim" class="legal-contact-btn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      Iletisime Gec
    </a>
  </div>
</div>

<?php require_once ROOT . '/includes/footer.php'; ?>

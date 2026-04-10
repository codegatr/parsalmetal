<?php
require_once ROOT . '/includes/header.php';
$pdo = getDB();
$pg  = $pdo->query("SELECT * FROM " . p() . "pages WHERE slug='gizlilik'")->fetch();
$pageMetaTitle = ($pg['meta_title'] ?? '') ?: 'Gizlilik Politikası - ' . getSetting('site_title');
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
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>
    <h1>Gizlilik Politikası</h1>
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
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </div>
          <div class="legal-section-body">
            <h3 class="legal-section-title">Hangi Bilgileri Topluyoruz?</h3>
            <div class="legal-section-content"><ul><li><strong>İletişim Bilgileri:</strong> Formlar aracılığıyla ad, e-posta, telefon</li><li><strong>Kullanım Verileri:</strong> Ziyaret edilen sayfalar, tıklamalar, oturum süresi</li><li><strong>Teknik Veriler:</strong> IP adresi, tarayıcı türü, cihaz bilgisi</li><li><strong>Çerezler:</strong> Oturum ve tercih çerezleri</li></ul></div>
          </div>
        </div>
        <div class="legal-section">
          <div class="legal-section-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="legal-section-body">
            <h3 class="legal-section-title">Bilgilerinizi Nasıl Kullanıyoruz?</h3>
            <div class="legal-section-content"><ul><li>Teklif ve hizmet taleplerini karşılamak</li><li>Müşteri desteği sağlamak</li><li>Web sitesi deneyimini iyileştirmek</li><li>Yasal yükümlülükleri yerine getirmek</li><li>Güvenliği sağlamak ve dolandırıcılığı önlemek</li></ul></div>
          </div>
        </div>
        <div class="legal-section">
          <div class="legal-section-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
          </div>
          <div class="legal-section-body">
            <h3 class="legal-section-title">Güvenlik</h3>
            <div class="legal-section-content"><p>Verilerinizi korumak için endüstri standardı SSL şifreleme, güvenli sunucular ve erişim kontrolleri kullanıyoruz. Düzenli güvenlik denetimleri gerçekleştirilmektedir.</p></div>
          </div>
        </div>
        <div class="legal-section">
          <div class="legal-section-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
          </div>
          <div class="legal-section-body">
            <h3 class="legal-section-title">Üçüncü Taraf Bağlantıları</h3>
            <div class="legal-section-content"><p>Web sitemiz üçüncü taraf sitelere bağlantılar içerebilir. Bu sitelerin gizlilik politikalarından sorumlu değiliz. Bağlantıyı takip etmeden önce ilgili sitenin politikasını incelemenizi öneririz.</p></div>
          </div>
        </div>
        <div class="legal-section">
          <div class="legal-section-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          </div>
          <div class="legal-section-body">
            <h3 class="legal-section-title">Değişiklikler</h3>
            <div class="legal-section-content"><p>Bu politika zaman zaman güncellenebilir. Önemli değişiklikler e-posta ile bildirilecektir. Politikayı düzenli olarak incelemenizi öneririz.</p></div>
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

<?php
require_once ROOT . '/includes/header.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$pdo  = getDB();
$sent = false; $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$name) $errors[] = 'Ad Soyad zorunludur.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Gecerli e-posta giriniz.';
    if (!$message) $errors[] = 'Mesaj giriniz.';
    if (empty($errors)) {
        $pdo->prepare('INSERT INTO ' . p() . 'contacts (name,email,phone,subject,message) VALUES (?,?,?,?,?)')
            ->execute([$name,$email,$phone,$subject,$message]);
        $siteEmail = getSetting('site_email','');
        if ($siteEmail) {
            @mail($siteEmail, 'Iletisim: ' . $subject, "Ad: $name\nEmail: $email\nTel: $phone\nMesaj:\n$message", 'From: ' . $siteEmail);
        }
        $sent = true;
    }
}
$sitePhone   = getSetting('site_phone','');
$siteEmail   = getSetting('site_email','');
$siteAddress = getSetting('site_address','');
$mapsEmbed   = getSetting('maps_embed','');
$pageMetaTitle = 'Iletisim - ' . getSetting('site_title');
$pageMetaDesc  = 'Bize ulasin, sorularinizi yanitleyelim.';
?>
    <h1>Bize Ulasin</h1>
    <p>Sorulariniz icin her zaman buradayiz.</p>
  </div>
</div>

<section class="section" style="margin-top:var(--header-h)">
  <div class="container">
    <div class="contact-grid">
      <!-- Info -->
      <div>
        <div class="section-label">Iletisim Bilgileri</div>
        <h2 class="section-title" style="margin-bottom:32px">Sizinle <span>Konusalim</span></h2>
        <?php if ($sitePhone): ?>
        <div class="contact-card">
          <div class="contact-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.81 19.79 19.79 0 0117.7 2a2 2 0 012.28 2v3"/></svg></div>
          <div>
            <div class="contact-label">Telefon</div>
            <div class="contact-val"><a href="tel:<?= preg_replace('/\D','',$sitePhone) ?>"><?= htmlspecialchars($sitePhone) ?></a></div>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($siteEmail): ?>
        <div class="contact-card">
          <div class="contact-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
          <div>
            <div class="contact-label">E-posta</div>
            <div class="contact-val"><a href="mailto:<?= htmlspecialchars($siteEmail) ?>"><?= htmlspecialchars($siteEmail) ?></a></div>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($siteAddress): ?>
        <div class="contact-card">
          <div class="contact-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
          <div>
            <div class="contact-label">Adres</div>
            <div class="contact-val"><?= nl2br(htmlspecialchars($siteAddress)) ?></div>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($mapsEmbed): ?>
        <div class="map-wrap" style="margin-top:28px"><?= $mapsEmbed ?></div>
        <?php endif; ?>
      </div>
      <!-- Form -->
      <div>
        <?php if ($sent): ?>
        <div class="alert alert-success">Mesajiniz alindi, en kisa surede donecegiz!</div>
        <?php endif; ?>
        <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
        <div style="background:#f8f8f8;border-radius:16px;padding:40px;border:1px solid #eaedf0">
          <h3 style="font-size:20px;font-weight:700;margin-bottom:24px">Mesaj Gonderin</h3>
          <form method="POST" id="contact-form">
            <div class="form-row">
              <div class="form-group">
                <label>Ad Soyad *</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>E-posta *</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Telefon</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Konu</label>
                <input type="text" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" placeholder="Mesaj konusu">
              </div>
            </div>
            <div class="form-group">
              <label>Mesaj *</label>
              <textarea name="message" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn-submit" style="width:100%;justify-content:center">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Gonder
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once ROOT . '/includes/footer.php'; ?>

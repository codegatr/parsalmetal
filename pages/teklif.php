<?php
require_once ROOT . '/includes/header.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$pdo = getDB();
$products = $pdo->query('SELECT name FROM ' . p() . 'products WHERE is_active=1 ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
$selectedProduct = htmlspecialchars($_GET['product'] ?? '');

$sent = false; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $product = trim($_POST['product'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$name)  $errors[] = 'Ad Soyad zorunludur.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Gecerli bir e-posta giriniz.';
    if (!$phone) $errors[] = 'Telefon numarasi zorunludur.';
    if (!$message) $errors[] = 'Mesaj alani zorunludur.';
    if (empty($errors)) {
        $pdo->prepare('INSERT INTO ' . p() . 'quotes (name,company,email,phone,product,message,ip_address) VALUES (?,?,?,?,?,?,?)')
            ->execute([$name, $company, $email, $phone, $product, $message, $_SERVER['REMOTE_ADDR']]);
        // Mail
        $siteEmail = getSetting('site_email','');
        if ($siteEmail) {
            $subj = 'Yeni Teklif Talebi - ' . $name;
            $body  = "Ad Soyad: $name\nFirma: $company\nE-posta: $email\nTelefon: $phone\nUrun: $product\nMesaj:\n$message";
            @mail($siteEmail, $subj, $body, 'From: ' . $siteEmail);
        }
        $sent = true;
    }
}
$pageMetaTitle = 'Teklif Alin - ' . getSetting('site_title');
$pageMetaDesc  = 'Ucretsiz fiyat teklifi icin hemen basvurun.';
?>
    <h1>Ucretsiz Teklif Alin</h1>
    <p>Formumu doldurun, uzman ekibimiz en kisa surede sizinle iletisime gececek.</p>
  </div>
</div>

<section class="section" style="margin-top:var(--header-h)">
  <div class="container" style="max-width:900px">
    <?php if ($sent): ?>
    <div class="alert alert-success" style="font-size:16px;padding:20px 24px">
      ✅ Teklif talebiniz alindi! En kisa surede sizinle iletisime gececegiz.
    </div>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <div class="quote-form-wrap">
      <div class="quote-form-head">
        <h2>Teklif Talep Formu</h2>
        <p>Tum alanlar dogruluk icin onemlidir. Bilgileriniz gizli tutulur.</p>
      </div>
      <div class="quote-form-body">
        <form method="POST" id="quote-form">
          <div class="form-row">
            <div class="form-group">
              <label>Ad Soyad *</label>
              <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required placeholder="Adiniz ve soyadiniz">
            </div>
            <div class="form-group">
              <label>Firma Adi</label>
              <input type="text" name="company" value="<?= htmlspecialchars($_POST['company'] ?? '') ?>" placeholder="Firmanizin adi">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>E-posta Adresi *</label>
              <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="ornek@firma.com">
            </div>
            <div class="form-group">
              <label>Telefon *</label>
              <input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required placeholder="05xx xxx xx xx">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Ilgili Urun / Hizmet</label>
              <select name="product">
                <option value="">Secin (opsiyonel)</option>
                <?php foreach ($products as $pr): ?>
                <option value="<?= htmlspecialchars($pr) ?>" <?= $selectedProduct === $pr ? 'selected' : '' ?>><?= htmlspecialchars($pr) ?></option>
                <?php endforeach; ?>
                <option value="Diger">Diger</option>
              </select>
            </div>
            <div class="form-group">
              <label>&nbsp;</label>
              <div style="padding:12px 16px;background:#f8f8f8;border-radius:8px;font-size:13px;color:#666;border:1.5px solid #eee">
                Tablette veya telefondan da kolayca teklif gonderebilirsiniz.
              </div>
            </div>
          </div>
          <div class="form-group full">
            <label>Mesaj ve Detaylar *</label>
            <textarea name="message" rows="6" required placeholder="Ihtiyacinizi, miktar, olcu, malzeme tipi gibi detaylari belirtin..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>
          <div style="margin-bottom:16px">
            <label style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:#666;cursor:pointer">
              <input type="checkbox" required style="margin-top:3px;width:auto">
              <span><a href="/?page=kvkk" target="_blank" style="color:#c0392b">KVKK Aydinlatma Metni</a>'ni okudum, kisisel verilerimin islenebilmesine onay veriyorum.</span>
            </label>
          </div>
          <div class="form-submit">
            <button type="submit" class="btn-submit">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Teklif Gonder
            </button>
            <p class="form-note">* Zorunlu alanlar. Bilgileriniz ucuncu taraflarla paylasilmaz.</p>
          </div>
        </form>
      </div>
    </div>

    <!-- Info Cards -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:40px">
      <?php
      $infoCards = [
        ['ico'=>'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z','title'=>'Guvenli','desc'=>'Bilgileriniz SSL ile sifrelenir.'],
        ['ico'=>'M12 2a10 10 0 110 20 10 10 0 010-20z M12 6v6l4 2','title'=>'Hizli Yanit','desc'=>'24 saat icinde geri doneriz.'],
        ['ico'=>'M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07','title'=>'Uzman Destek','desc'=>'Deneyimli ekip ile konusum.'],
      ];
      foreach ($infoCards as $c): ?>
      <div style="text-align:center;padding:28px 20px;background:#f8f8f8;border-radius:12px;border:1px solid #eee">
        <div style="width:48px;height:48px;background:rgba(192,57,43,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round"><path d="<?= $c['ico'] ?>"/></svg>
        </div>
        <h4 style="font-size:15px;font-weight:700;margin-bottom:6px"><?= $c['title'] ?></h4>
        <p style="font-size:13px;color:#888"><?= $c['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once ROOT . '/includes/footer.php'; ?>

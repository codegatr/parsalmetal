<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/config.php';
$pageTitle = 'Site Ayarlari';
require_once ROOT . '/admin/includes/sidebar.php';
require_once ROOT . '/includes/functions.php';
$pdo = getDB(); $flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['site_title','site_slogan','site_email','site_phone','site_address','site_url',
               'meta_title','meta_description','footer_text','github_repo','github_token',
               'cookie_bar','founded_year','about_short','mission_text','vision_text',
               'maps_embed','social_facebook','social_instagram','social_linkedin','social_youtube'];
    $stmt = $pdo->prepare('INSERT INTO ' . p() . 'settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
    foreach ($fields as $f) {
        $val = trim($_POST[$f] ?? '');
        $stmt->execute([$f, $val]);
    }
    // Logo upload
    if (!empty($_FILES['site_logo']['name'])) {
        $up = uploadImage($_FILES['site_logo'], 'media');
        if ($up) $stmt->execute(['site_logo', $up]);
    }
    // About image
    if (!empty($_FILES['about_image']['name'])) {
        $up = uploadImage($_FILES['about_image'], 'media');
        if ($up) $stmt->execute(['about_image', $up]);
    }
    flash('success','Ayarlar kaydedildi.');
    header('Location: /admin/ayarlar.php'); exit;
}

$s = [];
foreach (['site_title','site_slogan','site_email','site_phone','site_address','site_url',
          'meta_title','meta_description','footer_text','github_repo','github_token',
          'cookie_bar','founded_year','about_short','mission_text','vision_text',
          'maps_embed','social_facebook','social_instagram','social_linkedin','social_youtube',
          'site_logo','about_image'] as $k) {
    $s[$k] = getSetting($k, '');
}
?>
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ayarlar - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>"></head><body>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
<?php if (!empty($flash)): ?><div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
<form method="POST" enctype="multipart/form-data">

<!-- GENEL -->
<div class="page-actions" style="margin-bottom:16px"><h1>Site Ayarlari</h1><button type="submit" class="btn btn-primary">Kaydet</button></div>
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><div class="card-title">Genel Bilgiler</div></div>
  <div class="card-body">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Site Basligi</label>
        <input type="text" name="site_title" class="form-control" value="<?= htmlspecialchars($s['site_title']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Slogan</label>
        <input type="text" name="site_slogan" class="form-control" value="<?= htmlspecialchars($s['site_slogan']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">E-posta</label>
        <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars($s['site_email']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Telefon</label>
        <input type="text" name="site_phone" class="form-control" value="<?= htmlspecialchars($s['site_phone']) ?>">
      </div>
      <div class="form-group full">
        <label class="form-label">Adres</label>
        <textarea name="site_address" class="form-control" rows="2"><?= htmlspecialchars($s['site_address']) ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Site URL</label>
        <input type="url" name="site_url" class="form-control" value="<?= htmlspecialchars($s['site_url']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Kurulis Yili (Hakkimizda rozeti)</label>
        <input type="number" name="founded_year" class="form-control" value="<?= htmlspecialchars($s['founded_year'] ?: '25') ?>">
        <span class="form-hint">Girilen rakam + "yil" yazisi gorunur.</span>
      </div>
    </div>
  </div>
</div>

<!-- LOGO + GORSEL -->
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><div class="card-title">Gorsel ve Logo</div></div>
  <div class="card-body">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Site Logosu</label>
        <?php if ($s['site_logo']): ?><img src="<?= htmlspecialchars($s['site_logo']) ?>" id="logoPreview" style="max-height:60px;margin-bottom:8px;border-radius:6px"><?php else: ?><img id="logoPreview" src="" style="max-height:60px;margin-bottom:8px;border-radius:6px;display:none"><?php endif; ?>
        <div class="upload-area" onclick="document.getElementById('logoFile').click()">
          <p style="font-size:12px;color:#aaa">Logo yukle (PNG/SVG, saydam arkaplan)</p>
        </div>
        <input type="file" id="logoFile" name="site_logo" accept="image/*" data-preview="logoPreview" style="display:none">
      </div>
      <div class="form-group">
        <label class="form-label">Hakkimizda Gorseli</label>
        <?php if ($s['about_image']): ?><img src="<?= htmlspecialchars($s['about_image']) ?>" id="aboutImgPreview" style="max-height:80px;margin-bottom:8px;border-radius:6px"><?php else: ?><img id="aboutImgPreview" src="" style="max-height:80px;margin-bottom:8px;border-radius:6px;display:none"><?php endif; ?>
        <div class="upload-area" onclick="document.getElementById('aboutImgFile').click()">
          <p style="font-size:12px;color:#aaa">Hakkimizda bolumu sol gorsel</p>
        </div>
        <input type="file" id="aboutImgFile" name="about_image" accept="image/*" data-preview="aboutImgPreview" style="display:none">
      </div>
    </div>
  </div>
</div>

<!-- HAKKIMIZDA -->
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><div class="card-title">Hakkimizda / Misyon / Vizyon</div></div>
  <div class="card-body">
    <div class="form-group" style="margin-bottom:16px">
      <label class="form-label">Hakkimizda Kisa Aciklama (Ana sayfa)</label>
      <textarea name="about_short" class="form-control" rows="3"><?= htmlspecialchars($s['about_short']) ?></textarea>
    </div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Misyon</label>
        <textarea name="mission_text" class="form-control" rows="3"><?= htmlspecialchars($s['mission_text']) ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Vizyon</label>
        <textarea name="vision_text" class="form-control" rows="3"><?= htmlspecialchars($s['vision_text']) ?></textarea>
      </div>
    </div>
  </div>
</div>

<!-- META + FOOTER -->
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><div class="card-title">SEO ve Footer</div></div>
  <div class="card-body">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Meta Baslik (Ana sayfa)</label>
        <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($s['meta_title']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Meta Aciklama</label>
        <input type="text" name="meta_description" class="form-control" value="<?= htmlspecialchars($s['meta_description']) ?>">
      </div>
      <div class="form-group full">
        <label class="form-label">Footer Telif Metni</label>
        <input type="text" name="footer_text" class="form-control" value="<?= htmlspecialchars($s['footer_text']) ?>">
      </div>
      <div class="form-group full">
        <label class="form-label">Google Maps Embed Kodu</label>
        <textarea name="maps_embed" class="form-control" rows="3" placeholder="<iframe src=...></iframe>"><?= htmlspecialchars($s['maps_embed']) ?></textarea>
        <span class="form-hint">Google Maps &rsaquo; Paylasim &rsaquo; Harita Yerleştir &rsaquo; iframe kodunu buraya yapistirin.</span>
      </div>
      <div class="form-group">
        <label class="form-label">Cerez Bari</label>
        <select name="cookie_bar" class="form-control">
          <option value="1" <?= $s['cookie_bar']==='1'?'selected':'' ?>>Aktif</option>
          <option value="0" <?= $s['cookie_bar']==='0'?'selected':'' ?>>Kapali</option>
        </select>
      </div>
    </div>
  </div>
</div>

<!-- SOSYAL MEDYA -->
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><div class="card-title">Sosyal Medya</div></div>
  <div class="card-body">
    <div class="form-grid">
      <?php foreach ([['Facebook','social_facebook'],['Instagram','social_instagram'],['LinkedIn','social_linkedin'],['YouTube','social_youtube']] as [$l,$f]): ?>
      <div class="form-group">
        <label class="form-label"><?= $l ?> URL</label>
        <input type="url" name="<?= $f ?>" class="form-control" value="<?= htmlspecialchars($s[$f]) ?>" placeholder="https://...">
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- GITHUB -->
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><div class="card-title">GitHub Guncelleme Ayarlari</div></div>
  <div class="card-body">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">GitHub Repo</label>
        <input type="text" name="github_repo" class="form-control" value="<?= htmlspecialchars($s['github_repo']) ?>" placeholder="kullanici/repo">
      </div>
      <div class="form-group">
        <label class="form-label">GitHub Token</label>
        <input type="text" name="github_token" class="form-control" value="<?= htmlspecialchars($s['github_token']) ?>" placeholder="ghp_...">
      </div>
    </div>
  </div>
</div>

<div style="padding-bottom:32px">
  <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:15px">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
    Tum Ayarlari Kaydet
  </button>
</div>
</form>
</main>
<?php require ROOT . '/admin/includes/footer.php'; ?>

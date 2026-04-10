<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Slider Yonetimi';
$pdo = getDB();
$flash = getFlash();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// DELETE
if ($action === 'delete' && $id) {
    $row = $pdo->prepare('SELECT image FROM ' . p() . 'slider WHERE id=?');
    $row->execute([$id]);
    $img = $row->fetchColumn();
    if ($img && file_exists(ROOT . $img)) unlink(ROOT . $img);
    $pdo->prepare('DELETE FROM ' . p() . 'slider WHERE id=?')->execute([$id]);
    flash('success','Slider silindi.');
    header('Location: /admin/slider.php'); exit;
}

// TOGGLE
if ($action === 'toggle' && $id) {
    $pdo->prepare('UPDATE ' . p() . 'slider SET is_active = 1 - is_active WHERE id=?')->execute([$id]);
    header('Location: /admin/slider.php'); exit;
}

// SAVE (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $subtitle   = trim($_POST['subtitle'] ?? '');
    $btn_text   = trim($_POST['button_text'] ?? '');
    $btn_url    = trim($_POST['button_url'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active  = isset($_POST['is_active']) ? 1 : 0;

    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $uploaded = uploadImage($_FILES['image'], 'sliders');
        if ($uploaded) $image = $uploaded;
        else { flash('error','Görsel yuklenemedi. Max 5MB, JPG/PNG/WEBP.'); header('Location: /admin/slider.php?action=' . ($id ? 'edit&id='.$id : 'add')); exit; }
    }

    if ($id) {
        $existing = $pdo->prepare('SELECT image FROM ' . p() . 'slider WHERE id=?');
        $existing->execute([$id]);
        $oldImg = $existing->fetchColumn();
        if (!$image) $image = $oldImg;
        $pdo->prepare('UPDATE ' . p() . 'slider SET title=?,subtitle=?,button_text=?,button_url=?,sort_order=?,is_active=?,image=? WHERE id=?')
            ->execute([$title, $subtitle, $btn_text, $btn_url, $sort_order, $is_active, $image, $id]);
        flash('success','Slider güncellendi.');
    } else {
        $pdo->prepare('INSERT INTO ' . p() . 'slider (title,subtitle,button_text,button_url,sort_order,is_active,image) VALUES (?,?,?,?,?,?,?)')
            ->execute([$title, $subtitle, $btn_text, $btn_url, $sort_order, $is_active, $image]);
        flash('success','Slider eklendi.');
    }
    header('Location: /admin/slider.php'); exit;
}

$slides = $pdo->query('SELECT * FROM ' . p() . 'slider ORDER BY sort_order, id')->fetchAll();
$editing = null;
if (($action === 'edit') && $id) {
    $st = $pdo->prepare('SELECT * FROM ' . p() . 'slider WHERE id=?');
    $st->execute([$id]);
    $editing = $st->fetch();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Slider - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>">
</head>
<body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- FORM -->
<div class="page-actions">
  <h1><?= $action === 'edit' ? 'Slider Duzenle' : 'Yeni Slider Ekle' ?></h1>
  <a href="/admin/slider.php" class="btn btn-secondary">← Geri</a>
</div>
<div class="card">
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="form-group full">
          <label class="form-label">Baslik (HTML destekler) *</label>
          <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editing['title'] ?? '') ?>" placeholder="Metalin Gucu,&lt;br&gt;Alüminyumun Zarafeti" required>
          <span class="form-hint">Satir atlamak icin &lt;br&gt; kullanin.</span>
        </div>
        <div class="form-group full">
          <label class="form-label">Alt Baslik</label>
          <textarea name="subtitle" class="form-control" rows="2" placeholder="Kisa aciklama..."><?= htmlspecialchars($editing['subtitle'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Buton Metni</label>
          <input type="text" name="button_text" class="form-control" value="<?= htmlspecialchars($editing['button_text'] ?? 'Keşfet') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Buton Linki</label>
          <input type="text" name="button_url" class="form-control" value="<?= htmlspecialchars($editing['button_url'] ?? '/?page=urunler') ?>" placeholder="/?page=urunler">
        </div>
        <div class="form-group">
          <label class="form-label">Sira</label>
          <input type="number" name="sort_order" class="form-control" value="<?= (int)($editing['sort_order'] ?? 0) ?>" min="0">
        </div>
        <div class="form-group" style="justify-content:flex-end">
          <div class="form-check" style="margin-top:28px">
            <input type="checkbox" name="is_active" id="is_active" <?= ($editing['is_active'] ?? 1) ? 'checked' : '' ?>>
            <label for="is_active">Aktif</label>
          </div>
        </div>
        <div class="form-group full">
          <label class="form-label">Görsel (JPG/PNG/WEBP, max 5MB)</label>
          <?php if (!empty($editing['image'])): ?>
          <div style="margin-bottom:10px">
            <img src="<?= htmlspecialchars($editing['image']) ?>" id="imgPreview" style="max-height:180px;border-radius:8px;border:1px solid #eee">
          </div>
          <?php else: ?>
          <img id="imgPreview" src="" style="max-height:180px;border-radius:8px;display:none;margin-bottom:10px">
          <?php endif; ?>
          <div class="upload-area" onclick="document.getElementById('imageFile').click()">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="1.5" style="margin:0 auto 8px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <p style="font-size:13px;color:#888">Görsel secmek icin tiklayin</p>
            <p style="font-size:11px;color:#bbb;margin-top:4px">PNG, JPG, WEBP &bull; Maks 5MB</p>
          </div>
          <input type="file" id="imageFile" name="image" accept="image/*" data-preview="imgPreview" style="display:none">
          <span class="form-hint">Görsel secmezseniz mevcut görsel korunur. En iyi boyut: 1920x900px</span>
        </div>
      </div>
      <div style="margin-top:24px;display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <?= $action === 'edit' ? 'Güncelle' : 'Ekle' ?>
        </button>
        <a href="/admin/slider.php" class="btn btn-secondary">Iptal</a>
      </div>
    </form>
  </div>
</div>

<?php else: ?>
<!-- LIST -->
<div class="page-actions">
  <h1>Slider Yonetimi <span style="font-size:14px;font-weight:400;color:#888">(<?= count($slides) ?> slayt)</span></h1>
  <a href="/admin/slider.php?action=add" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Yeni Slider
  </a>
</div>
<div class="card">
  <div class="card-body" style="padding:0">
    <?php if (empty($slides)): ?>
    <div style="text-align:center;padding:60px;color:#999">
      <p>Henuz slider eklenmemis.</p>
      <a href="/admin/slider.php?action=add" class="btn btn-primary" style="margin-top:16px;display:inline-flex">Ilk Slideri Ekle</a>
    </div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Görsel</th><th>Baslik</th><th>Buton</th><th>Sira</th><th>Durum</th><th style="text-align:right">Islemler</th></tr></thead>
        <tbody>
          <?php foreach ($slides as $s): ?>
          <tr>
            <td>
              <?php if ($s['image']): ?>
              <img class="table-img" src="<?= htmlspecialchars($s['image']) ?>" alt="" style="width:100px;height:56px">
              <?php else: ?>
              <div class="table-no-img" style="width:100px;height:56px;border-radius:6px">🖼️</div>
              <?php endif; ?>
            </td>
            <td>
              <strong><?= strip_tags($s['title']) ?></strong>
              <br><span style="font-size:12px;color:#888"><?= htmlspecialchars(substr($s['subtitle'] ?? '', 0, 60)) ?></span>
            </td>
            <td>
              <span style="font-size:13px"><?= htmlspecialchars($s['button_text'] ?? '-') ?></span>
              <br><span style="font-size:11px;color:#aaa"><?= htmlspecialchars($s['button_url'] ?? '') ?></span>
            </td>
            <td><?= (int)$s['sort_order'] ?></td>
            <td>
              <a href="/admin/slider.php?action=toggle&id=<?= $s['id'] ?>">
                <span class="badge badge-<?= $s['is_active'] ? 'active' : 'passive' ?>"><?= $s['is_active'] ? '● Aktif' : '○ Pasif' ?></span>
              </a>
            </td>
            <td style="text-align:right">
              <div style="display:flex;gap:6px;justify-content:flex-end">
                <a href="/admin/slider.php?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Duzenle</a>
                <a href="/admin/slider.php?action=delete&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Bu slideri silmek istediginize emin misiniz?">Sil</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
</main>
<?php require ROOT . '/admin/includes/footer.php'; ?>

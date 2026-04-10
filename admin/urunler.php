<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Ürünler';
$pdo = getDB();
$flash = getFlash();
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    $r = $pdo->prepare('SELECT image FROM ' . p() . 'products WHERE id=?'); $r->execute([$id]);
    $img = $r->fetchColumn();
    if ($img && file_exists(ROOT . $img)) unlink(ROOT . $img);
    $pdo->prepare('DELETE FROM ' . p() . 'products WHERE id=?')->execute([$id]);
    flash('success','Ürün silindi.'); header('Location: /admin/ürünler.php'); exit;
}
if ($action === 'toggle' && $id) {
    $pdo->prepare('UPDATE ' . p() . 'products SET is_active=1-is_active WHERE id=?')->execute([$id]);
    header('Location: /admin/ürünler.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $cat      = (int)($_POST['category_id'] ?? 0);
    $slug     = trim($_POST['slug'] ?? '') ?: slugify($name);
    $short    = trim($_POST['short_desc'] ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $specs    = trim($_POST['specs'] ?? '');
    $sort     = (int)($_POST['sort_order'] ?? 0);
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $active   = isset($_POST['is_active']) ? 1 : 0;
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $up = uploadImage($_FILES['image'], 'products');
        if ($up) $image = $up;
    }
    if ($id) {
        $existing = $pdo->prepare('SELECT image FROM ' . p() . 'products WHERE id=?'); $existing->execute([$id]);
        if (!$image) $image = $existing->fetchColumn();
        $pdo->prepare('UPDATE ' . p() . 'products SET name=?,category_id=?,slug=?,short_desc=?,description=?,specs=?,sort_order=?,is_featured=?,is_active=?,image=? WHERE id=?')
            ->execute([$name,$cat,$slug,$short,$desc,$specs,$sort,$featured,$active,$image,$id]);
        flash('success','Ürün güncellendi.');
    } else {
        // Ensure unique slug
        $base = $slug; $i = 1;
        while ($pdo->prepare('SELECT id FROM ' . p() . 'products WHERE slug=?')->execute([$slug]) && $pdo->query('SELECT FOUND_ROWS()')->fetchColumn()) {
            $slug = $base . '-' . $i++;
        }
        $pdo->prepare('INSERT INTO ' . p() . 'products (name,category_id,slug,short_desc,description,specs,sort_order,is_featured,is_active,image) VALUES (?,?,?,?,?,?,?,?,?,?)')
            ->execute([$name,$cat,$slug,$short,$desc,$specs,$sort,$featured,$active,$image]);
        flash('success','Ürün eklendi.');
    }
    header('Location: /admin/ürünler.php'); exit;
}
$cats = $pdo->query('SELECT * FROM ' . p() . 'categories WHERE is_active=1 ORDER BY sort_order')->fetchAll();
$products = $pdo->query('SELECT pr.*,c.name as cat_name FROM ' . p() . 'products pr LEFT JOIN ' . p() . 'categories c ON c.id=pr.category_id ORDER BY pr.sort_order,pr.id')->fetchAll();
$editing = null;
if (($action === 'edit') && $id) { $s = $pdo->prepare('SELECT * FROM ' . p() . 'products WHERE id=?'); $s->execute([$id]); $editing = $s->fetch(); }
?>
<!DOCTYPE html>
<html lang="tr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ürünler - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>">
</head>
<body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
<?php if (!empty($flash)): ?><div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="page-actions">
  <h1><?= $action==='edit'?'Ürün Duzenle':'Yeni Ürün Ekle' ?></h1>
  <a href="/admin/ürünler.php" class="btn btn-secondary">← Geri</a>
</div>
<div class="card"><div class="card-body">
<form method="POST" enctype="multipart/form-data">
  <div class="form-grid">
    <div class="form-group">
      <label class="form-label">Ürün Adi *</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" required oninput="if(!document.getElementById('slug').value.length||!<?= $id ?>)document.getElementById('slug').value=this.value.toLowerCase()">
    </div>
    <div class="form-group">
      <label class="form-label">Kategori</label>
      <select name="category_id" class="form-control">
        <option value="0">-- Kategori Sec --</option>
        <?php foreach ($cats as $c): ?>
        <option value="<?= $c['id'] ?>" <?= ($editing['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Slug (URL)</label>
      <input type="text" name="slug" id="slug" class="form-control" value="<?= htmlspecialchars($editing['slug'] ?? '') ?>" placeholder="otomatik-oluşturulur">
    </div>
    <div class="form-group">
      <label class="form-label">Sira</label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int)($editing['sort_order'] ?? 0) ?>" min="0">
    </div>
    <div class="form-group full">
      <label class="form-label">Kisa Aciklama</label>
      <textarea name="short_desc" class="form-control" rows="2"><?= htmlspecialchars($editing['short_desc'] ?? '') ?></textarea>
    </div>
    <div class="form-group full">
      <label class="form-label">Detayli Aciklama</label>
      <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
    </div>
    <div class="form-group full">
      <label class="form-label">Teknik Özellikler</label>
      <textarea name="specs" class="form-control" rows="4" placeholder="Malzeme: Alüminyum&#10;Kalinlik: 2mm&#10;Uzunluk: 6000mm"><?= htmlspecialchars($editing['specs'] ?? '') ?></textarea>
      <span class="form-hint">Her satira bir ozellik: "Anahtar: Deger" formatinda yazin.</span>
    </div>
    <div class="form-group full">
      <label class="form-label">Görsel</label>
      <?php if (!empty($editing['image'])): ?>
      <img src="<?= htmlspecialchars($editing['image']) ?>" id="imgPreview" style="max-height:150px;border-radius:8px;margin-bottom:8px">
      <?php else: ?>
      <img id="imgPreview" src="" style="max-height:150px;border-radius:8px;display:none;margin-bottom:8px">
      <?php endif; ?>
      <div class="upload-area" onclick="document.getElementById('imgFile').click()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="1.5" style="margin:0 auto 8px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <p style="font-size:12px;color:#aaa">Tiklayin veya surukleme birakma</p>
      </div>
      <input type="file" id="imgFile" name="image" accept="image/*" data-preview="imgPreview" style="display:none">
    </div>
    <div class="form-group">
      <div class="form-check" style="margin-top:28px">
        <input type="checkbox" name="is_featured" id="is_featured" <?= !empty($editing['is_featured']) ? 'checked':'' ?>>
        <label for="is_featured">Öne Çıkan Ürün</label>
      </div>
    </div>
    <div class="form-group">
      <div class="form-check" style="margin-top:28px">
        <input type="checkbox" name="is_active" id="is_active" <?= !isset($editing) || !empty($editing['is_active']) ? 'checked':'' ?>>
        <label for="is_active">Aktif</label>
      </div>
    </div>
  </div>
  <div style="margin-top:24px;display:flex;gap:10px">
    <button type="submit" class="btn btn-primary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      <?= $action==='edit'?'Güncelle':'Ekle' ?>
    </button>
    <a href="/admin/ürünler.php" class="btn btn-secondary">Iptal</a>
  </div>
</form>
</div></div>

<?php else: ?>
<div class="page-actions">
  <h1>Ürünler <span style="font-size:14px;font-weight:400;color:#888">(<?= count($products) ?>)</span></h1>
  <a href="/admin/ürünler.php?action=add" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Yeni Ürün
  </a>
</div>
<div class="card"><div class="table-wrap">
  <table class="admin-table">
    <thead><tr><th>Görsel</th><th>Ürün</th><th>Kategori</th><th>Durum</th><th style="text-align:right">Islemler</th></tr></thead>
    <tbody>
      <?php foreach ($products as $pr): ?>
      <tr>
        <td><?php if ($pr['image']): ?><img class="table-img" src="<?= htmlspecialchars($pr['image']) ?>"><?php else: ?><div class="table-no-img">📦</div><?php endif; ?></td>
        <td>
          <strong><?= htmlspecialchars($pr['name']) ?></strong>
          <br><span style="font-size:11px;color:#aaa">/<?= htmlspecialchars($pr['slug']) ?></span>
        </td>
        <td><?= htmlspecialchars($pr['cat_name'] ?? '-') ?></td>
        <td>
          <a href="/admin/ürünler.php?action=toggle&id=<?= $pr['id'] ?>">
            <span class="badge badge-<?= $pr['is_active'] ? 'active' : 'passive' ?>"><?= $pr['is_active'] ? '● Aktif' : '○ Pasif' ?></span>
          </a>
          <?php if ($pr['is_featured']): ?><span class="badge badge-read" style="margin-left:4px">★ Öne Çıkan</span><?php endif; ?>
        </td>
        <td style="text-align:right">
          <div style="display:flex;gap:6px;justify-content:flex-end">
            <a href="/?page=urun&slug=<?= htmlspecialchars($pr['slug']) ?>" target="_blank" class="btn btn-sm btn-secondary">Gor</a>
            <a href="/admin/ürünler.php?action=edit&id=<?= $pr['id'] ?>" class="btn btn-sm btn-secondary">Duzenle</a>
            <a href="/admin/ürünler.php?action=delete&id=<?= $pr['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Ürün silinecek, emin misiniz?">Sil</a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($products)): ?>
      <tr><td colspan="5" style="text-align:center;color:#999;padding:40px">Henuz ürün eklenmemis.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div></div>
<?php endif; ?>
</main>
<?php require ROOT . '/admin/includes/footer.php'; ?>

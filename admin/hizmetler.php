<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Hizmetler';
$pdo = getDB(); $flash = getFlash();
$action = $_GET['action'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
$icons = ['settings','tool','layers','headphones','star','shield','award','truck'];
if ($action === 'delete' && $id) { $pdo->prepare('DELETE FROM ' . p() . 'services WHERE id=?')->execute([$id]); flash('success','Silindi.'); header('Location: /admin/hizmetler.php'); exit; }
if ($action === 'toggle' && $id) { $pdo->prepare('UPDATE ' . p() . 'services SET is_active=1-is_active WHERE id=?')->execute([$id]); header('Location: /admin/hizmetler.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
    $short = trim($_POST['short_desc'] ?? ''); $desc = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'settings'); $sort = (int)($_POST['sort_order'] ?? 0); $active = isset($_POST['is_active']) ? 1 : 0;
    $image = '';
    if (!empty($_FILES['image']['name'])) { $up = uploadImage($_FILES['image'], 'services'); if ($up) $image = $up; }
    if ($id) {
        $existing = $pdo->prepare('SELECT image FROM ' . p() . 'services WHERE id=?'); $existing->execute([$id]);
        if (!$image) $image = $existing->fetchColumn();
        $pdo->prepare('UPDATE ' . p() . 'services SET name=?,slug=?,short_desc=?,description=?,icon=?,sort_order=?,is_active=?,image=? WHERE id=?')
            ->execute([$name,$slug,$short,$desc,$icon,$sort,$active,$image,$id]);
        flash('success','Güncellendi.');
    } else {
        $pdo->prepare('INSERT INTO ' . p() . 'services (name,slug,short_desc,description,icon,sort_order,is_active,image) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$name,$slug,$short,$desc,$icon,$sort,$active,$image]);
        flash('success','Eklendi.');
    }
    header('Location: /admin/hizmetler.php'); exit;
}
$services = $pdo->query('SELECT * FROM ' . p() . 'services ORDER BY sort_order')->fetchAll();
$editing = null;
if (($action === 'edit') && $id) { $s = $pdo->prepare('SELECT * FROM ' . p() . 'services WHERE id=?'); $s->execute([$id]); $editing = $s->fetch(); }
?>
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Hizmetler - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>"></head><body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
<?php if (!empty($flash)): ?><div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="page-actions"><h1><?= $action==='edit'?'Hizmet Duzenle':'Yeni Hizmet' ?></h1><a href="/admin/hizmetler.php" class="btn btn-secondary">← Geri</a></div>
<div class="card"><div class="card-body">
  <form method="POST" enctype="multipart/form-data">
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Hizmet Adi *</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" required></div>
      <div class="form-group"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($editing['slug'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Ikon</label>
        <select name="icon" class="form-control">
          <?php foreach ($icons as $ic): ?><option value="<?= $ic ?>" <?= ($editing['icon'] ?? 'settings')===$ic?'selected':'' ?>><?= $ic ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Sira</label><input type="number" name="sort_order" class="form-control" value="<?= (int)($editing['sort_order'] ?? 0) ?>"></div>
      <div class="form-group full"><label class="form-label">Kisa Aciklama</label><textarea name="short_desc" class="form-control" rows="2"><?= htmlspecialchars($editing['short_desc'] ?? '') ?></textarea></div>
      <div class="form-group full"><label class="form-label">Detayli Aciklama</label><textarea name="description" class="form-control" rows="6"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea></div>
      <div class="form-group full"><label class="form-label">Görsel</label>
        <?php if (!empty($editing['image'])): ?><img src="<?= htmlspecialchars($editing['image']) ?>" id="imgPrev" style="max-height:120px;border-radius:8px;margin-bottom:8px"><?php else: ?><img id="imgPrev" src="" style="max-height:120px;border-radius:8px;display:none;margin-bottom:8px"><?php endif; ?>
        <div class="upload-area" onclick="document.getElementById('imgF').click()"><p style="font-size:12px;color:#aaa">Görsel yukle</p></div>
        <input type="file" id="imgF" name="image" accept="image/*" data-preview="imgPrev" style="display:none">
      </div>
      <div class="form-group"><div class="form-check" style="margin-top:28px"><input type="checkbox" name="is_active" id="ia" <?= !isset($editing)||!empty($editing['is_active'])?'checked':'' ?>><label for="ia">Aktif</label></div></div>
    </div>
    <div style="margin-top:20px;display:flex;gap:10px"><button type="submit" class="btn btn-primary"><?= $action==='edit'?'Güncelle':'Ekle' ?></button><a href="/admin/hizmetler.php" class="btn btn-secondary">Iptal</a></div>
  </form>
</div></div>
<?php else: ?>
<div class="page-actions"><h1>Hizmetler</h1><a href="/admin/hizmetler.php?action=add" class="btn btn-primary">+ Yeni</a></div>
<div class="card"><div class="table-wrap">
  <table class="admin-table">
    <thead><tr><th>Hizmet</th><th>Ikon</th><th>Durum</th><th style="text-align:right">Islemler</th></tr></thead>
    <tbody>
      <?php foreach ($services as $s): ?>
      <tr>
        <td><strong><?= htmlspecialchars($s['name']) ?></strong><br><span style="font-size:12px;color:#888"><?= htmlspecialchars(substr($s['short_desc']??'',0,60)) ?></span></td>
        <td><code style="font-size:12px"><?= htmlspecialchars($s['icon']) ?></code></td>
        <td><a href="/admin/hizmetler.php?action=toggle&id=<?= $s['id'] ?>"><span class="badge badge-<?= $s['is_active']?'active':'passive' ?>"><?= $s['is_active']?'Aktif':'Pasif' ?></span></a></td>
        <td style="text-align:right"><div style="display:flex;gap:6px;justify-content:flex-end">
          <a href="/admin/hizmetler.php?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Duzenle</a>
          <a href="/admin/hizmetler.php?action=delete&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Sil?">Sil</a>
        </div></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
<?php endif; ?>
</main><?php require ROOT . '/admin/includes/footer.php'; ?>

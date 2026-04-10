<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Kategoriler';
$pdo = getDB(); $flash = getFlash();
$action = $_GET['action'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
if ($action === 'delete' && $id) { $pdo->prepare('DELETE FROM ' . p() . 'categories WHERE id=?')->execute([$id]); flash('success','Kategori silindi.'); header('Location: /admin/kategoriler.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $slug = trim($_POST['slug'] ?? '') ?: slugify($name); $sort = (int)($_POST['sort_order'] ?? 0); $active = isset($_POST['is_active']) ? 1 : 0;
    if ($id) {
        $pdo->prepare('UPDATE ' . p() . 'categories SET name=?,slug=?,sort_order=?,is_active=? WHERE id=?')->execute([$name,$slug,$sort,$active,$id]);
        flash('success','Güncellendi.');
    } else {
        $pdo->prepare('INSERT INTO ' . p() . 'categories (name,slug,sort_order,is_active) VALUES (?,?,?,?)')->execute([$name,$slug,$sort,$active]);
        flash('success','Eklendi.');
    }
    header('Location: /admin/kategoriler.php'); exit;
}
$cats = $pdo->query('SELECT c.*,(SELECT COUNT(*) FROM ' . p() . 'products WHERE category_id=c.id) as pcount FROM ' . p() . 'categories c ORDER BY sort_order')->fetchAll();
$editing = null;
if (($action === 'edit') && $id) { $s = $pdo->prepare('SELECT * FROM ' . p() . 'categories WHERE id=?'); $s->execute([$id]); $editing = $s->fetch(); }
?>
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kategoriler - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>"></head><body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
<?php if (!empty($flash)): ?><div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
<div style="display:grid;grid-template-columns:1fr 400px;gap:24px;align-items:start">
  <!-- LIST -->
  <div>
    <div class="page-actions" style="margin-bottom:16px"><h1>Kategoriler</h1></div>
    <div class="card"><div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Kategori</th><th>Slug</th><th>Ürün</th><th>Durum</th><th style="text-align:right">Islemler</th></tr></thead>
        <tbody>
          <?php foreach ($cats as $c): ?>
          <tr>
            <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
            <td style="font-size:12px;color:#aaa"><?= htmlspecialchars($c['slug']) ?></td>
            <td><?= (int)$c['pcount'] ?></td>
            <td><span class="badge badge-<?= $c['is_active'] ? 'active':'passive' ?>"><?= $c['is_active'] ? 'Aktif':'Pasif' ?></span></td>
            <td style="text-align:right">
              <div style="display:flex;gap:6px;justify-content:flex-end">
                <a href="/admin/kategoriler.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">Duzenle</a>
                <a href="/admin/kategoriler.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Sil?">Sil</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div></div>
  </div>
  <!-- FORM -->
  <div>
    <div class="page-actions" style="margin-bottom:16px"><h1><?= $action==='edit'?'Duzenle':'Yeni Kategori' ?></h1></div>
    <div class="card"><div class="card-body">
      <form method="POST">
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Kategori Adi *</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" required oninput="if(!<?= $id ?>)document.getElementById('catSlug').value=this.value.toLowerCase().replace(/\s+/g,'-')">
        </div>
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" id="catSlug" class="form-control" value="<?= htmlspecialchars($editing['slug'] ?? '') ?>">
        </div>
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Sira</label>
          <input type="number" name="sort_order" class="form-control" value="<?= (int)($editing['sort_order'] ?? 0) ?>">
        </div>
        <div class="form-check" style="margin-bottom:20px">
          <input type="checkbox" name="is_active" id="isActive" <?= !isset($editing) || !empty($editing['is_active']) ? 'checked':'' ?>>
          <label for="isActive">Aktif</label>
        </div>
        <div style="display:flex;gap:8px">
          <button type="submit" class="btn btn-primary"><?= $action==='edit'?'Güncelle':'Ekle' ?></button>
          <?php if ($action==='edit'): ?><a href="/admin/kategoriler.php" class="btn btn-secondary">Iptal</a><?php endif; ?>
        </div>
      </form>
    </div></div>
  </div>
</div>
</main>
<?php require ROOT . '/admin/includes/footer.php'; ?>

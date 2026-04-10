<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Referanslar';
$pdo = getDB(); $flash = getFlash();
$action = $_GET['action'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
if ($action === 'delete' && $id) { $pdo->prepare('DELETE FROM ' . p() . 'references WHERE id=?')->execute([$id]); flash('success','Silindi.'); header('Location: /admin/referanslar.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $url = trim($_POST['url'] ?? ''); $sort = (int)($_POST['sort_order'] ?? 0); $active = isset($_POST['is_active']) ? 1 : 0;
    $logo = '';
    if (!empty($_FILES['logo']['name'])) { $up = uploadImage($_FILES['logo'], 'references'); if ($up) $logo = $up; }
    if ($id) {
        $existing = $pdo->prepare('SELECT logo FROM ' . p() . 'references WHERE id=?'); $existing->execute([$id]); if (!$logo) $logo = $existing->fetchColumn();
        $pdo->prepare('UPDATE ' . p() . 'references SET name=?,url=?,sort_order=?,is_active=?,logo=? WHERE id=?')->execute([$name,$url,$sort,$active,$logo,$id]);
        flash('success','Guncellendi.');
    } else {
        $pdo->prepare('INSERT INTO ' . p() . 'references (name,url,sort_order,is_active,logo) VALUES (?,?,?,?,?)')->execute([$name,$url,$sort,$active,$logo]);
        flash('success','Eklendi.');
    }
    header('Location: /admin/referanslar.php'); exit;
}
$refs = $pdo->query('SELECT * FROM ' . p() . 'references ORDER BY sort_order')->fetchAll();
$editing = null;
if (($action === 'edit') && $id) { $s = $pdo->prepare('SELECT * FROM ' . p() . 'references WHERE id=?'); $s->execute([$id]); $editing = $s->fetch(); }
?>
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Referanslar - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>"></head><body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
<?php if (!empty($flash)): ?><div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start">
  <div>
    <div class="page-actions" style="margin-bottom:16px"><h1>Referanslar</h1></div>
    <div class="card"><div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Logo</th><th>Firma</th><th>Sira</th><th>Durum</th><th style="text-align:right">Islemler</th></tr></thead>
        <tbody>
          <?php foreach ($refs as $r): ?>
          <tr>
            <td><?php if ($r['logo']): ?><img src="<?= htmlspecialchars($r['logo']) ?>" style="height:32px;width:auto;filter:grayscale(1)"><?php else: ?><span style="font-size:18px">🏢</span><?php endif; ?></td>
            <td><?= htmlspecialchars($r['name']) ?><?php if ($r['url']): ?><br><a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" style="font-size:11px;color:#aaa"><?= htmlspecialchars($r['url']) ?></a><?php endif; ?></td>
            <td><?= (int)$r['sort_order'] ?></td>
            <td><span class="badge badge-<?= $r['is_active']?'active':'passive' ?>"><?= $r['is_active']?'Aktif':'Pasif' ?></span></td>
            <td style="text-align:right"><div style="display:flex;gap:6px;justify-content:flex-end">
              <a href="/admin/referanslar.php?action=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">Duzenle</a>
              <a href="/admin/referanslar.php?action=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Sil?">Sil</a>
            </div></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div></div>
  </div>
  <div>
    <div class="page-actions" style="margin-bottom:16px"><h1><?= $action==='edit'?'Duzenle':'Yeni Referans' ?></h1></div>
    <div class="card"><div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <div class="form-group" style="margin-bottom:14px"><label class="form-label">Firma Adi *</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" required></div>
        <div class="form-group" style="margin-bottom:14px"><label class="form-label">Web Sitesi</label><input type="url" name="url" class="form-control" value="<?= htmlspecialchars($editing['url'] ?? '') ?>" placeholder="https://..."></div>
        <div class="form-group" style="margin-bottom:14px"><label class="form-label">Sira</label><input type="number" name="sort_order" class="form-control" value="<?= (int)($editing['sort_order'] ?? 0) ?>"></div>
        <div class="form-group" style="margin-bottom:14px"><label class="form-label">Logo</label>
          <?php if (!empty($editing['logo'])): ?><img src="<?= htmlspecialchars($editing['logo']) ?>" id="logoP" style="max-height:50px;border-radius:4px;margin-bottom:8px"><?php else: ?><img id="logoP" src="" style="max-height:50px;border-radius:4px;display:none;margin-bottom:8px"><?php endif; ?>
          <div class="upload-area" onclick="document.getElementById('lF').click()"><p style="font-size:12px;color:#aaa">Logo yukle (PNG/SVG)</p></div>
          <input type="file" id="lF" name="logo" accept="image/*" data-preview="logoP" style="display:none">
        </div>
        <div class="form-check" style="margin-bottom:16px"><input type="checkbox" name="is_active" id="ia" <?= !isset($editing)||!empty($editing['is_active'])?'checked':'' ?>><label for="ia">Aktif</label></div>
        <div style="display:flex;gap:8px">
          <button type="submit" class="btn btn-primary"><?= $action==='edit'?'Guncelle':'Ekle' ?></button>
          <?php if ($action==='edit'): ?><a href="/admin/referanslar.php" class="btn btn-secondary">Iptal</a><?php endif; ?>
        </div>
      </form>
    </div></div>
  </div>
</div>
</main><?php require ROOT . '/admin/includes/footer.php'; ?>

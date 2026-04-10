<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Istatistikler';
$pdo = getDB(); $flash = getFlash();
$action = $_GET['action'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
if ($action === 'delete' && $id) { $pdo->prepare('DELETE FROM ' . p() . 'stats WHERE id=?')->execute([$id]); flash('success','Silindi.'); header('Location: /admin/istatistikler.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $label = trim($_POST['label'] ?? ''); $value = trim($_POST['value'] ?? ''); $icon = trim($_POST['icon'] ?? 'star'); $sort = (int)($_POST['sort_order'] ?? 0);
    if ($id) { $pdo->prepare('UPDATE ' . p() . 'stats SET label=?,value=?,icon=?,sort_order=? WHERE id=?')->execute([$label,$value,$icon,$sort,$id]); flash('success','Guncellendi.'); }
    else { $pdo->prepare('INSERT INTO ' . p() . 'stats (label,value,icon,sort_order) VALUES (?,?,?,?)')->execute([$label,$value,$icon,$sort]); flash('success','Eklendi.'); }
    header('Location: /admin/istatistikler.php'); exit;
}
$stats = $pdo->query('SELECT * FROM ' . p() . 'stats ORDER BY sort_order')->fetchAll();
$editing = null;
if (($action === 'edit') && $id) { $s = $pdo->prepare('SELECT * FROM ' . p() . 'stats WHERE id=?'); $s->execute([$id]); $editing = $s->fetch(); }
?>
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Istatistikler - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>"></head><body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
<?php if (!empty($flash)): ?><div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
<p style="font-size:13px;color:#888;margin-bottom:16px">Stats bar ana sayfada ve hakkimizda sayfasinda gozukur (kirmizi bolum). Sayac animasyonu otomatik calisir.</p>
<div style="display:grid;grid-template-columns:1fr 360px;gap:24px">
  <div>
    <div class="card"><div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Deger</th><th>Etiket</th><th>Sira</th><th style="text-align:right">Islemler</th></tr></thead>
        <tbody>
          <?php foreach ($stats as $st): ?>
          <tr>
            <td><strong style="font-size:20px;color:#c0392b"><?= htmlspecialchars($st['value']) ?></strong></td>
            <td><?= htmlspecialchars($st['label']) ?></td>
            <td><?= (int)$st['sort_order'] ?></td>
            <td style="text-align:right"><div style="display:flex;gap:6px;justify-content:flex-end">
              <a href="/admin/istatistikler.php?action=edit&id=<?= $st['id'] ?>" class="btn btn-sm btn-secondary">Duzenle</a>
              <a href="/admin/istatistikler.php?action=delete&id=<?= $st['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Sil?">Sil</a>
            </div></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div></div>
  </div>
  <div>
    <div class="card"><div class="card-header"><div class="card-title"><?= $action==='edit'?'Duzenle':'Yeni Istatistik' ?></div></div>
      <div class="card-body">
        <form method="POST">
          <div class="form-group" style="margin-bottom:14px"><label class="form-label">Deger *</label><input type="text" name="value" class="form-control" value="<?= htmlspecialchars($editing['value'] ?? '') ?>" required placeholder="25+, 1500, %98 vb."></div>
          <div class="form-group" style="margin-bottom:14px"><label class="form-label">Etiket *</label><input type="text" name="label" class="form-control" value="<?= htmlspecialchars($editing['label'] ?? '') ?>" required placeholder="Yillik Deneyim"></div>
          <div class="form-group" style="margin-bottom:14px"><label class="form-label">Sira</label><input type="number" name="sort_order" class="form-control" value="<?= (int)($editing['sort_order'] ?? 0) ?>"></div>
          <div class="form-group" style="margin-bottom:16px"><label class="form-label">Ikon (lutfen biri: calendar,users,check-circle,map-pin,star,trophy)</label><input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($editing['icon'] ?? 'star') ?>"></div>
          <div style="display:flex;gap:8px">
            <button type="submit" class="btn btn-primary"><?= $action==='edit'?'Guncelle':'Ekle' ?></button>
            <?php if ($action==='edit'): ?><a href="/admin/istatistikler.php" class="btn btn-secondary">Iptal</a><?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</main><?php require ROOT . '/admin/includes/footer.php'; ?>

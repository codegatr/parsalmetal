<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Kullanicilar';
$pdo = getDB(); $flash = getFlash(); $currentAdmin = currentAdmin();
$action = $_GET['action'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
if ($action === 'delete' && $id && $id !== (int)$currentAdmin['id']) { $pdo->prepare('DELETE FROM ' . p() . 'users WHERE id=?')->execute([$id]); flash('success','Silindi.'); header('Location: /admin/kullanicilar.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? ''); $fullname = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? ''); $password = trim($_POST['password'] ?? ''); $role = trim($_POST['role'] ?? 'admin');
    if ($id) {
        if ($password) { $hash = password_hash($password, PASSWORD_BCRYPT); $pdo->prepare('UPDATE ' . p() . 'users SET username=?,full_name=?,email=?,password=?,role=? WHERE id=?')->execute([$username,$fullname,$email,$hash,$role,$id]); }
        else { $pdo->prepare('UPDATE ' . p() . 'users SET username=?,full_name=?,email=?,role=? WHERE id=?')->execute([$username,$fullname,$email,$role,$id]); }
        flash('success','Güncellendi.');
    } else {
        if (!$password) { flash('error','Sifre gerekli.'); header('Location: /admin/kullanicilar.php?action=add'); exit; }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare('INSERT INTO ' . p() . 'users (username,full_name,email,password,role) VALUES (?,?,?,?,?)')->execute([$username,$fullname,$email,$hash,$role]);
        flash('success','Eklendi.');
    }
    header('Location: /admin/kullanicilar.php'); exit;
}
$users = $pdo->query('SELECT * FROM ' . p() . 'users ORDER BY id')->fetchAll();
$editing = null;
if (($action === 'edit') && $id) { $s = $pdo->prepare('SELECT * FROM ' . p() . 'users WHERE id=?'); $s->execute([$id]); $editing = $s->fetch(); }
?>
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kullanicilar - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>"></head><body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
<?php if (!empty($flash)): ?><div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start">
  <div>
    <div class="page-actions" style="margin-bottom:16px"><h1>Admin Kullanicilari</h1></div>
    <div class="card"><div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Kullanici</th><th>E-posta</th><th>Rol</th><th>Son Giriş</th><th style="text-align:right">Islemler</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><div style="display:flex;align-items:center;gap:10px">
              <div style="width:34px;height:34px;border-radius:50%;background:#c0392b;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff"><?= strtoupper(substr($u['full_name']?:$u['username'],0,1)) ?></div>
              <div><strong><?= htmlspecialchars($u['full_name'] ?: $u['username']) ?></strong><br><span style="font-size:11px;color:#aaa"><?= htmlspecialchars($u['username']) ?></span></div>
            </div></td>
            <td style="font-size:13px"><?= htmlspecialchars($u['email'] ?? '-') ?></td>
            <td><span class="badge badge-<?= $u['role']==='super'?'active':'new' ?>"><?= htmlspecialchars($u['role']) ?></span></td>
            <td style="font-size:12px;color:#888"><?= $u['last_login'] ? date('d.m.Y H:i',strtotime($u['last_login'])) : '-' ?></td>
            <td style="text-align:right"><div style="display:flex;gap:6px;justify-content:flex-end">
              <a href="/admin/kullanicilar.php?action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-secondary">Duzenle</a>
              <?php if ($u['id'] !== (int)$currentAdmin['id']): ?>
              <a href="/admin/kullanicilar.php?action=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Kullanici silinecek?">Sil</a>
              <?php endif; ?>
            </div></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div></div>
  </div>
  <div>
    <div class="page-actions" style="margin-bottom:16px"><h1><?= $action==='edit'?'Kullaniciyi Duzenle':'Yeni Kullanici' ?></h1></div>
    <div class="card"><div class="card-body">
      <form method="POST">
        <div class="form-group" style="margin-bottom:14px"><label class="form-label">Ad Soyad</label><input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($editing['full_name'] ?? '') ?>"></div>
        <div class="form-group" style="margin-bottom:14px"><label class="form-label">Kullanici Adi *</label><input type="text" name="username" class="form-control" value="<?= htmlspecialchars($editing['username'] ?? '') ?>" required></div>
        <div class="form-group" style="margin-bottom:14px"><label class="form-label">E-posta</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editing['email'] ?? '') ?>"></div>
        <div class="form-group" style="margin-bottom:14px"><label class="form-label">Sifre <?= $action==='edit'?'(bos birakilanlar degismez)':'*' ?></label><input type="password" name="password" class="form-control" <?= $action!=='edit'?'required':'' ?>></div>
        <div class="form-group" style="margin-bottom:16px"><label class="form-label">Rol</label>
          <select name="role" class="form-control">
            <option value="admin" <?= ($editing['role'] ?? 'admin')==='admin'?'selected':'' ?>>Admin</option>
            <option value="editor" <?= ($editing['role'] ?? '')==='editor'?'selected':'' ?>>Editor</option>
            <option value="super" <?= ($editing['role'] ?? '')==='super'?'selected':'' ?>>Super Admin</option>
          </select>
        </div>
        <div style="display:flex;gap:8px">
          <button type="submit" class="btn btn-primary"><?= $action==='edit'?'Güncelle':'Kullanici Ekle' ?></button>
          <?php if ($action==='edit'): ?><a href="/admin/kullanicilar.php" class="btn btn-secondary">Iptal</a><?php endif; ?>
        </div>
      </form>
    </div></div>
  </div>
</div>
</main><?php require ROOT . '/admin/includes/footer.php'; ?>

<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Iletisim Mesajlari';
$pdo = getDB(); $flash = getFlash();
$action = $_GET['action'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
if ($action === 'delete' && $id) { $pdo->prepare('DELETE FROM ' . p() . 'contacts WHERE id=?')->execute([$id]); flash('success','Silindi.'); header('Location: /admin/mesajlar.php'); exit; }
$msgs = $pdo->query('SELECT * FROM ' . p() . 'contacts ORDER BY created_at DESC')->fetchAll();
$detail = null;
if ($action === 'view' && $id) {
    $st = $pdo->prepare('SELECT * FROM ' . p() . 'contacts WHERE id=?'); $st->execute([$id]); $detail = $st->fetch();
    if ($detail) $pdo->prepare('UPDATE ' . p() . 'contacts SET is_read=1 WHERE id=?')->execute([$id]);
}
?>
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mesajlar - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>"></head><body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
<?php if (!empty($flash)): ?><div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
<?php if ($detail): ?>
<div class="page-actions"><h1>Mesaj #<?= $detail['id'] ?></h1><a href="/admin/mesajlar.php" class="btn btn-secondary">← Geri</a></div>
<div class="card"><div class="card-body">
  <table style="width:100%;margin-bottom:20px">
    <?php foreach ([['Ad','name'],['E-posta','email'],['Telefon','phone'],['Konu','subject']] as [$l,$f]): ?>
    <tr><td style="padding:8px 0;color:#888;font-size:13px;width:120px"><?= $l ?></td><td style="padding:8px 0;font-size:14px;font-weight:600"><?= htmlspecialchars($detail[$f] ?? '-') ?></td></tr>
    <?php endforeach; ?>
    <tr><td style="padding:8px 0;color:#888;font-size:13px">Tarih</td><td style="padding:8px 0;font-size:14px"><?= date('d.m.Y H:i',strtotime($detail['created_at'])) ?></td></tr>
  </table>
  <div style="padding:16px;background:#f8f8f8;border-radius:8px;margin-bottom:16px">
    <p style="font-size:14px;line-height:1.7"><?= nl2br(htmlspecialchars($detail['message'])) ?></p>
  </div>
  <a href="mailto:<?= htmlspecialchars($detail['email']) ?>" class="btn btn-primary">E-posta ile Yanitle</a>
</div></div>
<?php else: ?>
<div class="page-actions"><h1>Iletisim Mesajlari <span style="font-size:14px;font-weight:400;color:#888">(<?= count($msgs) ?>)</span></h1></div>
<div class="card"><div class="table-wrap">
  <table class="admin-table">
    <thead><tr><th>Ad</th><th>E-posta</th><th>Konu</th><th>Tarih</th><th>Durum</th><th style="text-align:right">Islemler</th></tr></thead>
    <tbody>
      <?php foreach ($msgs as $m): ?>
      <tr style="<?= !$m['is_read']?'font-weight:600':'' ?>">
        <td><?= htmlspecialchars($m['name']) ?></td>
        <td style="font-size:13px"><?= htmlspecialchars($m['email']) ?></td>
        <td><?= htmlspecialchars(substr($m['subject'] ?? '-',0,40)) ?></td>
        <td style="font-size:12px;color:#888;white-space:nowrap"><?= date('d.m.Y H:i',strtotime($m['created_at'])) ?></td>
        <td><span class="badge badge-<?= $m['is_read']?'passive':'new' ?>"><?= $m['is_read']?'Okundu':'Yeni' ?></span></td>
        <td style="text-align:right">
          <div style="display:flex;gap:6px;justify-content:flex-end">
            <a href="/admin/mesajlar.php?action=view&id=<?= $m['id'] ?>" class="btn btn-sm btn-secondary">Gor</a>
            <a href="/admin/mesajlar.php?action=delete&id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Sil?">Sil</a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($msgs)): ?><tr><td colspan="6" style="text-align:center;color:#999;padding:40px">Henuz mesaj yok.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
<?php endif; ?>
</main>
<?php require ROOT . '/admin/includes/footer.php'; ?>

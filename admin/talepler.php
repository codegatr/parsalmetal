<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Teklif Talepleri';
$pdo = getDB(); $flash = getFlash();
$action = $_GET['action'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);

if ($action === 'status' && $id) {
    $status = $_GET['s'] ?? 'read';
    if (in_array($status, ['new','read','replied','closed'])) {
        $pdo->prepare('UPDATE ' . p() . 'quotes SET status=? WHERE id=?')->execute([$status,$id]);
    }
    header('Location: /admin/talepler.php'); exit;
}
if ($action === 'delete' && $id) {
    $pdo->prepare('DELETE FROM ' . p() . 'quotes WHERE id=?')->execute([$id]);
    flash('success','Silindi.'); header('Location: /admin/talepler.php'); exit;
}
$quotes = $pdo->query('SELECT * FROM ' . p() . 'quotes ORDER BY created_at DESC')->fetchAll();
$detail = null;
if ($action === 'view' && $id) {
    $st = $pdo->prepare('SELECT * FROM ' . p() . 'quotes WHERE id=?'); $st->execute([$id]); $detail = $st->fetch();
    if ($detail && $detail['status'] === 'new') $pdo->prepare('UPDATE ' . p() . 'quotes SET status=\'read\' WHERE id=?')->execute([$id]);
}
$statusLabels = ['new'=>'Yeni','read'=>'Okundu','replied'=>'Yanitlandi','closed'=>'Kapali'];
?>
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Talepler - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>"></head><body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
<?php if (!empty($flash)): ?><div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>

<?php if ($detail): ?>
<div class="page-actions"><h1>Talep Detayi #<?= $detail['id'] ?></h1><a href="/admin/talepler.php" class="btn btn-secondary">← Geri</a></div>
<div style="display:grid;grid-template-columns:1fr 300px;gap:24px">
  <div class="card"><div class="card-body">
    <table style="width:100%;border-collapse:collapse">
      <?php foreach ([['Ad Soyad','name'],['Firma','company'],['E-posta','email'],['Telefon','phone'],['Urun','product']] as [$lbl,$fld]): ?>
      <tr><td style="padding:10px 0;color:#888;font-size:13px;width:140px;border-bottom:1px solid #f0f0f0"><?= $lbl ?></td><td style="padding:10px 0;font-size:14px;font-weight:600;border-bottom:1px solid #f0f0f0"><?= htmlspecialchars($detail[$fld] ?? '-') ?></td></tr>
      <?php endforeach; ?>
      <tr><td style="padding:10px 0;color:#888;font-size:13px">Tarih</td><td style="padding:10px 0;font-size:14px"><?= date('d.m.Y H:i', strtotime($detail['created_at'])) ?></td></tr>
    </table>
    <div style="margin-top:20px;padding:16px;background:#f8f8f8;border-radius:8px">
      <p style="font-size:13px;color:#888;margin-bottom:8px">Mesaj:</p>
      <p style="font-size:14px;line-height:1.7"><?= nl2br(htmlspecialchars($detail['message'])) ?></p>
    </div>
  </div></div>
  <div class="card"><div class="card-body">
    <h3 style="font-size:14px;font-weight:700;margin-bottom:16px">Durum Degistir</h3>
    <?php foreach ($statusLabels as $s => $l): ?>
    <a href="/admin/talepler.php?action=status&id=<?= $detail['id'] ?>&s=<?= $s ?>" style="display:block;padding:10px 14px;border-radius:8px;margin-bottom:6px;font-size:13px;font-weight:600;background:<?= $detail['status']===$s?'#c0392b':'#f0f2f5' ?>;color:<?= $detail['status']===$s?'#fff':'#333' ?>"><?= $l ?></a>
    <?php endforeach; ?>
    <hr style="margin:16px 0;border:none;border-top:1px solid #eee">
    <a href="mailto:<?= htmlspecialchars($detail['email']) ?>" class="btn btn-primary" style="width:100%;justify-content:center">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      E-posta Gonder
    </a>
  </div></div>
</div>

<?php else: ?>
<div class="page-actions"><h1>Teklif Talepleri <span style="font-size:14px;font-weight:400;color:#888">(<?= count($quotes) ?>)</span></h1></div>
<div class="card"><div class="table-wrap">
  <table class="admin-table">
    <thead><tr><th>#</th><th>Ad / Firma</th><th>Telefon</th><th>Urun</th><th>Tarih</th><th>Durum</th><th style="text-align:right">Islemler</th></tr></thead>
    <tbody>
      <?php foreach ($quotes as $q): ?>
      <tr style="<?= $q['status']==='new'?'font-weight:600':'' ?>">
        <td><?= $q['id'] ?></td>
        <td><?= htmlspecialchars($q['name']) ?><br><span style="font-size:11px;color:#aaa"><?= htmlspecialchars($q['company'] ?? '') ?></span></td>
        <td><?= htmlspecialchars($q['phone']) ?></td>
        <td style="font-size:13px"><?= htmlspecialchars(substr($q['product'] ?? '-',0,30)) ?></td>
        <td style="font-size:12px;color:#888;white-space:nowrap"><?= date('d.m.Y H:i',strtotime($q['created_at'])) ?></td>
        <td><span class="badge badge-<?= $q['status'] ?>"><?= $statusLabels[$q['status']] ?></span></td>
        <td style="text-align:right">
          <div style="display:flex;gap:6px;justify-content:flex-end">
            <a href="/admin/talepler.php?action=view&id=<?= $q['id'] ?>" class="btn btn-sm btn-secondary">Gor</a>
            <a href="/admin/talepler.php?action=delete&id=<?= $q['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Sil?">Sil</a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($quotes)): ?><tr><td colspan="7" style="text-align:center;color:#999;padding:40px">Henuz talep yok.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
<?php endif; ?>
</main>
<?php require ROOT . '/admin/includes/footer.php'; ?>

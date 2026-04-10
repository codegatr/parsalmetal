<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Sayfalar';
$pdo = getDB(); $flash = getFlash();
$slug = $_GET['slug'] ?? 'hakkimizda';
$allowed = ['hakkimizda','kvkk','gizlilik','cerez'];
if (!in_array($slug, $allowed)) $slug = 'hakkimizda';
$pageTitles = ['hakkimizda'=>'Hakkimizda','kvkk'=>'KVKK Metni','gizlilik'=>'Gizlilik Politikasi','cerez'=>'Cerez Politikasi'];
$page = $pdo->prepare('SELECT * FROM ' . p() . 'pages WHERE slug=?'); $page->execute([$slug]); $page = $page->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $mt      = trim($_POST['meta_title'] ?? '');
    $md      = trim($_POST['meta_description'] ?? '');
    if ($page) {
        $pdo->prepare('UPDATE ' . p() . 'pages SET title=?,content=?,meta_title=?,meta_description=? WHERE slug=?')->execute([$title,$content,$mt,$md,$slug]);
    } else {
        $pdo->prepare('INSERT INTO ' . p() . 'pages (slug,title,content,meta_title,meta_description) VALUES (?,?,?,?,?)')->execute([$slug,$title,$content,$mt,$md]);
    }
    flash('success','Sayfa guncellendi.'); header('Location: /admin/sayfalar.php?slug=' . $slug); exit;
}
?>
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sayfalar - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>"></head><body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
<?php if (!empty($flash)): ?><div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
<div style="display:grid;grid-template-columns:200px 1fr;gap:24px">
  <div class="card" style="padding:12px">
    <?php foreach ($pageTitles as $s => $l): ?>
    <a href="/admin/sayfalar.php?slug=<?= $s ?>" style="display:block;padding:10px 14px;border-radius:8px;font-size:13px;font-weight:<?= $slug===$s?'700':'500' ?>;margin-bottom:4px;background:<?= $slug===$s?'#c0392b':'transparent' ?>;color:<?= $slug===$s?'#fff':'#333' ?>"><?= $l ?></a>
    <?php endforeach; ?>
  </div>
  <div>
    <div class="page-actions" style="margin-bottom:16px"><h1><?= $pageTitles[$slug] ?> Duzenle</h1></div>
    <div class="card"><div class="card-body">
      <form method="POST">
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Sayfa Basligi</label>
          <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($page['title'] ?? $pageTitles[$slug]) ?>">
        </div>
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Icerik (HTML destekler)</label>
          <textarea name="content" class="form-control" rows="18" style="font-family:monospace;font-size:13px"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
          <span class="form-hint">HTML etiketi kullanabilirsiniz: &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;strong&gt; vb.</span>
        </div>
        <div class="form-grid" style="margin-bottom:16px">
          <div class="form-group">
            <label class="form-label">Meta Baslik</label>
            <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($page['meta_title'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Meta Aciklama</label>
            <input type="text" name="meta_description" class="form-control" value="<?= htmlspecialchars($page['meta_description'] ?? '') ?>">
          </div>
        </div>
        <div style="display:flex;gap:10px">
          <button type="submit" class="btn btn-primary">Kaydet</button>
          <a href="/?page=<?= $slug ?>" target="_blank" class="btn btn-secondary">Sayfayi Gor</a>
        </div>
      </form>
    </div></div>
  </div>
</div>
</main>
<?php require ROOT . '/admin/includes/footer.php'; ?>

<?php
require_once ROOT . '/includes/header.php';
$pdo = getDB();
$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: /?page=urunler'); exit; }

$st = $pdo->prepare('SELECT pr.*, c.name as cat_name, c.slug as cat_slug FROM ' . p() . 'products pr LEFT JOIN ' . p() . 'categories c ON c.id=pr.category_id WHERE pr.slug=? AND pr.is_active=1');
$st->execute([$slug]);
$pr = $st->fetch();
if (!$pr) { header('Location: /?page=urunler'); exit; }

$pageMetaTitle = htmlspecialchars($pr['name']) . ' - ' . getSetting('site_title');
$pageMetaDesc  = htmlspecialchars($pr['short_desc'] ?? '');

$related = $pdo->prepare('SELECT * FROM ' . p() . 'products WHERE category_id=? AND id!=? AND is_active=1 LIMIT 3');
$related->execute([$pr['category_id'], $pr['id']]);
$relatedProducts = $related->fetchAll();
?>
    <h1><?= htmlspecialchars($pr['name']) ?></h1>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="product-detail-grid">
      <div>
        <div class="product-gallery-main" style="background:#f5f5f5;border-radius:12px;overflow:hidden">
          <?php if ($pr['image']): ?>
          <img src="<?= htmlspecialchars($pr['image']) ?>" alt="<?= htmlspecialchars($pr['name']) ?>" style="width:100%;height:420px;object-fit:cover">
          <?php else: ?>
          <div style="height:420px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1a1a1a,#2c3e50)">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div>
        <?php if ($pr['cat_name']): ?>
        <div class="product-detail-cat"><?= htmlspecialchars($pr['cat_name']) ?></div>
        <?php endif; ?>
        <h1 class="product-detail-title"><?= htmlspecialchars($pr['name']) ?></h1>
        <p class="product-detail-desc"><?= nl2br(htmlspecialchars($pr['short_desc'] ?? '')) ?></p>

        <?php if ($pr['specs']): ?>
        <div class="product-specs">
          <div class="specs-title">Teknik Ozellikler</div>
          <table class="specs-table">
            <?php foreach (explode("\n", trim($pr['specs'])) as $line):
              $parts = explode(':', $line, 2);
              if (count($parts) === 2): ?>
            <tr><td><?= htmlspecialchars(trim($parts[0])) ?></td><td><?= htmlspecialchars(trim($parts[1])) ?></td></tr>
            <?php endif; endforeach; ?>
          </table>
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:12px;margin-top:32px;flex-wrap:wrap">
          <a href="/?page=teklif&product=<?= urlencode($pr['name']) ?>" class="btn-main">
            Teklif Al
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <a href="/?page=iletisim" class="btn-outline">Bilgi Al</a>
        </div>
      </div>
    </div>

    <?php if ($pr['description']): ?>
    <div style="margin-top:60px">
      <h2 style="font-size:22px;font-weight:700;margin-bottom:20px">Urun Detaylari</h2>
      <div class="page-content"><?= $pr['description'] ?></div>
    </div>
    <?php endif; ?>

    <?php if ($relatedProducts): ?>
    <div style="margin-top:60px">
      <h2 style="font-size:22px;font-weight:700;margin-bottom:28px">Benzer Urunler</h2>
      <div class="products-grid">
        <?php foreach ($relatedProducts as $rp): ?>
        <div class="product-card">
          <div class="product-img" style="height:180px">
            <?php if ($rp['image']): ?>
            <img src="<?= htmlspecialchars($rp['image']) ?>" alt="<?= htmlspecialchars($rp['name']) ?>" style="height:180px;object-fit:cover">
            <?php else: ?>
            <div class="product-img-placeholder"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
            <?php endif; ?>
          </div>
          <div class="product-body">
            <h3 class="product-name"><?= htmlspecialchars($rp['name']) ?></h3>
            <div class="product-footer">
              <a href="/?page=urun&slug=<?= htmlspecialchars($rp['slug']) ?>" class="product-link">Detay &rsaquo;</a>
              <a href="/?page=teklif&product=<?= urlencode($rp['name']) ?>" class="product-cta">Teklif Al</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once ROOT . '/includes/footer.php'; ?>

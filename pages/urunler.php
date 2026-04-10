<?php
require_once ROOT . '/includes/header.php';
$pdo = getDB();

$catSlug = $_GET['cat'] ?? '';
$search  = trim($_GET['q'] ?? '');
$catId   = 0;

$cats = $pdo->query('SELECT * FROM ' . p() . 'categories WHERE is_active=1 ORDER BY sort_order')->fetchAll();
if ($catSlug) {
    $st = $pdo->prepare('SELECT id FROM ' . p() . 'categories WHERE slug=?');
    $st->execute([$catSlug]);
    $catId = (int)($st->fetchColumn() ?? 0);
}

$where = 'WHERE pr.is_active=1';
$params = [];
if ($catId) { $where .= ' AND pr.category_id=?'; $params[] = $catId; }
if ($search) { $where .= ' AND pr.name LIKE ?'; $params[] = '%' . $search . '%'; }

$total = (int)$pdo->prepare('SELECT COUNT(*) FROM ' . p() . 'products pr ' . $where)->execute($params) ? 0 : 0;
$st = $pdo->prepare('SELECT COUNT(*) FROM ' . p() . 'products pr ' . $where);
$st->execute($params);
$total = (int)$st->fetchColumn();

$page  = max(1, (int)($_GET['sayfa'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

$st2 = $pdo->prepare('SELECT pr.*, c.name as cat_name FROM ' . p() . 'products pr LEFT JOIN ' . p() . 'categories c ON c.id=pr.category_id ' . $where . ' ORDER BY pr.sort_order, pr.id LIMIT ' . $limit . ' OFFSET ' . $offset);
$st2->execute($params);
$products = $st2->fetchAll();

$pageMetaTitle = 'Urunler - ' . getSetting('site_title');
$pageMetaDesc  = 'Metal ve aluminyum urun katalogumuzu inceleyin.';
?>
    <h1>Urun Katalogu</h1>
    <p>Genis urun yelpazemizden ihtiyaciniza uygun secimi yapin.</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:240px 1fr;gap:40px;align-items:start">

      <!-- Sidebar -->
      <aside>
        <div style="background:#f8f8f8;border-radius:12px;padding:24px;margin-bottom:20px">
          <h3 style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#666;margin-bottom:16px">Kategoriler</h3>
          <a href="/?page=urunler" style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-radius:8px;font-size:14px;margin-bottom:4px;<?= !$catSlug ? 'background:#c0392b;color:#fff;font-weight:600' : 'color:#333' ?>">
            Tumu <span style="font-size:12px"><?= $total ?></span>
          </a>
          <?php foreach ($cats as $c): ?>
          <a href="/?page=urunler&cat=<?= htmlspecialchars($c['slug']) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-radius:8px;font-size:14px;margin-bottom:4px;<?= $catSlug === $c['slug'] ? 'background:#c0392b;color:#fff;font-weight:600' : 'color:#333' ?>">
            <?= htmlspecialchars($c['name']) ?>
          </a>
          <?php endforeach; ?>
        </div>

        <div style="background:#1a1a1a;border-radius:12px;padding:24px;color:#fff">
          <h3 style="font-size:15px;font-weight:700;margin-bottom:10px">Teklif Alin</h3>
          <p style="font-size:13px;color:rgba(255,255,255,.6);margin-bottom:16px">Ihtiyaciniza ozel fiyat almak icin hemen basvurun.</p>
          <a href="/?page=teklif" style="display:block;text-align:center;padding:11px;background:#c0392b;color:#fff;border-radius:8px;font-size:14px;font-weight:700">Teklif Al</a>
        </div>
      </aside>

      <!-- Products -->
      <div>
        <!-- Search -->
        <form method="GET" action="/" style="margin-bottom:28px;display:flex;gap:10px">
          <input type="hidden" name="page" value="urunler">
          <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($catSlug) ?>"><?php endif; ?>
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Urun ara..." style="flex:1;padding:11px 16px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;outline:none">
          <button type="submit" style="padding:11px 22px;background:#c0392b;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">Ara</button>
        </form>

        <p style="font-size:14px;color:#999;margin-bottom:24px"><?= $total ?> urun listeleniyor</p>

        <div class="products-grid">
          <?php foreach ($products as $pr): ?>
          <div class="product-card">
            <div class="product-img">
              <?php if ($pr['image']): ?>
              <img src="<?= htmlspecialchars($pr['image']) ?>" alt="<?= htmlspecialchars($pr['name']) ?>" loading="lazy">
              <?php else: ?>
              <div class="product-img-placeholder">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              </div>
              <?php endif; ?>
            </div>
            <div class="product-body">
              <div class="product-cat"><?= htmlspecialchars($pr['cat_name'] ?? '') ?></div>
              <h3 class="product-name"><?= htmlspecialchars($pr['name']) ?></h3>
              <p class="product-desc"><?= htmlspecialchars($pr['short_desc'] ?? '') ?></p>
              <div class="product-footer">
                <a href="/?page=urun&slug=<?= htmlspecialchars($pr['slug']) ?>" class="product-link">Detay &rsaquo;</a>
                <a href="/?page=teklif&product=<?= urlencode($pr['name']) ?>" class="product-cta">Teklif Al</a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($products)): ?>
          <div style="grid-column:1/-1;text-align:center;padding:80px 0;color:#999">
            <p>Urun bulunamadi.</p>
          </div>
          <?php endif; ?>
        </div>
        <?php echo paginate($total, $limit, $page, '/?page=urunler&cat=' . urlencode($catSlug) . '&q=' . urlencode($search) . '&'); ?>
      </div>
    </div>
  </div>
</section>

<?php require_once ROOT . '/includes/footer.php'; ?>

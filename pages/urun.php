<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/includes/header.php';
$pdo = getDB();

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: /?page=urunler'); exit; }

$st = $pdo->prepare('SELECT pr.*, c.name as cat_name, c.slug as cat_slug FROM ' . p() . 'products pr LEFT JOIN ' . p() . 'categories c ON c.id=pr.category_id WHERE pr.slug=? AND pr.is_active=1');
$st->execute([$slug]);
$pr = $st->fetch();
if (!$pr) { header('Location: /?page=urunler'); exit; }

$pageMetaTitle = htmlspecialchars($pr['name']) . ' - ' . getSetting('site_title');
$pageMetaDesc  = htmlspecialchars($pr['short_desc'] ?? '');

$related = $pdo->prepare('SELECT pr.*, c.name as cat_name FROM ' . p() . 'products pr LEFT JOIN ' . p() . 'categories c ON c.id=pr.category_id WHERE pr.category_id=? AND pr.id!=? AND pr.is_active=1 LIMIT 3');
$related->execute([$pr['category_id'], $pr['id']]);
$relatedProducts = $related->fetchAll();

$phone = getSetting('site_phone', '');
$phoneClean = preg_replace('/[^0-9]/', '', $phone);
?>
<style>
.urun-hero{width:100%;height:420px;border-radius:20px;overflow:hidden;margin-bottom:48px;position:relative}
.urun-hero img{width:100%;height:100%;object-fit:cover}
.urun-hero-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.75) 0%,rgba(0,0,0,.08) 55%,transparent 100%)}
.urun-hero-text{position:absolute;bottom:36px;left:44px;right:44px}
.urun-hero-eyebrow{font-size:11px;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px}
.urun-hero h1{font-size:34px;font-weight:800;color:#fff;margin:0 0 8px;line-height:1.2}
.urun-hero p{color:rgba(255,255,255,.78);font-size:15px;margin:0;max-width:600px}
.urun-wrap{display:grid;grid-template-columns:1fr 300px;gap:48px;align-items:start}
.urun-sidebar{position:sticky;top:calc(var(--header-h) + 24px);display:flex;flex-direction:column;gap:16px}
.specs-block{background:#f8f9fa;border-radius:14px;overflow:hidden}
.specs-block-head{background:#1a1a2e;color:#fff;padding:12px 18px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px}
.specs-table{width:100%;border-collapse:collapse}
.specs-table td{padding:10px 18px;border-bottom:1px solid #eee;font-size:13px;color:#555}
.specs-table td:first-child{font-weight:600;color:#1a1a2e;width:45%}
.specs-table tr:last-child td{border-bottom:none}
.specs-table tr:nth-child(even) td{background:#fff}
@media(max-width:900px){.urun-wrap{grid-template-columns:1fr}.urun-sidebar{position:static}}
@media(max-width:640px){.urun-hero{height:260px}.urun-hero h1{font-size:22px}.urun-hero-text{left:20px;right:20px;bottom:20px}}
</style>

<section class="section">
  <div class="container">

    <?php if ($pr['image']): ?>
    <div class="urun-hero">
      <img src="<?= htmlspecialchars($pr['image']) ?>" alt="<?= htmlspecialchars($pr['name']) ?>">
      <div class="urun-hero-overlay"></div>
      <div class="urun-hero-text">
        <?php if ($pr['cat_name']): ?><div class="urun-hero-eyebrow"><?= htmlspecialchars($pr['cat_name']) ?></div><?php endif; ?>
        <h1><?= htmlspecialchars($pr['name']) ?></h1>
        <p><?= htmlspecialchars($pr['short_desc'] ?? '') ?></p>
      </div>
    </div>
    <?php else: ?>
    <div style="margin-bottom:36px;padding-bottom:24px;border-bottom:2px solid #f0f0f0">
      <?php if ($pr['cat_name']): ?><div class="section-label"><?= htmlspecialchars($pr['cat_name']) ?></div><?php endif; ?>
      <h1 class="section-title"><?= htmlspecialchars($pr['name']) ?></h1>
      <p class="section-desc"><?= htmlspecialchars($pr['short_desc'] ?? '') ?></p>
    </div>
    <?php endif; ?>

    <div class="urun-wrap">
      <div>
        <?php if ($pr['description']): ?>
        <div class="page-content" style="margin-bottom:32px"><?= $pr['description'] ?></div>
        <?php endif; ?>

        <?php if ($pr['specs']): ?>
        <div class="specs-block">
          <div class="specs-block-head">Teknik Özellikler</div>
          <table class="specs-table">
            <?php foreach (explode("\n", trim($pr['specs'])) as $line):
              $parts = explode(':', $line, 2);
              if (count($parts) === 2): ?>
            <tr><td><?= htmlspecialchars(trim($parts[0])) ?></td><td><?= htmlspecialchars(trim($parts[1])) ?></td></tr>
            <?php endif; endforeach; ?>
          </table>
        </div>
        <?php endif; ?>

        <?php if ($relatedProducts): ?>
        <div style="margin-top:52px">
          <h2 style="font-size:20px;font-weight:700;color:#1a1a2e;margin-bottom:20px">Benzer Ürünler</h2>
          <div class="products-grid" style="grid-template-columns:repeat(3,1fr);gap:16px">
            <?php foreach ($relatedProducts as $rp): ?>
            <div class="product-card">
              <div class="product-img">
                <?php if ($rp['image']): ?><img src="<?= htmlspecialchars($rp['image']) ?>" alt="<?= htmlspecialchars($rp['name']) ?>" loading="lazy">
                <?php else: ?><div class="product-img-placeholder"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div><?php endif; ?>
              </div>
              <div class="product-body">
                <div class="product-cat"><?= htmlspecialchars($rp['cat_name'] ?? '') ?></div>
                <h3 class="product-name"><?= htmlspecialchars($rp['name']) ?></h3>
                <div class="product-footer">
                  <a href="/?page=urun&slug=<?= htmlspecialchars($rp['slug']) ?>" class="product-link">Detay →</a>
                  <a href="/?page=teklif&product=<?= urlencode($rp['name']) ?>" class="product-cta">Teklif Al</a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <div class="urun-sidebar">
        <div style="background:linear-gradient(145deg,#c0392b,#96281b);border-radius:16px;padding:28px;color:#fff">
          <h3 style="font-size:17px;font-weight:700;margin:0 0 8px">Ücretsiz Teklif Alın</h3>
          <p style="font-size:13px;color:rgba(255,255,255,.8);line-height:1.6;margin:0 0 20px">Bu ürün için fiyat teklifi ve detaylı bilgi alın.</p>
          <a href="/?page=teklif&product=<?= urlencode($pr['name']) ?>" style="display:block;background:#fff;color:#c0392b;text-align:center;padding:12px;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none;margin-bottom:10px">Hemen Teklif Al →</a>
          <?php if ($phone): ?>
          <a href="tel:<?= $phoneClean ?>" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;border:1px solid rgba(255,255,255,.3);border-radius:10px;font-size:13px;color:#fff;text-decoration:none">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.81 19.79 19.79 0 0117.7 2a2 2 0 012.28 2v3"/></svg>
            <?= htmlspecialchars($phone) ?>
          </a>
          <?php endif; ?>
        </div>
        <div style="background:#fff;border:1px solid #eaedf0;border-radius:14px;padding:20px">
          <h4 style="font-size:12px;font-weight:700;color:#999;margin:0 0 14px;text-transform:uppercase;letter-spacing:1.5px">Hızlı Bilgi</h4>
          <?php foreach(['Ücretsiz keşif ve ölçüm','Anahtar teslim montaj','Satış sonrası garanti','Konya geneli teslimat'] as $item): ?>
          <div style="display:flex;gap:10px;align-items:center;font-size:13px;color:#555;padding:6px 0">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <?= $item ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require_once ROOT . '/includes/footer.php'; ?>

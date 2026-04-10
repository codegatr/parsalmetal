<?php
require_once ROOT . '/includes/header.php';
http_response_code(404);
$pageMetaTitle = '404 - Sayfa Bulunamadi - ' . getSetting('site_title');
$pageMetaDesc  = '';
?>
<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:60px 20px;margin-top:80px">
  <div>
    <div style="font-size:120px;font-weight:900;color:#eee;line-height:1;font-family:'Montserrat',sans-serif">404</div>
    <h1 style="font-size:28px;font-weight:700;margin-bottom:12px;color:#1a1a1a">Sayfa Bulunamadi</h1>
    <p style="color:#888;font-size:16px;margin-bottom:32px">Aradaginiz sayfa mevcut degil veya tasindi.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      <a href="/" style="padding:12px 28px;background:#c0392b;color:#fff;border-radius:8px;font-weight:700;font-size:15px">Ana Sayfaya Don</a>
      <a href="/?page=iletisim" style="padding:12px 28px;border:2px solid #ddd;color:#333;border-radius:8px;font-weight:600;font-size:15px">Iletisim</a>
    </div>
  </div>
</div>
<?php require_once ROOT . '/includes/footer.php'; ?>

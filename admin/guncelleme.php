<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Guncelleme';
$pdo = getDB();
$pageTitle = 'Guncelleme';

$currentVer  = defined('APP_VERSION') ? APP_VERSION : '1.0.0';
$repoSlug    = getSetting('github_repo',  '');
$githubToken = getSetting('github_token', '');

$msg = ''; $msgType = '';

/* ---------- AJAX: surum kontrol ---------- */
if (isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json; charset=utf-8');
    if (!function_exists('curl_init')) {
        echo json_encode(['error' => 'Sunucuda cURL aktif degil.']);
        exit;
    }
    if (!$repoSlug) {
        echo json_encode(['error' => 'GitHub repo ayarlanmamis. Lutfen Site Ayarlari sayfasini doldurun.']);
        exit;
    }
    $ch = curl_init("https://api.github.com/repos/$repoSlug/releases/latest");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => 'ParsalCMS/1.0',
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => $githubToken ? ["Authorization: token $githubToken"] : [],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!$body || $code !== 200) {
        echo json_encode(['error' => "GitHub API yanit vermedi (HTTP $code)."]);
        exit;
    }
    $data = json_decode($body, true);
    if (!$data || !isset($data['tag_name'])) {
        echo json_encode(['error' => 'Release bulunamadi veya API yaniti hatali.']);
        exit;
    }
    // ZIP asset bul
    $zipUrl = '';
    foreach ((array)($data['assets'] ?? []) as $asset) {
        if (substr($asset['name'], -4) === '.zip') {
            $zipUrl = $asset['browser_download_url'];
            break;
        }
    }
    if (!$zipUrl) $zipUrl = $data['zipball_url'] ?? '';
    echo json_encode([
        'tag'      => $data['tag_name'],
        'name'     => $data['name'] ?? $data['tag_name'],
        'body'     => substr($data['body'] ?? '', 0, 500),
        'date'     => isset($data['published_at']) ? date('d.m.Y', strtotime($data['published_at'])) : '',
        'zip_url'  => $zipUrl,
        'current'  => $currentVer,
        'is_newer' => version_compare(ltrim($data['tag_name'], 'v'), $currentVer, '>'),
    ]);
    exit;
}

/* ---------- POST: guncelleme uygula ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_update'])) {
    $zipUrl  = $_POST['zip_url']  ?? '';
    $version = ltrim(trim($_POST['version'] ?? ''), 'v');
    $log = [];

    try {
        if (!$zipUrl || !$version) throw new Exception('Eksik parametre.');
        if (!function_exists('curl_init')) throw new Exception('cURL aktif degil.');
        if (!class_exists('ZipArchive'))  throw new Exception('ZipArchive aktif degil.');

        // ZIP indir
        $tmpZip = sys_get_temp_dir() . '/parsal_up_' . time() . '.zip';
        $ch = curl_init($zipUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'ParsalCMS/1.0',
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => $githubToken ? ["Authorization: token $githubToken"] : [],
        ]);
        $zipData = curl_exec($ch);
        curl_close($ch);

        if (!$zipData || strlen($zipData) < 100) throw new Exception('ZIP indirilemedi.');
        file_put_contents($tmpZip, $zipData);

        // ZIP ac
        $zip = new ZipArchive();
        if ($zip->open($tmpZip) !== true) {
            unlink($tmpZip);
            throw new Exception('ZIP acilamadi.');
        }

        // manifest.json oku
        $manifestJson = $zip->getFromName('manifest.json');
        $files = [];
        if ($manifestJson) {
            $m = json_decode($manifestJson, true);
            $files = $m['files'] ?? [];
        }

        if (empty($files)) {
            $zip->extractTo(ROOT);
            $log[] = 'Manifest yok, tum dosyalar yuklendi.';
        } else {
            foreach ($files as $f) {
                $fc = $zip->getFromName($f);
                if ($fc === false) { $log[] = "Atla: $f"; continue; }
                $dest = ROOT . '/' . ltrim($f, '/');
                $dir  = dirname($dest);
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                file_put_contents($dest, $fc);
                $log[] = "OK: $f";
            }
        }
        $zip->close();
        unlink($tmpZip);

        // config.php versiyonu guncelle
        if (file_exists(ROOT . '/config.php')) {
            $cfg = file_get_contents(ROOT . '/config.php');
            $cfg = preg_replace("/define\('APP_VERSION',\s*'[^']+'\)/", "define('APP_VERSION', '$version')", $cfg);
            file_put_contents(ROOT . '/config.php', $cfg);
        }

        // DB log
        $pdo->prepare('INSERT INTO ' . p() . 'updates (version, notes, status) VALUES (?,?,?)')
            ->execute([$version, implode("\n", $log), 'success']);

        flash('success', "v$version basariyla yuklendi.");
    } catch (Exception $e) {
        $pdo->prepare('INSERT INTO ' . p() . 'updates (version, notes, status) VALUES (?,?,?)')
            ->execute([$version ?: '?', $e->getMessage(), 'failed']);
        flash('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: /admin/guncelleme.php');
    exit;
}

$history = $pdo->query('SELECT * FROM ' . p() . 'updates ORDER BY updated_at DESC LIMIT 15')->fetchAll();
$flash   = getFlash();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Guncelleme - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>">
</head>
<body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<div class="page-actions">
  <h1>Sistem Guncellemesi</h1>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

  <!-- Surum Paneli -->
  <div class="card">
    <div class="card-header"><div class="card-title">Surum Kontrolu</div></div>
    <div class="card-body">

      <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px">
        <div style="flex:1;padding:16px;background:#f8f8f8;border-radius:8px;text-align:center">
          <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Aktif Surum</div>
          <div style="font-size:26px;font-weight:800;color:#1a1a2e">v<?= htmlspecialchars($currentVer) ?></div>
        </div>
        <div style="font-size:24px;color:#ccc">→</div>
        <div id="latestBox" style="flex:1;padding:16px;background:#f8f8f8;border:1px solid #eee;border-radius:8px;text-align:center">
          <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Son Surum</div>
          <div style="font-size:16px;color:#ccc">—</div>
        </div>
      </div>

      <button id="btnCheck" onclick="checkRelease()" class="btn btn-secondary" style="width:100%;justify-content:center;margin-bottom:16px">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Surum Kontrol Et
      </button>

      <div id="releaseInfo" style="display:none">
        <div id="releaseBox" style="padding:14px;background:#f8f8f8;border-radius:8px;margin-bottom:14px;font-size:13px;color:#555;line-height:1.6"></div>
        <form method="POST" id="updateForm" onsubmit="return confirm('Guncelleme uygulanacak, emin misiniz?')">
          <input type="hidden" name="apply_update" value="1">
          <input type="hidden" name="zip_url" id="zipUrl">
          <input type="hidden" name="version" id="version">
          <button type="submit" id="btnUpdate" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Guncelle
          </button>
        </form>
      </div>

      <div id="upToDate" style="display:none" class="alert alert-success">Zaten en guncel surum kullaniliyor.</div>
      <div id="checkError" style="display:none" class="alert alert-error"></div>

      <div style="margin-top:12px">
        <a href="https://github.com/<?= htmlspecialchars($repoSlug) ?>/releases" target="_blank"
           class="btn btn-secondary" style="width:100%;justify-content:center">
          GitHub Releases
        </a>
      </div>
    </div>
  </div>

  <!-- Gecmis -->
  <div class="card">
    <div class="card-header"><div class="card-title">Guncelleme Gecmisi</div></div>
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Surum</th><th>Durum</th><th>Tarih</th></tr></thead>
        <tbody>
          <?php foreach ($history as $h): ?>
          <tr>
            <td><strong>v<?= htmlspecialchars($h['version']) ?></strong></td>
            <td><span class="badge badge-<?= $h['status'] === 'success' ? 'active' : 'passive' ?>">
              <?= $h['status'] === 'success' ? 'Basarili' : 'Basarisiz' ?>
            </span></td>
            <td style="font-size:12px;color:#888"><?= date('d.m.Y H:i', strtotime($h['updated_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($history)): ?>
          <tr><td colspan="3" style="text-align:center;color:#999;padding:32px">Gecmis kayit yok.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</main>

<script>
function checkRelease() {
  var btn = document.getElementById('btnCheck');
  btn.disabled = true;
  btn.textContent = 'Kontrol ediliyor...';
  document.getElementById('releaseInfo').style.display = 'none';
  document.getElementById('upToDate').style.display    = 'none';
  document.getElementById('checkError').style.display  = 'none';

  fetch('/admin/guncelleme.php?action=check')
    .then(function(r){ return r.json(); })
    .then(function(d) {
      btn.disabled = false;
      btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Surum Kontrol Et';

      if (d.error) {
        document.getElementById('checkError').style.display = 'block';
        document.getElementById('checkError').textContent = d.error;
        return;
      }

      var lb = document.getElementById('latestBox');
      lb.innerHTML = '<div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Son Surum</div>'
                   + '<div style="font-size:26px;font-weight:800;color:#10b981">' + d.tag + '</div>';
      lb.style.background = 'rgba(16,185,129,.1)';
      lb.style.border     = '1px solid rgba(16,185,129,.3)';

      if (!d.is_newer) {
        document.getElementById('upToDate').style.display = 'block';
        return;
      }

      var info = '<strong>' + d.name + '</strong>';
      if (d.date) info += ' <span style="color:#aaa;font-size:11px">' + d.date + '</span>';
      if (d.body) info += '<br><br>' + d.body.replace(/\n/g,'<br>');
      document.getElementById('releaseBox').innerHTML  = info;
      document.getElementById('zipUrl').value   = d.zip_url;
      document.getElementById('version').value  = d.tag;
      document.getElementById('btnUpdate').textContent = d.tag + ' surumune guncelle';
      document.getElementById('releaseInfo').style.display = 'block';
    })
    .catch(function(e) {
      btn.disabled = false;
      document.getElementById('checkError').style.display = 'block';
      document.getElementById('checkError').textContent   = 'Baglanti hatasi: ' + e.message;
    });
}
</script>

<?php require ROOT . '/admin/includes/footer.php'; ?>

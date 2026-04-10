<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Guncelleme';

$currentVer  = defined('APP_VERSION') ? APP_VERSION : '1.0.0';
$repoSlug    = getSetting('github_repo',  '');
$githubToken = getSetting('github_token', '');

/* ---------- AJAX: surum kontrol ---------- */
if (isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json; charset=utf-8');
    if (!function_exists('curl_init')) { echo json_encode(['error' => 'cURL aktif degil.']); exit; }
    if (!$repoSlug) { echo json_encode(['error' => 'GitHub repo ayarlanmamis.']); exit; }
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
    $err  = curl_error($ch);
    curl_close($ch);
    if (!$body || $code !== 200) { echo json_encode(['error' => "GitHub yanit vermedi (HTTP $code). $err"]); exit; }
    $data = json_decode($body, true);
    if (!$data || !isset($data['tag_name'])) { echo json_encode(['error' => 'Release bulunamadi.']); exit; }
    $zipUrl = '';
    foreach ((array)($data['assets'] ?? []) as $asset) {
        if (substr($asset['name'], -4) === '.zip') { $zipUrl = $asset['browser_download_url']; break; }
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
        $curlErr = curl_error($ch);
        curl_close($ch);

        if (!$zipData || strlen($zipData) < 200) {
            throw new Exception('ZIP indirilemedi. Hata: ' . ($curlErr ?: 'bos yanit'));
        }
        file_put_contents($tmpZip, $zipData);
        $log[] = 'ZIP indirildi: ' . round(strlen($zipData)/1024) . ' KB';

        // ZIP ac
        $zip = new ZipArchive();
        $openRes = $zip->open($tmpZip);
        if ($openRes !== true) {
            unlink($tmpZip);
            throw new Exception('ZIP acilamadi. Kod: ' . $openRes);
        }

        // manifest.json bul - kök, update/ altı, veya zipball prefix
        $manifestJson = $zip->getFromName('manifest.json');
        if ($manifestJson === false) $manifestJson = $zip->getFromName('update/manifest.json');
        if ($manifestJson === false) {
            for ($i = 0; $i < min(10, $zip->numFiles); $i++) {
                $name = $zip->getNameIndex($i);
                if ($name !== false && substr($name, -13) === 'manifest.json') {
                    $manifestJson = $zip->getFromIndex($i);
                    $log[] = 'Manifest bulundu: ' . $name;
                    break;
                }
            }
        }

        $files = []; $sqlMigrations = [];
        if ($manifestJson) {
            $m = json_decode($manifestJson, true);
            $files         = $m['files']      ?? [];
            $sqlMigrations = $m['migrations'] ?? [];
            $log[] = count($files) . ' dosya, ' . count($sqlMigrations) . ' migration.';
        } else {
            $log[] = 'Manifest bulunamadi - tum dosyalar cikartilacak.';
        }

        // ZIP icindeki prefix belirle (GitHub zipball: repo-tag/ klasoru)
        $prefix = '';
        if (!empty($files)) {
            $firstFile = $zip->getNameIndex(0);
            if ($firstFile !== false && strpos($firstFile, '/') !== false) {
                $parts = explode('/', $firstFile);
                $candidate = $parts[0] . '/';
                // Eger manifest'teki dosyalar bu prefix ile baslamiyorsa prefix'tir
                if (!empty($files) && $zip->getFromName($files[0]) === false) {
                    $prefix = $candidate;
                    $log[] = 'ZIP prefix: ' . $prefix;
                }
            }
        }

        if (empty($files)) {
            $zip->extractTo(ROOT);
            $log[] = 'Tum dosyalar cikartildi.';
        } else {
            foreach ($files as $f) {
                $fc = $zip->getFromName($f);
                if ($fc === false) $fc = $zip->getFromName($prefix . $f);
                if ($fc === false) { $log[] = "Atla: $f"; continue; }
                $dest = ROOT . '/' . ltrim($f, '/');
                $dir  = dirname($dest);
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $written = file_put_contents($dest, $fc);
                $log[] = ($written !== false) ? "OK: $f" : "HATA (izin?): $f";
            }
        }
        $zip->close();
        unlink($tmpZip);

        // SQL Migrations
        foreach ($sqlMigrations as $sqlFile) {
            $sqlPath = ROOT . '/update/' . $sqlFile;
            if (file_exists($sqlPath)) {
                try {
                    $pdo->exec(file_get_contents($sqlPath));
                    $log[] = "Migration OK: $sqlFile";
                } catch (Exception $sqlEx) {
                    $log[] = "Migration HATA: $sqlFile - " . $sqlEx->getMessage();
                }
            } else {
                $log[] = "Migration dosya yok: $sqlFile";
            }
        }

        // config.php versiyon
        if (file_exists(ROOT . '/config.php')) {
            $cfg    = file_get_contents(ROOT . '/config.php');
            $newCfg = preg_replace("/define\('APP_VERSION',\s*'[^']+'\)/", "define('APP_VERSION', '$version')", $cfg);
            if ($newCfg && $newCfg !== $cfg) {
                file_put_contents(ROOT . '/config.php', $newCfg);
                $log[] = 'config.php v' . $version . ' olarak guncellendi.';
            } else {
                $log[] = 'UYARI: config.php guncellenemedi (yazma izni kontrol et).';
            }
        }

        $pdo->prepare('INSERT INTO ' . p() . 'updates (version, notes, status) VALUES (?,?,?)')
            ->execute([$version, implode("\n", $log), 'success']);
        flash('success', "v$version basariyla yuklendi! " . count($files) . " dosya guncellendi.");

    } catch (Exception $e) {
        $errMsg = $e->getMessage();
        $pdo->prepare('INSERT INTO ' . p() . 'updates (version, notes, status) VALUES (?,?,?)')
            ->execute([$version ?: '?', $errMsg . "\n" . implode("\n", $log), 'failed']);
        flash('error', 'Guncelleme hatasi: ' . $errMsg);
    }
    header('Location: /admin/guncelleme.php');
    exit;
}

$history = $pdo->query('SELECT * FROM ' . p() . 'updates ORDER BY updated_at DESC LIMIT 15')->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Guncelleme - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>">
</head>
<body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">

<?php $f = getFlash(); if (!empty($f)): ?>
<div class="alert alert-<?= $f['type'] ?>"><?= htmlspecialchars($f['msg']) ?></div>
<?php endif; ?>

<div class="page-actions"><h1>Sistem Guncellemesi</h1></div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
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
        <form method="POST" onsubmit="return confirm('Guncelleme uygulanacak, emin misiniz?')">
          <input type="hidden" name="apply_update" value="1">
          <input type="hidden" name="zip_url" id="zipUrl">
          <input type="hidden" name="version" id="version">
          <button type="submit" id="btnUpdate" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Guncelle
          </button>
        </form>
      </div>
      <div id="upToDate" style="display:none" class="alert alert-success">En guncel surum kullaniliyor.</div>
      <div id="checkError" style="display:none" class="alert alert-error"></div>

      <div style="margin-top:12px">
        <a href="https://github.com/<?= htmlspecialchars($repoSlug) ?>/releases" target="_blank"
           class="btn btn-secondary" style="width:100%;justify-content:center">GitHub Releases</a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title">Guncelleme Gecmisi</div></div>
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Surum</th><th>Durum / Detay</th><th>Tarih</th></tr></thead>
        <tbody>
          <?php foreach ($history as $h): ?>
          <tr>
            <td><strong>v<?= htmlspecialchars($h['version']) ?></strong></td>
            <td>
              <span class="badge badge-<?= $h['status'] === 'success' ? 'active' : 'passive' ?>">
                <?= $h['status'] === 'success' ? 'Basarili' : 'Basarisiz' ?>
              </span>
              <?php
              $notes = trim($h['notes'] ?? '');
              if ($h['status'] !== 'success' && $notes):
                $firstLine = explode("\n", $notes)[0]; ?>
              <div style="font-size:11px;color:#ef4444;margin-top:4px"><?= htmlspecialchars(substr($firstLine,0,150)) ?></div>
              <?php elseif ($h['status'] === 'success' && $notes):
                $lines = array_filter(explode("\n", $notes));
                $okCount = count(array_filter($lines, function($l){ return strpos($l,'OK:')===0; })); ?>
              <div style="font-size:11px;color:#888;margin-top:4px"><?= $okCount ?> dosya guncellendi</div>
              <?php endif; ?>
            </td>
            <td style="font-size:12px;color:#888;white-space:nowrap"><?= date('d.m.Y H:i', strtotime($h['updated_at'])) ?></td>
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
  btn.disabled = true; btn.textContent = 'Kontrol ediliyor...';
  ['releaseInfo','upToDate','checkError'].forEach(function(id){ document.getElementById(id).style.display='none'; });
  fetch('/admin/guncelleme.php?action=check')
    .then(function(r){ return r.json(); })
    .then(function(d) {
      btn.disabled = false;
      btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Surum Kontrol Et';
      if (d.error) {
        document.getElementById('checkError').style.display = 'block';
        document.getElementById('checkError').textContent = d.error; return;
      }
      var lb = document.getElementById('latestBox');
      lb.innerHTML = '<div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Son Surum</div><div style="font-size:26px;font-weight:800;color:#10b981">'+d.tag+'</div>';
      lb.style.background='rgba(16,185,129,.1)'; lb.style.border='1px solid rgba(16,185,129,.3)';
      if (!d.is_newer) { document.getElementById('upToDate').style.display='block'; return; }
      var info = '<strong>'+d.name+'</strong>';
      if (d.date) info += ' <span style="color:#aaa;font-size:11px">'+d.date+'</span>';
      if (d.body) info += '<br><br>'+d.body.replace(/\n/g,'<br>');
      document.getElementById('releaseBox').innerHTML = info;
      document.getElementById('zipUrl').value  = d.zip_url;
      document.getElementById('version').value = d.tag;
      document.getElementById('btnUpdate').innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> '+d.tag+' Surumune Guncelle';
      document.getElementById('releaseInfo').style.display = 'block';
    })
    .catch(function(e) {
      btn.disabled = false;
      document.getElementById('checkError').style.display = 'block';
      document.getElementById('checkError').textContent = 'Baglanti hatasi: '+e.message;
    });
}
</script>

<?php require ROOT . '/admin/includes/footer.php'; ?>

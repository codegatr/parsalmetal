<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Guncelleme';

// Versiyon: config.php > DB > fallback
$currentVer = '1.0.0';
if (defined('APP_VERSION') && APP_VERSION !== '') {
    $currentVer = APP_VERSION;
}
$dbVer = getSetting('app_version', '');
if ($dbVer !== '') $currentVer = $dbVer;
$currentVer = ltrim($currentVer, 'v'); // daima v-siz sakla

$repoSlug    = getSetting('github_repo',  '');
$githubToken = getSetting('github_token', '');

/* ---- AJAX: surum kontrol ---- */
if (isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json; charset=utf-8');
    if (!function_exists('curl_init')) { echo json_encode(['error' => 'cURL aktif degil.']); exit; }
    if (!$repoSlug)                    { echo json_encode(['error' => 'GitHub repo ayarlanmamis.']); exit; }
    $ch = curl_init("https://api.github.com/repos/$repoSlug/releases/latest");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => 'ParsalCMS/1.0',
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => array_filter([
            $githubToken ? "Authorization: token $githubToken" : null,
            "Accept: application/vnd.github.v3+json",
        ]),
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);
    if (!$body || $code !== 200) {
        echo json_encode(['error' => "GitHub yanit vermedi (HTTP $code). $cerr"]); exit;
    }
    $data = json_decode($body, true);
    if (!isset($data['tag_name'])) {
        echo json_encode(['error' => 'Release bulunamadi.']); exit;
    }
    $zipUrl = '';
    foreach ((array)($data['assets'] ?? []) as $a) {
        if (substr($a['name'], -4) === '.zip') { $zipUrl = $a['browser_download_url']; break; }
    }
    if (!$zipUrl) $zipUrl = $data['zipball_url'] ?? '';
    $tag = ltrim($data['tag_name'], 'v');
    echo json_encode([
        'tag'      => $data['tag_name'],
        'name'     => $data['name'] ?? $data['tag_name'],
        'body'     => substr($data['body'] ?? '', 0, 500),
        'date'     => isset($data['published_at']) ? date('d.m.Y', strtotime($data['published_at'])) : '',
        'zip_url'  => $zipUrl,
        'current'  => $currentVer,
        'is_newer' => version_compare($tag, $currentVer, '>'),
    ]);
    exit;
}

/* ---- Ortak: ZIP uygula ---- */
function applyZip(string $tmpZip, string $version, $pdo): array
{
    $log = [];
    $ver = ltrim($version, 'v');

    if (!class_exists('ZipArchive')) {
        throw new Exception('ZipArchive PHP eklentisi aktif degil. Hosting panelinden PHP eklentilerini kontrol edin.');
    }

    $zip = new ZipArchive();
    $res = $zip->open($tmpZip);
    if ($res !== true) {
        throw new Exception("ZIP acilamadi (kod: $res). Dosya bozuk olabilir.");
    }

    // manifest.json bul
    $mJson = false;
    $searchPaths = ['manifest.json', 'update/manifest.json'];
    foreach ($searchPaths as $mp) {
        $t = $zip->getFromName($mp);
        if ($t !== false) { $mJson = $t; break; }
    }
    // zipball prefix ile dene (GitHub zipball: repo-tag/ altinda)
    if ($mJson === false) {
        for ($i = 0; $i < min(20, $zip->numFiles); $i++) {
            $n = $zip->getNameIndex($i);
            if ($n !== false && substr($n, -13) === 'manifest.json') {
                $mJson = $zip->getFromIndex($i);
                break;
            }
        }
    }

    $files = [];
    $migrations = [];
    if ($mJson) {
        $m          = json_decode($mJson, true);
        $files      = $m['files']      ?? [];
        $migrations = $m['migrations'] ?? [];
        $log[] = 'Manifest: ' . count($files) . ' dosya, ' . count($migrations) . ' migration.';
    } else {
        $log[] = 'Manifest bulunamadi, tum dosyalar cikartiliyor.';
    }

    // GitHub zipball prefix belirle
    $prefix = '';
    if (!empty($files)) {
        $first = $zip->getNameIndex(0);
        if ($first !== false && strpos($first, '/') !== false) {
            $p = explode('/', $first)[0] . '/';
            if ($zip->getFromName($files[0]) === false && $zip->getFromName($p . $files[0]) !== false) {
                $prefix = $p;
                $log[]  = "ZIP prefix: $prefix";
            }
        }
    }

    // Dosyalari uygula
    $ok   = 0;
    $fail = 0;
    if (empty($files)) {
        if ($zip->extractTo(ROOT)) {
            $log[] = 'Tum dosyalar cikartildi.';
        } else {
            $log[] = 'extractTo HATASI. Yazma izni kontrol edin.';
        }
    } else {
        foreach ($files as $f) {
            $fc = $zip->getFromName($f);
            if ($fc === false) $fc = $zip->getFromName($prefix . $f);
            if ($fc === false) {
                $log[] = "Atla (ZIP icinde yok): $f";
                $fail++;
                continue;
            }
            $dest = ROOT . '/' . ltrim($f, '/');
            $dir  = dirname($dest);

            // Dizin olustur
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    $log[] = "DIZIN HATASI: $dir";
                    $fail++;
                    continue;
                }
            }

            // Yaz
            $written = file_put_contents($dest, $fc);
            if ($written === false) {
                $log[] = "YAZMA IZNI HATASI: $f";
                $log[] = "  -> Cozum: $dest dosyasina chmod 644 verin.";
                $fail++;
            } else {
                $log[] = "OK: $f ($written byte)";
                $ok++;
            }
        }
    }
    $zip->close();
    $log[] = "Sonuc: $ok OK, $fail hata.";

    // SQL migrations
    foreach ($migrations as $sf) {
        $sp = ROOT . '/update/' . $sf;
        if (file_exists($sp)) {
            try {
                $pdo->exec(file_get_contents($sp));
                $log[] = "Migration OK: $sf";
            } catch (Exception $e) {
                $log[] = "Migration HATA: $sf -> " . $e->getMessage();
            }
        } else {
            $log[] = "Migration dosya bulunamadi: $sf";
        }
    }

    // Versiyon kaydet - once DB (her zaman calisir)
    try {
        $pdo->prepare(
            "INSERT INTO " . p() . "settings (setting_key, setting_value) VALUES ('app_version', ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        )->execute([$ver]);
        $log[] = "Versiyon DB'ye kaydedildi: $ver";
    } catch (Exception $e) {
        $log[] = "DB versiyon hatasi: " . $e->getMessage();
    }

    // config.php - yazma izni varsa guncelle
    $cfgPath = ROOT . '/config.php';
    if (file_exists($cfgPath)) {
        if (is_writable($cfgPath)) {
            $cfg    = file_get_contents($cfgPath);
            $newCfg = preg_replace(
                "/define\(\s*'APP_VERSION'\s*,\s*'[^']*'\s*\)/",
                "define('APP_VERSION', '$ver')",
                $cfg
            );
            if ($newCfg !== null && $newCfg !== $cfg) {
                if (file_put_contents($cfgPath, $newCfg) !== false) {
                    $log[] = "config.php guncellendi: APP_VERSION = '$ver'";
                } else {
                    $log[] = "config.php yazma hatasi.";
                }
            } elseif ($newCfg === $cfg) {
                // Satir yoksa ekle
                if (strpos($cfg, 'APP_VERSION') === false) {
                    file_put_contents($cfgPath, $cfg . "\ndefine('APP_VERSION', '$ver');\n");
                    $log[] = "config.php'ye APP_VERSION eklendi: $ver";
                } else {
                    $log[] = "config.php APP_VERSION zaten guncel.";
                }
            }
        } else {
            $log[] = "UYARI: config.php yazma izni yok (dosya izni: " . substr(sprintf('%o', fileperms($cfgPath)), -4) . ").";
            $log[] = "  -> Manuel degistir: define('APP_VERSION', '$ver')";
            $log[] = "  -> Ya da: chmod 644 config.php";
        }
    }

    return $log;
}

/* ---- POST: GitHub ile indir ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_github'])) {
    $zipUrl  = trim($_POST['zip_url']  ?? '');
    $version = ltrim(trim($_POST['version'] ?? ''), 'v'); // v-siz sakla
    $log = [];
    try {
        if (!$zipUrl || !$version) throw new Exception('Eksik parametre.');
        if (!function_exists('curl_init')) throw new Exception('cURL aktif degil.');

        $tmpZip = sys_get_temp_dir() . '/parsal_gh_' . time() . '.zip';
        $fp = fopen($tmpZip, 'wb');
        $ch = curl_init($zipUrl);
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'ParsalCMS/1.0',
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => array_filter([
                $githubToken ? "Authorization: token $githubToken" : null,
                "Accept: application/octet-stream",
            ]),
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        $size = file_exists($tmpZip) ? filesize($tmpZip) : 0;
        if ($size < 200) {
            if (file_exists($tmpZip)) unlink($tmpZip);
            throw new Exception("ZIP indirilemedi (boyut: {$size}B, HTTP: $code, hata: $cerr). Manuel ZIP kullanin.");
        }
        $log[] = 'ZIP indirildi: ' . round($size / 1024) . ' KB';
        $log   = array_merge($log, applyZip($tmpZip, $version, $pdo));
        unlink($tmpZip);

        $pdo->prepare('INSERT INTO ' . p() . 'updates (version, notes, status) VALUES (?,?,?)')
            ->execute([$version, implode("\n", $log), 'success']);
        flash('success', "v$version basariyla yuklendi!");
    } catch (Exception $e) {
        if (isset($tmpZip) && file_exists($tmpZip)) unlink($tmpZip);
        $pdo->prepare('INSERT INTO ' . p() . 'updates (version, notes, status) VALUES (?,?,?)')
            ->execute([$version ?: '?', $e->getMessage() . "\n" . implode("\n", $log), 'failed']);
        flash('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: /admin/guncelleme.php'); exit;
}

/* ---- POST: Manuel ZIP ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_manual'])) {
    $version = '';
    $log = [];
    try {
        if (!class_exists('ZipArchive')) throw new Exception('ZipArchive aktif degil.');
        if (empty($_FILES['zip_file']['tmp_name'])) throw new Exception('ZIP secilmedi.');
        if ($_FILES['zip_file']['error'] !== UPLOAD_ERR_OK) {
            $errCodes = [1=>'Max boyut (php.ini)',2=>'Max boyut (form)',3=>'Parcali yukleme',4=>'Dosya secilmedi',6=>'Temp klasor yok',7=>'Disk yazma hatasi'];
            throw new Exception('Yukleme hatasi: ' . ($errCodes[$_FILES['zip_file']['error']] ?? 'Kod ' . $_FILES['zip_file']['error']));
        }
        $size = $_FILES['zip_file']['size'];
        if ($size < 100) throw new Exception("ZIP cok kucuk ($size byte).");

        $tmpZip = sys_get_temp_dir() . '/parsal_man_' . time() . '.zip';
        if (!move_uploaded_file($_FILES['zip_file']['tmp_name'], $tmpZip)) {
            throw new Exception('ZIP tasinma hatasi. Temp klasor yazma izni kontrol edin.');
        }
        $log[] = 'ZIP yuklendi: ' . round($size / 1024) . ' KB (' . $_FILES['zip_file']['name'] . ')';

        // Versiyonu manifest'ten oku
        $zv = new ZipArchive();
        if ($zv->open($tmpZip) === true) {
            foreach (['manifest.json', 'update/manifest.json'] as $mp) {
                $mj = $zv->getFromName($mp);
                if ($mj !== false) {
                    $md = json_decode($mj, true);
                    if (!empty($md['version'])) { $version = ltrim($md['version'], 'v'); break; }
                }
            }
            $zv->close();
        }
        if (!$version) throw new Exception('Versiyon okunamadi. manifest.json icinde "version" alani eksik.');
        $log[] = "manifest.json versiyonu: $version";

        $log = array_merge($log, applyZip($tmpZip, $version, $pdo));
        unlink($tmpZip);

        $pdo->prepare('INSERT INTO ' . p() . 'updates (version, notes, status) VALUES (?,?,?)')
            ->execute([$version, implode("\n", $log), 'success']);
        flash('success', "v$version basariyla yuklendi!");
    } catch (Exception $e) {
        if (isset($tmpZip) && file_exists($tmpZip)) unlink($tmpZip);
        $pdo->prepare('INSERT INTO ' . p() . 'updates (version, notes, status) VALUES (?,?,?)')
            ->execute([$version ?: '?', $e->getMessage() . "\n" . implode("\n", $log), 'failed']);
        flash('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: /admin/guncelleme.php'); exit;
}

$history = $pdo->query(
    'SELECT * FROM ' . p() . 'updates ORDER BY updated_at DESC LIMIT 20'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Guncelleme - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= htmlspecialchars($currentVer) ?>">
</head>
<body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">

<?php $fl = getFlash(); if (!empty($fl)): ?>
<div class="alert alert-<?= $fl['type'] ?>"><?= htmlspecialchars($fl['msg']) ?></div>
<?php endif; ?>

<div class="page-actions"><h1>Sistem Guncellemesi</h1></div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">

  <!-- GitHub -->
  <div class="card">
    <div class="card-header"><div class="card-title">🔗 GitHub Otomatik Guncelleme</div></div>
    <div class="card-body">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
        <div style="flex:1;padding:14px;background:#f8f8f8;border-radius:8px;text-align:center">
          <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Aktif Surum</div>
          <div style="font-size:24px;font-weight:800;color:#1a1a2e">v<?= htmlspecialchars($currentVer) ?></div>
        </div>
        <div style="color:#ccc;font-size:20px">→</div>
        <div id="latestBox" style="flex:1;padding:14px;background:#f8f8f8;border:1px solid #eee;border-radius:8px;text-align:center">
          <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Son Surum</div>
          <div style="font-size:16px;color:#ccc">—</div>
        </div>
      </div>
      <button id="btnCheck" onclick="checkRelease()" class="btn btn-secondary" style="width:100%;justify-content:center;margin-bottom:12px">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Surum Kontrol Et
      </button>
      <div id="releaseInfo" style="display:none">
        <div id="releaseBox" style="padding:12px;background:#f8f8f8;border-radius:8px;margin-bottom:12px;font-size:12px;color:#555;line-height:1.6"></div>
        <form method="POST" id="ghUpdateForm" onsubmit="return confirm('Guncelleme uygulanacak?')">
          <input type="hidden" name="apply_github" value="1">
          <input type="hidden" name="zip_url"  id="zipUrl">
          <input type="hidden" name="version"  id="versionInput">
          <button type="submit" id="btnUpdate" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">Guncelle</button>
        </form>
      </div>
      <div id="upToDate" style="display:none" class="alert alert-success">En guncel surum kullaniliyor.</div>
      <div id="checkError" style="display:none" class="alert alert-error"></div>
      <a href="https://github.com/<?= htmlspecialchars($repoSlug) ?>/releases" target="_blank"
         class="btn btn-secondary" style="width:100%;justify-content:center;margin-top:10px">GitHub Releases</a>
    </div>
  </div>

  <!-- Manuel -->
  <div class="card">
    <div class="card-header"><div class="card-title">📦 Manuel ZIP Yukle</div></div>
    <div class="card-body">
      <p style="font-size:13px;color:#888;margin-bottom:16px;line-height:1.6">
        ZIP icindeki <code style="background:#f5f5f5;padding:1px 6px;border-radius:4px;font-size:11px">manifest.json</code>
        dosyasindan versiyon otomatik okunur.
      </p>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="apply_manual" value="1">
        <div class="form-group" style="margin-bottom:16px">
          <div class="upload-area" onclick="document.getElementById('zipFileInput').click()" style="padding:24px;cursor:pointer">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="1.5" style="margin:0 auto 10px">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <p id="zipFileName" style="font-size:13px;color:#888">Tikla veya sur-birak (update_vX.X.X.zip)</p>
          </div>
          <input type="file" id="zipFileInput" name="zip_file" accept=".zip" style="display:none" required
                 onchange="var f=this.files[0];document.getElementById('zipFileName').textContent=f?f.name+' ('+Math.round(f.size/1024)+'KB)':'Dosya sec'">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px"
                onclick="return confirm('ZIP uygulanacak, emin misiniz?')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
          </svg>
          ZIP Yukle ve Uygula
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Gecmis -->
<div class="card">
  <div class="card-header"><div class="card-title">Guncelleme Gecmisi</div></div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead><tr><th style="width:120px">Surum</th><th style="width:120px">Durum</th><th style="width:160px">Tarih</th><th>Log</th></tr></thead>
      <tbody>
        <?php foreach ($history as $i => $h): ?>
        <?php $ver = ltrim($h['version'], 'v'); ?>
        <tr>
          <td><strong>v<?= htmlspecialchars($ver) ?></strong></td>
          <td>
            <span class="badge badge-<?= $h['status'] === 'success' ? 'active' : 'passive' ?>">
              <?= $h['status'] === 'success' ? '✓ Basarili' : '✗ Basarisiz' ?>
            </span>
          </td>
          <td style="font-size:12px;color:#888;white-space:nowrap"><?= date('d.m.Y H:i', strtotime($h['updated_at'])) ?></td>
          <td>
            <?php $notes = trim($h['notes'] ?? ''); if ($notes): ?>
            <button onclick="var el=document.getElementById('log<?= $i ?>');el.style.display=el.style.display?'':'block'"
                    style="background:none;border:none;color:var(--acc);font-size:12px;cursor:pointer;font-weight:600">
              Detay ▾
            </button>
            <pre id="log<?= $i ?>" style="display:none;margin-top:8px;padding:10px;background:#f4f4f4;border-radius:6px;font-size:11px;white-space:pre-wrap;max-height:250px;overflow-y:auto;color:<?= $h['status'] === 'success' ? '#333' : '#c0392b' ?>;border-left:3px solid <?= $h['status'] === 'success' ? '#10b981' : '#c0392b' ?>;"><?= htmlspecialchars($notes) ?></pre>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($history)): ?>
        <tr><td colspan="4" style="text-align:center;color:#999;padding:32px">Gecmis kayit yok.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</main>
<script>
function checkRelease() {
  var btn = document.getElementById('btnCheck');
  btn.disabled = true; btn.textContent = 'Kontrol ediliyor...';
  ['releaseInfo','upToDate','checkError'].forEach(function(id){ document.getElementById(id).style.display='none'; });
  fetch('/admin/guncelleme.php?action=check')
    .then(function(r) { return r.json(); })
    .then(function(d) {
      btn.disabled = false;
      btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Surum Kontrol Et';
      if (d.error) {
        document.getElementById('checkError').style.display = 'block';
        document.getElementById('checkError').textContent  = d.error;
        return;
      }
      var lb = document.getElementById('latestBox');
      lb.innerHTML = '<div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Son Surum</div>'
                   + '<div style="font-size:24px;font-weight:800;color:#10b981">'+d.tag+'</div>';
      lb.style.background = 'rgba(16,185,129,.1)';
      lb.style.border     = '1px solid rgba(16,185,129,.3)';
      if (!d.is_newer) { document.getElementById('upToDate').style.display = 'block'; return; }
      var info = '<strong>' + d.name + '</strong>';
      if (d.date) info += ' <span style="color:#aaa;font-size:11px">' + d.date + '</span>';
      if (d.body) info += '<br><br>' + d.body.replace(/\n/g, '<br>');
      document.getElementById('releaseBox').innerHTML = info;
      document.getElementById('zipUrl').value      = d.zip_url;
      document.getElementById('versionInput').value = d.tag;
      document.getElementById('btnUpdate').textContent = d.tag + ' Surumune Guncelle';
      document.getElementById('releaseInfo').style.display = 'block';
    })
    .catch(function(e) {
      btn.disabled = false;
      document.getElementById('checkError').style.display  = 'block';
      document.getElementById('checkError').textContent    = 'Baglanti hatasi: ' + e.message;
    });
}
</script>
<?php require ROOT . '/admin/includes/footer.php'; ?>

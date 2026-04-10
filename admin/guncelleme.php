<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/config.php';
require_once ROOT . '/admin/includes/sidebar.php';
require_once ROOT . '/includes/functions.php';
$pdo = getDB();
$pageTitle = 'Sistem Guncellemesi';

$repoSlug    = getSetting('github_repo', defined('GITHUB_REPO') ? GITHUB_REPO : '');
$githubToken = getSetting('github_token', defined('GITHUB_TOKEN') ? GITHUB_TOKEN : '');
$currentVer  = defined('APP_VERSION') ? APP_VERSION : '1.0.0';

$latestRelease = null;
$releaseError  = '';
$updateLog     = [];

function githubGet(string $url, string $token): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => 'ParsalCMS-Updater/1.0',
        CURLOPT_HTTPHEADER     => $token ? ["Authorization: token $token"] : [],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($status !== 200 || !$body) return ['error' => "HTTP $status"];
    $data = json_decode($body, true);
    return $data ?: ['error' => 'JSON parse hatasi'];
}

// Check for latest release
if ($repoSlug) {
    $apiUrl = "https://api.github.com/repos/$repoSlug/releases/latest";
    $data   = githubGet($apiUrl, $githubToken);
    if (isset($data['error'])) {
        $releaseError = 'GitHub API hatasi: ' . $data['error'];
    } elseif (isset($data['tag_name'])) {
        $latestRelease = $data;
    } else {
        $releaseError = 'Hicbir release bulunamadi.';
    }
}

$updateMsg = '';
$updateType = '';
// APPLY UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_update'])) {
    $zipUrl  = $_POST['zip_url'] ?? '';
    $version = $_POST['version'] ?? '';

    if (!$zipUrl || !$version) {
        $updateMsg = 'Eksik parametre.'; $updateType = 'error';
    } else {
        // Download ZIP
        $tmpZip = sys_get_temp_dir() . '/parsal_update_' . time() . '.zip';
        $ch = curl_init($zipUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'ParsalCMS-Updater/1.0',
            CURLOPT_HTTPHEADER     => $githubToken ? ["Authorization: token $githubToken"] : [],
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $zipData = curl_exec($ch);
        curl_close($ch);

        if (!$zipData || strlen($zipData) < 100) {
            $updateMsg = 'ZIP indirilemedi.'; $updateType = 'error';
        } else {
            file_put_contents($tmpZip, $zipData);
            $zip = new ZipArchive();
            if ($zip->open($tmpZip) !== true) {
                $updateMsg = 'ZIP acilamadi.'; $updateType = 'error';
                unlink($tmpZip);
            } else {
                // Read manifest.json
                $manifestJson = $zip->getFromName('manifest.json');
                $files = [];
                if ($manifestJson) {
                    $manifest = json_decode($manifestJson, true);
                    $files = $manifest['files'] ?? [];
                }

                if (empty($files)) {
                    // Extract all if no manifest
                    $zip->extractTo(ROOT);
                    $updateLog[] = 'Manifest bulunamadi, tum dosyalar cikartildi.';
                } else {
                    foreach ($files as $f) {
                        $content = $zip->getFromName($f);
                        if ($content === false) { $updateLog[] = "Atl.: $f (ZIP icinde yok)"; continue; }
                        $dest = ROOT . '/' . ltrim($f, '/');
                        $dir = dirname($dest);
                        if (!is_dir($dir)) mkdir($dir, 0755, true);
                        file_put_contents($dest, $content);
                        $updateLog[] = "Guncellendi: $f";
                    }
                }
                $zip->close();
                unlink($tmpZip);

                // Update config.php version
                if (file_exists(ROOT . '/config.php')) {
                    $cfg = file_get_contents(ROOT . '/config.php');
                    $cfg = preg_replace("/define\('APP_VERSION',\s*'[^']+'\)/", "define('APP_VERSION', '$version')", $cfg);
                    file_put_contents(ROOT . '/config.php', $cfg);
                }

                // Log to DB
                $pdo->prepare('INSERT INTO ' . p() . 'updates (version, notes, status) VALUES (?,?,?)')
                    ->execute([$version, implode("\n", $updateLog), 'success']);

                $updateMsg  = "Guncelleme tamamlandi! v$version yuklendi.";
                $updateType = 'success';
            }
        }
    }
}

// Update history
$history = $pdo->query('SELECT * FROM ' . p() . 'updates ORDER BY updated_at DESC LIMIT 10')->fetchAll();
?>
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Guncelleme - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= APP_VERSION ?>"></head><body>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">
<div class="page-actions"><h1>Sistem Guncellemesi</h1></div>

<?php if ($updateMsg): ?>
<div class="alert alert-<?= $updateType === 'success' ? 'success' : 'error' ?>">
  <?= htmlspecialchars($updateMsg) ?>
</div>
<?php if (!empty($updateLog)): ?>
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><div class="card-title">Islem Logu</div></div>
  <div class="card-body" style="font-family:monospace;font-size:12px;max-height:300px;overflow-y:auto">
    <?php foreach ($updateLog as $line): ?>
    <div style="padding:3px 0;border-bottom:1px solid #f5f5f5;color:<?= strpos($line,'Atl.')!==false?'#f59e0b':'#333' ?>"><?= htmlspecialchars($line) ?></div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
  <!-- Current + Latest -->
  <div class="card">
    <div class="card-header"><div class="card-title">Surum Bilgisi</div></div>
    <div class="card-body">
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
        <div style="flex:1;padding:16px;background:#f8f8f8;border-radius:8px;text-align:center">
          <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Mevcut Surum</div>
          <div style="font-size:24px;font-weight:800;color:#1a1a2e">v<?= htmlspecialchars($currentVer) ?></div>
        </div>
        <div style="font-size:20px;color:#ccc">→</div>
        <div style="flex:1;padding:16px;border-radius:8px;text-align:center;background:<?= $latestRelease ? 'rgba(16,185,129,.1)':'#f8f8f8' ?>;border:<?= $latestRelease ? '1px solid rgba(16,185,129,.3)':'1px solid #eee' ?>">
          <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Son Surum</div>
          <?php if ($latestRelease): ?>
          <div style="font-size:24px;font-weight:800;color:#10b981"><?= htmlspecialchars($latestRelease['tag_name']) ?></div>
          <?php else: ?>
          <div style="font-size:16px;color:#ccc">?</div>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($releaseError): ?>
      <div class="alert alert-warning"><?= htmlspecialchars($releaseError) ?></div>
      <?php endif; ?>

      <?php if ($latestRelease): ?>
        <?php
        $latestTag = ltrim($latestRelease['tag_name'], 'v');
        $isNewer   = version_compare($latestTag, $currentVer, '>');
        $zipAsset  = null;
        foreach ($latestRelease['assets'] ?? [] as $asset) {
            if (str_ends_with($asset['name'], '.zip')) { $zipAsset = $asset; break; }
        }
        if (!$zipAsset) { // Fallback to zipball_url
            $zipAsset = ['browser_download_url' => $latestRelease['zipball_url'] ?? '', 'name' => 'source.zip', 'size' => 0];
        }
        ?>
        <div style="padding:16px;background:#f8f8f8;border-radius:8px;margin-bottom:16px">
          <h4 style="font-size:14px;font-weight:700;margin-bottom:8px"><?= htmlspecialchars($latestRelease['name'] ?: $latestRelease['tag_name']) ?></h4>
          <p style="font-size:13px;color:#666;line-height:1.6"><?= nl2br(htmlspecialchars(substr($latestRelease['body'] ?? '', 0, 400))) ?></p>
          <p style="font-size:11px;color:#aaa;margin-top:8px"><?= date('d.m.Y', strtotime($latestRelease['published_at'])) ?></p>
        </div>

        <?php if ($isNewer): ?>
        <form method="POST" onsubmit="return confirm('Guncelleme uygulanacak. Devam etmek istiyor musunuz?')">
          <input type="hidden" name="apply_update" value="1">
          <input type="hidden" name="zip_url" value="<?= htmlspecialchars($zipAsset['browser_download_url']) ?>">
          <input type="hidden" name="version" value="<?= htmlspecialchars($latestTag) ?>">
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            v<?= htmlspecialchars($latestTag) ?> Surumune Guncelle
          </button>
        </form>
        <?php else: ?>
        <div class="alert alert-success">✅ En guncel surum kullanilmaktadir.</div>
        <?php endif; ?>
      <?php endif; ?>

      <div style="margin-top:12px">
        <a href="<?= htmlspecialchars("https://github.com/$repoSlug/releases") ?>" target="_blank" class="btn btn-secondary" style="width:100%;justify-content:center">
          GitHub Releases Sayfasi
        </a>
      </div>
    </div>
  </div>

  <!-- History -->
  <div class="card">
    <div class="card-header"><div class="card-title">Guncelleme Gecmisi</div></div>
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Surum</th><th>Durum</th><th>Tarih</th></tr></thead>
        <tbody>
          <?php foreach ($history as $h): ?>
          <tr>
            <td><strong>v<?= htmlspecialchars($h['version']) ?></strong></td>
            <td><span class="badge badge-<?= $h['status']==='success'?'active':'passive' ?>"><?= $h['status']==='success'?'Basarili':'Basarisiz' ?></span></td>
            <td style="font-size:12px;color:#888"><?= date('d.m.Y H:i',strtotime($h['updated_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($history)): ?><tr><td colspan="3" style="text-align:center;color:#999;padding:24px">Gecmis kayit yok.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</main>
<?php require ROOT . '/admin/includes/footer.php'; ?>

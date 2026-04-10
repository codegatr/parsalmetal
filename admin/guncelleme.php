<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/admin/includes/admin_init.php';
$pageTitle = 'Guncelleme';

$currentVer  = getSetting('app_version', defined('APP_VERSION') ? APP_VERSION : '1.0.0');
$repoSlug    = getSetting('github_repo',  '');
$githubToken = getSetting('github_token', '');

/* ---------- AJAX: surum kontrol ---------- */
if (isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json; charset=utf-8');
    if (!function_exists('curl_init')) { echo json_encode(['error'=>'cURL aktif degil.']); exit; }
    if (!$repoSlug)                    { echo json_encode(['error'=>'GitHub repo ayarlanmamis.']); exit; }
    $ch = curl_init("https://api.github.com/repos/$repoSlug/releases/latest");
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true, CURLOPT_USERAGENT=>'ParsalCMS/1.0',
        CURLOPT_TIMEOUT=>10, CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_HTTPHEADER=>$githubToken?["Authorization: token $githubToken","Accept: application/vnd.github.v3+json"]:["Accept: application/vnd.github.v3+json"],
    ]);
    $body=curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); $cerr=curl_error($ch); curl_close($ch);
    if (!$body||$code!==200){echo json_encode(['error'=>"GitHub yanit vermedi (HTTP $code). $cerr"]);exit;}
    $data=json_decode($body,true);
    if (!$data||!isset($data['tag_name'])){echo json_encode(['error'=>'Release bulunamadi.']);exit;}
    $zipUrl='';
    foreach((array)($data['assets']??[]) as $a){if(substr($a['name'],-4)==='.zip'){$zipUrl=$a['browser_download_url'];break;}}
    if(!$zipUrl) $zipUrl=$data['zipball_url']??'';
    echo json_encode([
        'tag'=>$data['tag_name'],'name'=>$data['name']??$data['tag_name'],
        'body'=>substr($data['body']??'',0,500),
        'date'=>isset($data['published_at'])?date('d.m.Y',strtotime($data['published_at'])):'',
        'zip_url'=>$zipUrl,'current'=>$currentVer,
        'is_newer'=>version_compare(ltrim($data['tag_name'],'v'),$currentVer,'>'),
    ]);
    exit;
}

/* ---------- Ortak: ZIP uygula ---------- */
function applyZip(string $tmpZip, string $version, $pdo): array {
    $log=[];
    if(!class_exists('ZipArchive')) throw new Exception('ZipArchive PHP eklentisi aktif degil. Hosting destek ekibinden etkinlestirmelerini isteyin.');
    $zip=new ZipArchive();
    $res=$zip->open($tmpZip);
    if($res!==true) throw new Exception('ZIP acilamadi. Kod:'.$res.' - Dosya bozuk olabilir.');

    // manifest.json bul
    $mJson=false;
    foreach(['manifest.json','update/manifest.json'] as $mp){ $t=$zip->getFromName($mp); if($t!==false){$mJson=$t;break;} }
    if($mJson===false){
        for($i=0;$i<min(20,$zip->numFiles);$i++){
            $n=$zip->getNameIndex($i);
            if($n!==false&&substr($n,-13)==='manifest.json'){$mJson=$zip->getFromIndex($i);break;}
        }
    }

    $files=[]; $migrations=[];
    if($mJson){
        $m=json_decode($mJson,true);
        $files=$m['files']??[]; $migrations=$m['migrations']??[];
        $log[]=count($files).' dosya, '.count($migrations).' migration.';
    } else {
        $log[]='Manifest bulunamadi - extractAll.';
    }

    // GitHub zipball prefix
    $prefix='';
    if(!empty($files)){
        $first=$zip->getNameIndex(0);
        if($first!==false&&strpos($first,'/')!==false){
            $p=explode('/',$first)[0].'/';
            if($zip->getFromName($files[0])===false&&$zip->getFromName($p.$files[0])!==false){$prefix=$p;$log[]="Prefix:$prefix";}
        }
    }

    $ok=0; $fail=0;
    if(empty($files)){
        $zip->extractTo(ROOT); $log[]='Tum dosyalar cikartildi.';
    } else {
        foreach($files as $f){
            $fc=$zip->getFromName($f);
            if($fc===false) $fc=$zip->getFromName($prefix.$f);
            if($fc===false){$log[]="Atla:$f"; $fail++; continue;}
            $dest=ROOT.'/'.ltrim($f,'/');
            $dir=dirname($dest);
            if(!is_dir($dir)) mkdir($dir,0755,true);
            if(!is_writable($dir)&&!is_writable($dest)){$log[]="IZIN HATASI:$f"; $fail++; continue;}
            file_put_contents($dest,$fc)?($ok++&&$log[]="OK:$f"):($fail++&&$log[]="YAZMA HATASI:$f");
        }
    }
    $zip->close();
    $log[]="Sonuc: $ok dosya guncellendi, $fail atland/hata.";

    // SQL migrations
    foreach($migrations as $sf){
        $sp=ROOT.'/update/'.$sf;
        if(file_exists($sp)){try{$pdo->exec(file_get_contents($sp));$log[]="Migration OK:$sf";}catch(Exception $e){$log[]="Migration HATA:$sf - ".$e->getMessage();}}
        else{$log[]="Migration yok:$sf";}
    }

    // Versiyon: once DB, sonra config.php (izin varsa)
    $ver=ltrim($version,'v');
    try {
        $pdo->prepare("INSERT INTO ".DB_PREFIX."settings (setting_key,setting_value) VALUES ('app_version',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute([$ver]);
        $log[]="Versiyon DB'ye kaydedildi: v$ver";
    } catch(Exception $e){ $log[]="DB versiyon hatasi:".$e->getMessage(); }

    if(file_exists(ROOT.'/config.php')){
        if(is_writable(ROOT.'/config.php')){
            $cfg=file_get_contents(ROOT.'/config.php');
            $new=preg_replace("/define\('APP_VERSION',\s*'[^']*'\)/","define('APP_VERSION','$ver')",$cfg);
            if($new&&$new!==$cfg&&file_put_contents(ROOT.'/config.php',$new)!==false){
                $log[]="config.php guncellendi: v$ver";
            } else {
                $log[]="config.php yazilamadi (regex veya izin sorunu). Manuel ekle: define('APP_VERSION','$ver')";
            }
        } else {
            $log[]="config.php yazma izni YOK. Versiyon DB'de guncellendi. Manuel degistir: define('APP_VERSION','$ver')";
        }
    }
    return $log;
}

/* ---------- POST: GitHub ile indir ---------- */
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['apply_github'])){
    $zipUrl=trim($_POST['zip_url']??''); $version=trim($_POST['version']??''); $log=[];
    try {
        if(!$zipUrl||!$version) throw new Exception('Eksik parametre.');
        if(!function_exists('curl_init')) throw new Exception('cURL aktif degil.');
        $tmpZip=sys_get_temp_dir().'/parsal_'.time().'.zip';
        $fp=fopen($tmpZip,'wb');
        $ch=curl_init($zipUrl);
        curl_setopt_array($ch,[CURLOPT_FILE=>$fp,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_USERAGENT=>'ParsalCMS/1.0',CURLOPT_TIMEOUT=>120,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_HTTPHEADER=>$githubToken?["Authorization: token $githubToken","Accept: application/octet-stream"]:["Accept: application/octet-stream"]]);
        curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); $cerr=curl_error($ch); curl_close($ch); fclose($fp);
        $size=file_exists($tmpZip)?filesize($tmpZip):0;
        if($size<200){if(file_exists($tmpZip))unlink($tmpZip);throw new Exception("ZIP indirilemedi (boyut:{$size}B, HTTP:{$code}). $cerr. Manuel ZIP'i kullanin.");}
        $log[]='ZIP indirildi: '.round($size/1024).'KB';
        $log=array_merge($log,applyZip($tmpZip,$version,$pdo));
        unlink($tmpZip);
        $pdo->prepare('INSERT INTO '.p().'updates (version,notes,status) VALUES (?,?,?)')->execute([$version,implode("\n",$log),'success']);
        flash('success',ltrim($version,'v').' basariyla yuklendi!');
    } catch(Exception $e){
        if(isset($tmpZip)&&file_exists($tmpZip))unlink($tmpZip);
        $pdo->prepare('INSERT INTO '.p().'updates (version,notes,status) VALUES (?,?,?)')->execute([$version??'?',$e->getMessage()."\n".implode("\n",$log),'failed']);
        flash('error','Hata: '.$e->getMessage());
    }
    header('Location: /admin/guncelleme.php'); exit;
}

/* ---------- POST: Manuel ZIP ---------- */
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['apply_manual'])){
    $log=[];
    try {
        if(!class_exists('ZipArchive')) throw new Exception('ZipArchive aktif degil.');
        if(empty($_FILES['zip_file']['tmp_name'])) throw new Exception('ZIP secilmedi.');
        if($_FILES['zip_file']['error']!==UPLOAD_ERR_OK) throw new Exception('Yukleme hatasi kodu:'.$_FILES['zip_file']['error']);
        $size=$_FILES['zip_file']['size'];
        if($size<100) throw new Exception("ZIP cok kucuk ({$size}B).");
        $tmpZip=sys_get_temp_dir().'/parsal_man_'.time().'.zip';
        move_uploaded_file($_FILES['zip_file']['tmp_name'],$tmpZip);
        $log[]='ZIP yuklendi: '.round($size/1024).'KB';

        // Versiyonu manifest'ten oku
        $version='';
        $zv=new ZipArchive();
        if($zv->open($tmpZip)===true){
            foreach(['manifest.json','update/manifest.json'] as $mp){
                $mj=$zv->getFromName($mp);
                if($mj!==false){$md=json_decode($mj,true);if(!empty($md['version'])){$version=$md['version'];break;}}
            }
            $zv->close();
        }
        if(!$version) throw new Exception('Versiyon belirlenemedi. manifest.json icinde "version" alani eksik veya ZIP bozuk.');
        $log[]='manifest.json versiyonu: v'.$version;

        $log=array_merge($log,applyZip($tmpZip,$version,$pdo));
        unlink($tmpZip);
        $pdo->prepare('INSERT INTO '.p().'updates (version,notes,status) VALUES (?,?,?)')->execute([$version,implode("\n",$log),'success']);
        flash('success',"v$version basariyla yuklendi!");
    } catch(Exception $e){
        if(isset($tmpZip)&&file_exists($tmpZip))unlink($tmpZip);
        $ver=$version??'?';
        $pdo->prepare('INSERT INTO '.p().'updates (version,notes,status) VALUES (?,?,?)')->execute([$ver,$e->getMessage()."\n".implode("\n",$log),'failed']);
        flash('error','Hata: '.$e->getMessage());
    }
    header('Location: /admin/guncelleme.php'); exit;
}

$history=$pdo->query('SELECT * FROM '.p().'updates ORDER BY updated_at DESC LIMIT 20')->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Guncelleme - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= $currentVer ?>">
</head>
<body>
<?php require ROOT . '/admin/includes/sidebar.php'; ?>
<?php require ROOT . '/admin/includes/header.php'; ?>
<main class="admin-main">

<?php $fl=getFlash(); if(!empty($fl)): ?>
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
          <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Aktif</div>
          <div style="font-size:22px;font-weight:800;color:#1a1a2e">v<?= htmlspecialchars($currentVer) ?></div>
        </div>
        <div style="color:#ccc;font-size:20px">→</div>
        <div id="latestBox" style="flex:1;padding:14px;background:#f8f8f8;border:1px solid #eee;border-radius:8px;text-align:center">
          <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Son</div>
          <div style="font-size:16px;color:#ccc">—</div>
        </div>
      </div>
      <button id="btnCheck" onclick="checkRelease()" class="btn btn-secondary" style="width:100%;justify-content:center;margin-bottom:12px">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Surum Kontrol Et
      </button>
      <div id="releaseInfo" style="display:none">
        <div id="releaseBox" style="padding:12px;background:#f8f8f8;border-radius:8px;margin-bottom:12px;font-size:12px;color:#555;line-height:1.6"></div>
        <form method="POST" onsubmit="return confirm('Guncelleme uygulanacak?')">
          <input type="hidden" name="apply_github" value="1">
          <input type="hidden" name="zip_url" id="zipUrl">
          <input type="hidden" name="version" id="version">
          <button type="submit" id="btnUpdate" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">Guncelle</button>
        </form>
      </div>
      <div id="upToDate" style="display:none" class="alert alert-success">En guncel surum kullaniliyor.</div>
      <div id="checkError" style="display:none" class="alert alert-error"></div>
      <a href="https://github.com/<?= htmlspecialchars($repoSlug) ?>/releases" target="_blank" class="btn btn-secondary" style="width:100%;justify-content:center;margin-top:10px">GitHub Releases</a>
    </div>
  </div>

  <!-- Manuel -->
  <div class="card">
    <div class="card-header"><div class="card-title">📦 Manuel ZIP Yukle</div></div>
    <div class="card-body">
      <p style="font-size:13px;color:#888;margin-bottom:16px;line-height:1.6">
        ZIP icindeki <code style="background:#f5f5f5;padding:1px 5px;border-radius:4px;font-size:11px">manifest.json</code> dosyasindan versiyon otomatik okunur. Ayri bir numara girmenize gerek yok.
      </p>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="apply_manual" value="1">
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">ZIP Dosyasi</label>
          <div class="upload-area" onclick="document.getElementById('zipFileInput').click()" style="padding:20px;cursor:pointer">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="1.5" style="margin:0 auto 8px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <p id="zipFileName" style="font-size:13px;color:#888">Tikla veya sur-birak (update_vX.X.X.zip)</p>
          </div>
          <input type="file" id="zipFileInput" name="zip_file" accept=".zip" style="display:none" required
                 onchange="document.getElementById('zipFileName').textContent=this.files[0]?this.files[0].name+' ('+Math.round(this.files[0].size/1024)+'KB)':'Dosya sec'">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px"
                onclick="return confirm('ZIP uygulanacak, emin misiniz?')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          ZIP Yukle ve Uygula
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Gecmis + Log -->
<div class="card">
  <div class="card-header"><div class="card-title">Guncelleme Gecmisi</div></div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead><tr><th>Surum</th><th>Durum</th><th>Tarih</th><th>Log</th></tr></thead>
      <tbody>
        <?php foreach($history as $i=>$h): ?>
        <tr>
          <td><strong>v<?= htmlspecialchars($h['version']) ?></strong></td>
          <td><span class="badge badge-<?= $h['status']==='success'?'active':'passive' ?>"><?= $h['status']==='success'?'Basarili':'Basarisiz' ?></span></td>
          <td style="font-size:12px;color:#888;white-space:nowrap"><?= date('d.m.Y H:i',strtotime($h['updated_at'])) ?></td>
          <td>
            <?php $notes=trim($h['notes']??''); if($notes): ?>
            <button onclick="toggleLog(<?= $i ?>)" style="background:none;border:none;color:#888;font-size:12px;cursor:pointer;text-decoration:underline">Detay</button>
            <div id="log<?= $i ?>" style="display:none;margin-top:8px;padding:10px;background:#f8f8f8;border-radius:6px;font-family:monospace;font-size:11px;white-space:pre-wrap;max-height:200px;overflow-y:auto;color:<?= $h['status']==='success'?'#333':'#c0392b' ?>"><?= htmlspecialchars($notes) ?></div>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($history)): ?>
        <tr><td colspan="4" style="text-align:center;color:#999;padding:32px">Gecmis kayit yok.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</main>
<script>
function checkRelease(){
  var btn=document.getElementById('btnCheck');
  btn.disabled=true;btn.textContent='Kontrol ediliyor...';
  ['releaseInfo','upToDate','checkError'].forEach(function(id){document.getElementById(id).style.display='none';});
  fetch('/admin/guncelleme.php?action=check')
    .then(function(r){return r.json();})
    .then(function(d){
      btn.disabled=false;btn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Surum Kontrol Et';
      if(d.error){document.getElementById('checkError').style.display='block';document.getElementById('checkError').textContent=d.error;return;}
      var lb=document.getElementById('latestBox');
      lb.innerHTML='<div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Son</div><div style="font-size:22px;font-weight:800;color:#10b981">'+d.tag+'</div>';
      lb.style.background='rgba(16,185,129,.1)';lb.style.border='1px solid rgba(16,185,129,.3)';
      if(!d.is_newer){document.getElementById('upToDate').style.display='block';return;}
      document.getElementById('releaseBox').innerHTML='<strong>'+d.name+'</strong>'+(d.date?' <span style="color:#aaa;font-size:11px">'+d.date+'</span>':'')+(d.body?'<br><br>'+d.body.replace(/\n/g,'<br>'):'');
      document.getElementById('zipUrl').value=d.zip_url;
      document.getElementById('version').value=d.tag;
      document.getElementById('btnUpdate').textContent=d.tag+' Surumune Guncelle';
      document.getElementById('releaseInfo').style.display='block';
    })
    .catch(function(e){btn.disabled=false;document.getElementById('checkError').style.display='block';document.getElementById('checkError').textContent='Baglanti hatasi: '+e.message;});
}
function toggleLog(i){
  var el=document.getElementById('log'+i);
  el.style.display=el.style.display==='none'?'block':'none';
}
</script>
<?php require ROOT . '/admin/includes/footer.php'; ?>

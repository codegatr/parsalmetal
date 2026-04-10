<?php
session_start();
define('INSTALL_PATH', dirname(__DIR__));
define('INSTALL_LOCK', INSTALL_PATH . '/config.php');

// Eğer kurulum tamamlandıysa engelle
if (file_exists(INSTALL_LOCK) && !isset($_GET['force'])) {
    die('<div style="font-family:sans-serif;text-align:center;margin-top:80px;"><h2>Kurulum Tamamlandı</h2><p>config.php dosyası mevcut. Yeniden kurulum için dosyayı silin.</p></div>');
}

$step = (int)($_GET['step'] ?? 1);
if ($step < 1 || $step > 5) $step = 1;

// POST işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        // DB bağlantı testi
        $host = trim($_POST['db_host'] ?? '');
        $name = trim($_POST['db_name'] ?? '');
        $user = trim($_POST['db_user'] ?? '');
        $pass = trim($_POST['db_pass'] ?? '');
        $prefix = trim($_POST['db_prefix'] ?? 'prs_');
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $_SESSION['install_db'] = compact('host','name','user','pass','prefix');
            // Tabloları oluştur
            $sql = file_get_contents(__DIR__ . '/install.sql');
            $sql = str_replace('prs_', $prefix, $sql);
            // SQL ifadelerini satır satır ayır (heredoc içinde noktalı virgül riski yok)
            $statements = preg_split('/;\s*\n/', $sql, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($statements as $q) {
                $q = trim($q);
                if ($q && !preg_match('/^\s*--/', $q)) {
                    $pdo->exec($q);
                }
            }
            header('Location: ?step=3'); exit;
        } catch (PDOException $e) {
            $error = 'Veritabanı bağlantısı başarısız: ' . $e->getMessage();
        }
    } elseif ($step === 3) {
        // Admin hesabı
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $fullname = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        if ($username && $password && strlen($password) >= 6) {
            $_SESSION['install_admin'] = compact('username','password','fullname','email');
            header('Location: ?step=4'); exit;
        } else {
            $error = 'Kullanıcı adı ve en az 6 haneli şifre gereklidir.';
        }
    } elseif ($step === 4) {
        // Site ayarları
        $_SESSION['install_site'] = [
            'site_title'   => trim($_POST['site_title'] ?? 'Parsal Metal Alüminyum'),
            'site_slogan'  => trim($_POST['site_slogan'] ?? ''),
            'site_email'   => trim($_POST['site_email'] ?? ''),
            'site_phone'   => trim($_POST['site_phone'] ?? ''),
            'site_address' => trim($_POST['site_address'] ?? ''),
            'site_url'     => rtrim(trim($_POST['site_url'] ?? ''), '/'),
            'github_repo'  => trim($_POST['github_repo'] ?? ''),
            'github_token' => trim($_POST['github_token'] ?? ''),
        ];
        // config.php yaz
        $db   = $_SESSION['install_db'];
        $site = $_SESSION['install_site'];
        $adm  = $_SESSION['install_admin'];
        $cfg  = "<?php\n";
        $cfg .= "define('DB_HOST', '" . addslashes($db['host']) . "');\n";
        $cfg .= "define('DB_NAME', '" . addslashes($db['name']) . "');\n";
        $cfg .= "define('DB_USER', '" . addslashes($db['user']) . "');\n";
        $cfg .= "define('DB_PASS', '" . addslashes($db['pass']) . "');\n";
        $cfg .= "define('DB_PREFIX', '" . addslashes($db['prefix']) . "');\n";
        $cfg .= "define('SITE_URL', '" . addslashes($site['site_url']) . "');\n";
        $cfg .= "define('GITHUB_REPO', '" . addslashes($site['github_repo']) . "');\n";
        $cfg .= "define('GITHUB_TOKEN', '" . addslashes($site['github_token']) . "');\n";
        $cfg .= "define('APP_VERSION', '1.0.0');\n";
        $cfg .= "define('APP_NAME', 'ParsalCMS');\n";
        file_put_contents(INSTALL_PATH . '/config.php', $cfg);
        // Admin ve ayarları DB'ye kaydet
        require_once INSTALL_PATH . '/includes/db.php';
        $pdo = getDB();
        $p = $db['prefix'];
        // Admin user
        $hash = password_hash($adm['password'], PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO {$p}users (username, password, full_name, email) VALUES (?,?,?,?)")
            ->execute([$adm['username'], $hash, $adm['fullname'], $adm['email']]);
        // Settings
        $settings = [
            'site_title'   => $site['site_title'],
            'site_slogan'  => $site['site_slogan'],
            'site_email'   => $site['site_email'],
            'site_phone'   => $site['site_phone'],
            'site_address' => $site['site_address'],
            'site_url'     => $site['site_url'],
            'github_repo'  => $site['github_repo'],
            'github_token' => $site['github_token'],
            'meta_title'   => $site['site_title'] . ' - Metal ve Alüminyum Çözümleri',
            'meta_description' => $site['site_title'] . ' - Profesyonel metal ve alüminyum imalat hizmetleri.',
            'footer_text'  => '© ' . date('Y') . ' ' . $site['site_title'] . '. Tüm hakları saklıdır.',
            'cookie_bar'   => '1',
        ];
        $stmt = $pdo->prepare("INSERT INTO {$p}settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        foreach ($settings as $k => $v) $stmt->execute([$k, $v]);
        header('Location: ?step=5'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Parsal CMS - Kurulum</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',sans-serif;background:#0f0f0f;color:#fff;min-height:100vh;display:flex;align-items:center;justify-content:center}
.wrap{width:100%;max-width:600px;padding:20px}
.logo{text-align:center;margin-bottom:30px}
.logo h1{font-size:28px;font-weight:700;letter-spacing:2px}
.logo h1 span{color:#c0392b}
.logo p{color:#888;margin-top:5px}
.box{background:#1a1a1a;border:1px solid #2a2a2a;border-radius:12px;padding:40px;margin-bottom:20px}
.steps{display:flex;gap:8px;margin-bottom:30px;justify-content:center}
.step-dot{width:36px;height:36px;border-radius:50%;background:#2a2a2a;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:#666;border:2px solid #333}
.step-dot.active{background:#c0392b;border-color:#c0392b;color:#fff}
.step-dot.done{background:#2d5a2d;border-color:#2d5a2d;color:#6fcf97}
h2{font-size:20px;font-weight:600;margin-bottom:8px}
p.sub{color:#888;font-size:14px;margin-bottom:24px}
.form-group{margin-bottom:18px}
label{display:block;font-size:13px;color:#aaa;margin-bottom:6px;font-weight:500}
input[type=text],input[type=email],input[type=password],input[type=url]{width:100%;padding:12px 14px;background:#111;border:1px solid #333;border-radius:8px;color:#fff;font-size:14px;outline:none;transition:border .2s}
input:focus{border-color:#c0392b}
.btn{display:block;width:100%;padding:14px;background:#c0392b;color:#fff;font-size:15px;font-weight:600;border:none;border-radius:8px;cursor:pointer;text-align:center;transition:background .2s;letter-spacing:.5px}
.btn:hover{background:#a93226}
.error{background:#3d1515;border:1px solid #c0392b;border-radius:8px;padding:12px 16px;color:#e74c3c;font-size:14px;margin-bottom:20px}
.success-icon{font-size:60px;text-align:center;margin-bottom:20px}
.check-item{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #222;font-size:14px}
.check-item:last-child{border-bottom:none}
.ok{color:#6fcf97}
.fail{color:#e74c3c}
.check-label{flex:1;color:#ccc}
.row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:480px){.row{grid-template-columns:1fr}}
a.link{color:#c0392b;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo">
    <h1>PARSAL <span>CMS</span></h1>
    <p>Kurulum Sihirbazı</p>
  </div>
  <div class="box">
    <div class="steps">
      <?php
      $labels = ['Gereksinimler','Veritabanı','Admin','Ayarlar','Tamamlandı'];
      for($i=1;$i<=5;$i++){
        $cls = ($i < $step) ? 'done' : (($i === $step) ? 'active' : '');
        echo "<div class='step-dot $cls' title='{$labels[$i-1]}'>".($i < $step ? '✓' : $i)."</div>";
      }
      ?>
    </div>

    <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
    <h2>Sistem Gereksinimleri</h2>
    <p class="sub">Kurulum öncesi sistem kontrolü yapılıyor.</p>
    <?php
    $checks = [
        'PHP 8.0+' => version_compare(PHP_VERSION,'8.0','>='),
        'PDO MySQL' => extension_loaded('pdo_mysql'),
        'cURL'      => extension_loaded('curl'),
        'JSON'      => extension_loaded('json'),
        'Mbstring'  => extension_loaded('mbstring'),
        'OpenSSL'   => extension_loaded('openssl'),
        'Config yazılabilir' => is_writable(INSTALL_PATH),
        'Uploads yazılabilir' => is_writable(INSTALL_PATH . '/assets/uploads'),
    ];
    $allOk = !in_array(false, $checks);
    ?>
    <?php foreach ($checks as $label => $ok): ?>
    <div class="check-item">
      <span class="check-label"><?= $label ?></span>
      <span class="<?= $ok?'ok':'fail' ?>"><?= $ok ? '✓ OK' : '✗ Hata' ?></span>
    </div>
    <?php endforeach; ?>
    <br>
    <?php if ($allOk): ?>
    <a href="?step=2" class="btn">Devam Et →</a>
    <?php else: ?>
    <div class="error">Bazı gereksinimler karşılanmıyor. Sunucu ayarlarını kontrol edin.</div>
    <?php endif; ?>

    <?php elseif ($step === 2): ?>
    <h2>Veritabanı Ayarları</h2>
    <p class="sub">MySQL bağlantı bilgilerini girin.</p>
    <form method="POST">
      <div class="row">
        <div class="form-group">
          <label>Sunucu (Host)</label>
          <input type="text" name="db_host" value="localhost" required>
        </div>
        <div class="form-group">
          <label>Veritabanı Adı</label>
          <input type="text" name="db_name" placeholder="parsal_db" required>
        </div>
      </div>
      <div class="row">
        <div class="form-group">
          <label>Kullanıcı Adı</label>
          <input type="text" name="db_user" required>
        </div>
        <div class="form-group">
          <label>Şifre</label>
          <input type="password" name="db_pass">
        </div>
      </div>
      <div class="form-group">
        <label>Tablo Öneki</label>
        <input type="text" name="db_prefix" value="prs_">
      </div>
      <button class="btn" type="submit">Bağlan ve Tabloları Oluştur →</button>
    </form>

    <?php elseif ($step === 3): ?>
    <h2>Admin Hesabı</h2>
    <p class="sub">Yönetim paneli giriş bilgilerini belirleyin.</p>
    <form method="POST">
      <div class="form-group">
        <label>Ad Soyad</label>
        <input type="text" name="fullname" placeholder="Yönetici Adı">
      </div>
      <div class="row">
        <div class="form-group">
          <label>Kullanıcı Adı</label>
          <input type="text" name="username" required>
        </div>
        <div class="form-group">
          <label>E-posta</label>
          <input type="email" name="email">
        </div>
      </div>
      <div class="form-group">
        <label>Şifre (min. 6 karakter)</label>
        <input type="password" name="password" required>
      </div>
      <button class="btn" type="submit">Devam Et →</button>
    </form>

    <?php elseif ($step === 4): ?>
    <h2>Site Ayarları</h2>
    <p class="sub">Temel site bilgilerini girin. Daha sonra admin panelinden değiştirebilirsiniz.</p>
    <form method="POST">
      <div class="form-group">
        <label>Site Başlığı</label>
        <input type="text" name="site_title" value="Parsal Metal Alüminyum" required>
      </div>
      <div class="form-group">
        <label>Slogan</label>
        <input type="text" name="site_slogan" value="Metalin Gücü, Alüminyumun Zarafeti">
      </div>
      <div class="form-group">
        <label>Site URL</label>
        <input type="url" name="site_url" value="https://parsal.com.tr" required>
      </div>
      <div class="row">
        <div class="form-group">
          <label>E-posta</label>
          <input type="email" name="site_email" placeholder="info@parsal.com.tr">
        </div>
        <div class="form-group">
          <label>Telefon</label>
          <input type="text" name="site_phone" placeholder="+90 xxx xxx xx xx">
        </div>
      </div>
      <div class="form-group">
        <label>Adres</label>
        <input type="text" name="site_address" placeholder="Firma adresi">
      </div>
      <div class="form-group">
        <label>GitHub Repo (güncelleme için)</label>
        <input type="text" name="github_repo" value="codegatr/parsalmetal">
      </div>
      <div class="form-group">
        <label>GitHub Token</label>
        <input type="text" name="github_token" placeholder="ghp_...">
      </div>
      <button class="btn" type="submit">Kurulumu Tamamla →</button>
    </form>

    <?php elseif ($step === 5): ?>
    <div class="success-icon">🎉</div>
    <h2 style="text-align:center">Kurulum Tamamlandı!</h2>
    <p class="sub" style="text-align:center">Parsal CMS başarıyla kuruldu.</p>
    <br>
    <div class="check-item"><span class="check-label">config.php oluşturuldu</span><span class="ok">✓</span></div>
    <div class="check-item"><span class="check-label">Veritabanı tabloları kuruldu</span><span class="ok">✓</span></div>
    <div class="check-item"><span class="check-label">Admin hesabı oluşturuldu</span><span class="ok">✓</span></div>
    <br>
    <div class="error" style="background:#1a2a1a;border-color:#2d5a2d;color:#6fcf97;">
      ⚠️ Güvenlik için <strong>/install/</strong> klasörünü sunucudan silin veya erişimi engelleyin!
    </div>
    <br>
    <a href="../admin/" class="btn">Admin Paneline Git →</a>
    <?php endif; ?>
  </div>
  <p style="text-align:center;color:#444;font-size:12px">Tasarım ve Geliştirme: <strong style="color:#666">CODEGA</strong></p>
</div>
</body>
</html>

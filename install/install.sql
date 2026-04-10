CREATE TABLE IF NOT EXISTS `prs_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` LONGTEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255),
  `email` VARCHAR(255),
  `role` ENUM('super','admin','editor') DEFAULT 'admin',
  `last_login` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_slider` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255),
  `subtitle` TEXT,
  `button_text` VARCHAR(100),
  `button_url` VARCHAR(255),
  `image` VARCHAR(255),
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE,
  `description` TEXT,
  `image` VARCHAR(255),
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT DEFAULT 0,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE,
  `short_desc` TEXT,
  `description` LONGTEXT,
  `image` VARCHAR(255),
  `gallery` TEXT,
  `specs` TEXT,
  `sort_order` INT DEFAULT 0,
  `is_featured` TINYINT DEFAULT 0,
  `is_active` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE,
  `short_desc` TEXT,
  `description` LONGTEXT,
  `icon` VARCHAR(100),
  `image` VARCHAR(255),
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `title` VARCHAR(255),
  `content` LONGTEXT,
  `meta_title` VARCHAR(255),
  `meta_description` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_quotes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `company` VARCHAR(255),
  `email` VARCHAR(255),
  `phone` VARCHAR(50),
  `product` VARCHAR(255),
  `message` TEXT,
  `status` ENUM('new','read','replied','closed') DEFAULT 'new',
  `ip_address` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255),
  `email` VARCHAR(255),
  `phone` VARCHAR(50),
  `subject` VARCHAR(255),
  `message` TEXT,
  `is_read` TINYINT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_references` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255),
  `logo` VARCHAR(255),
  `url` VARCHAR(255),
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_stats` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `label` VARCHAR(100),
  `value` VARCHAR(50),
  `icon` VARCHAR(100),
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_certificates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255),
  `image` VARCHAR(255),
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prs_updates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20),
  `notes` TEXT,
  `status` ENUM('success','failed') DEFAULT 'success',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `prs_slider` (title, subtitle, button_text, button_url, image, sort_order) VALUES
('Metalin Gücü,<br>Alüminyumun Zarafeti', 'Yüksek kaliteli metal ve alüminyum çözümleriyle projelerinize değer katıyoruz.', 'Ürünlerimizi Keşfedin', '/urunler', '', 1),
('Güvenilir Üretim,<br>Kaliteli Malzeme', 'Onlarca yıllık tecrübemizle sektörün en güvenilir tedarikçisiyiz.', 'Teklif Alın', '/teklif', '', 2),
('Her Projeye<br>Özel Çözümler', 'Alüminyum profil, sac, boru ve daha fazlası için doğru adrestesiniz.', 'Bizimle İletişime Geçin', '/iletisim', '', 3);

INSERT IGNORE INTO `prs_stats` (label, value, icon, sort_order) VALUES
('Yıllık Deneyim', '25+', 'calendar', 1),
('Tamamlanan Proje', '1500+', 'check-circle', 2),
('Mutlu Müşteri', '500+', 'users', 3),
('Ülke Çapında Hizmet', '81', 'map-pin', 4);

INSERT IGNORE INTO `prs_pages` (slug, title, content, meta_title, meta_description) VALUES
('hakkimizda', 'Hakkımızda', '<h2>Parsal Metal Alüminyum Hakkında</h2><p>1999 yılından bu yana metal ve alüminyum sektöründe faaliyet gösteren firmamız, müşterilerine yüksek kaliteli ürün ve hizmetler sunmaktadır.</p><p>Deneyimli kadromuz ve modern üretim tesislerimizle her ölçekteki projeye çözüm üretiyoruz.</p>', 'Hakkımızda - Parsal Metal Alüminyum', 'Parsal Metal Alüminyum hakkında bilgi edinin.'),
('kvkk', 'KVKK Aydınlatma Metni', '<h2>Kişisel Verilerin Korunması Kanunu Aydınlatma Metni</h2><p><strong>VERİ SORUMLUSU</strong></p><p>Parsal Metal Alüminyum ("Şirket" veya "Parsal"), 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca veri sorumlusu sıfatıyla kişisel verilerinizi işlemektedir.</p><h3>1. Kişisel Verilerin İşlenme Amacı</h3><p>Kişisel verileriniz; teklif süreçlerinin yürütülmesi, müşteri ilişkilerinin yönetimi, hizmet sunumu, iletişim faaliyetlerinin yürütülmesi ve yasal yükümlülüklerin yerine getirilmesi amaçlarıyla işlenmektedir.</p><h3>2. Kişisel Verilerin Aktarılması</h3><p>Toplanan kişisel verileriniz, hizmet sunumu amacıyla iş ortaklarımız ve yasal mercilerle paylaşılabilir.</p><h3>3. Haklarınız</h3><p>KVKK kapsamında; kişisel verilerinizin işlenip işlenmediğini öğrenme, işlendiyse buna ilişkin bilgi talep etme, işleme amacını ve amacına uygun kullanılıp kullanılmadığını öğrenme, yurt içinde veya yurt dışında kişisel verilerinizin aktarıldığı üçüncü kişileri bilme, eksik veya yanlış işlenmiş olması hâlinde bunların düzeltilmesini isteme, silinmesini veya yok edilmesini isteme haklarına sahipsiniz.</p><h3>4. İletişim</h3><p>Haklarınızı kullanmak için info@parsal.com.tr adresine yazılı başvuruda bulunabilirsiniz.</p>', 'KVKK Aydınlatma Metni - Parsal Metal', 'KVKK kapsamında kişisel verilerinizin işlenmesine ilişkin aydınlatma metni.'),
('gizlilik', 'Gizlilik Politikası', '<h2>Gizlilik Politikası</h2><p>Bu gizlilik politikası, Parsal Metal Alüminyum web sitesini ziyaret ettiğinizde toplanan bilgilerin nasıl kullanıldığını açıklamaktadır.</p><h3>Toplanan Bilgiler</h3><p>İletişim formları aracılığıyla ad, e-posta, telefon numarası gibi kişisel bilgileriniz toplanabilir. Bu bilgiler yalnızca sizinle iletişim kurmak amacıyla kullanılır.</p><h3>Çerezler</h3><p>Web sitemiz, kullanıcı deneyimini iyileştirmek amacıyla çerezler kullanmaktadır. Tarayıcı ayarlarınızdan çerezleri devre dışı bırakabilirsiniz.</p><h3>Üçüncü Taraflar</h3><p>Kişisel verileriniz, açık rızanız olmaksızın üçüncü taraflarla paylaşılmaz.</p><h3>Güvenlik</h3><p>Verilerinizi korumak için gerekli teknik ve idari güvenlik önlemleri alınmaktadır.</p>', 'Gizlilik Politikası - Parsal Metal', 'Gizlilik politikamız hakkında bilgi edinin.'),
('cerez', 'Çerez Politikası', '<h2>Çerez Politikası</h2><p>Bu politika, parsal.com.tr web sitesinde kullanılan çerezleri açıklamaktadır.</p><h3>Çerez Nedir?</h3><p>Çerezler, web sitelerinin tarayıcınızda depoladığı küçük metin dosyalarıdır. Oturumunuzu hatırlamak ve deneyiminizi kişiselleştirmek amacıyla kullanılır.</p><h3>Kullandığımız Çerezler</h3><ul><li><strong>Zorunlu Çerezler:</strong> Sitenin temel işlevlerinin çalışması için gereklidir.</li><li><strong>Analitik Çerezler:</strong> Site performansını ölçmek için kullanılır.</li><li><strong>Tercih Çerezleri:</strong> Tercihlerinizi hatırlamak için kullanılır.</li></ul><h3>Çerezleri Kontrol Etme</h3><p>Tarayıcı ayarlarınızdan çerezleri yönetebilir, engelleyebilir veya silebilirsiniz.</p>', 'Çerez Politikası - Parsal Metal', 'Çerez politikamız hakkında bilgi edinin.');

INSERT IGNORE INTO `prs_categories` (name, slug, sort_order) VALUES
('Alüminyum Profil', 'aluminyum-profil', 1),
('Metal Sac', 'metal-sac', 2),
('Alüminyum Boru', 'aluminyum-boru', 3),
('Endüstriyel Çözümler', 'endustriyel-cozumler', 4);

INSERT IGNORE INTO `prs_services` (name, slug, short_desc, icon, sort_order) VALUES
('Alüminyum İmalat', 'aluminyum-imalat', 'Özel ölçü ve tasarımlarda alüminyum imalat hizmetleri.', 'settings', 1),
('Metal İşleme', 'metal-isleme', 'CNC kesim, büküm ve şekillendirme hizmetleri.', 'tool', 2),
('Yüzey İşlem', 'yuzey-islem', 'Eloksal, boya ve kaplama hizmetleri.', 'layers', 3),
('Teknik Destek', 'teknik-destek', 'Proje danışmanlığı ve teknik destek hizmetleri.', 'headphones', 4);

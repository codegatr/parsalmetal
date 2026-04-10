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
('Metalin Gucu,<br>Aluminyumun Zarafeti', 'Yuksek kaliteli metal ve aluminyum cozumleriyle projelerinize deger katiyoruz.', 'Urunlerimizi Kesfet', '/?page=urunler', '', 1),
('Guvenilir Uretim,<br>Kaliteli Malzeme', 'Onlarca yillik tecrubemizle sektorun en guvenilir tedarikcisiyiz.', 'Teklif Alin', '/?page=teklif', '', 2),
('Her Projeye<br>Ozel Cozumler', 'Aluminyum profil, sac, boru ve daha fazlasi icin dogru adrestesiniz.', 'Iletisime Gecin', '/?page=iletisim', '', 3);

INSERT IGNORE INTO `prs_stats` (label, value, icon, sort_order) VALUES
('Yillik Deneyim', '25+', 'calendar', 1),
('Tamamlanan Proje', '1500+', 'check-circle', 2),
('Mutlu Musteri', '500+', 'users', 3),
('Il Genelinde Hizmet', '81', 'map-pin', 4);

INSERT IGNORE INTO `prs_categories` (name, slug, sort_order) VALUES
('Aluminyum Profil', 'aluminyum-profil', 1),
('Metal Sac', 'metal-sac', 2),
('Aluminyum Boru', 'aluminyum-boru', 3),
('Endustriyel Cozumler', 'endustriyel-cozumler', 4);

INSERT IGNORE INTO `prs_services` (name, slug, short_desc, icon, sort_order) VALUES
('Aluminyum Imalat', 'aluminyum-imalat', 'Ozel olcu ve tasarimlarda aluminyum imalat hizmetleri.', 'settings', 1),
('Metal Isleme', 'metal-isleme', 'CNC kesim, bukum ve sekillendirme hizmetleri.', 'tool', 2),
('Yuzey Islem', 'yuzey-islem', 'Eloksal, boya ve kaplama hizmetleri.', 'layers', 3),
('Teknik Destek', 'teknik-destek', 'Proje danismanligi ve teknik destek hizmetleri.', 'headphones', 4);

INSERT IGNORE INTO `prs_pages` (slug, title, content, meta_title, meta_description) VALUES ('hakkimizda', 'Hakkimizda', '<h2>Parsal Metal Aluminyum</h2><p>1999 yilindan bu yana metal ve aluminyum sektorunde faaliyet gosteren firmamiz, musterilerine yuksek kaliteli urun ve hizmetler sunmaktadir.</p><p>Deneyimli kadromuz ve modern uretim tesislerimizle her olcekteki projeye cozum uretiyoruz.</p>', 'Hakkimizda - Parsal Metal Aluminyum', 'Parsal Metal Aluminyum hakkinda bilgi edinin.');

INSERT IGNORE INTO `prs_pages` (slug, title, content, meta_title, meta_description) VALUES ('kvkk', 'KVKK Aydinlatma Metni', '<h2>KVKK Aydinlatma Metni</h2><p>Parsal Metal Aluminyum, 6698 sayili Kisisel Verilerin Korunmasi Kanunu kapsaminda veri sorumlusu sifatiyla kisisel verilerinizi islemektedir.</p><h3>Kisisel Verilerin Islenme Amaci</h3><p>Kisisel verileriniz teklif sureclerinin yurutulmesi, musteri iliskilerinin yonetimi ve hizmet sunumu amaclarıyla islenmektedir.</p><h3>Haklariniz</h3><p>KVKK kapsaminda kisisel verilerinizin islenmesi, duzeltilmesi veya silinmesini talep etme hakkina sahipsiniz.</p><h3>Iletisim</h3><p>Haklarinizi kullanmak icin info@parsal.com.tr adresine basvurabilirsiniz.</p>', 'KVKK Aydinlatma Metni - Parsal Metal', 'KVKK kapsaminda kisisel verilerinizin islenmesine iliskin aydinlatma metni.');

INSERT IGNORE INTO `prs_pages` (slug, title, content, meta_title, meta_description) VALUES ('gizlilik', 'Gizlilik Politikasi', '<h2>Gizlilik Politikasi</h2><p>Bu gizlilik politikasi, Parsal Metal Aluminyum web sitesini ziyaret ettiginizde toplanan bilgilerin nasil kullanildigini aciklamaktadir.</p><h3>Toplanan Bilgiler</h3><p>Iletisim formlari araciligiyla ad, e-posta ve telefon numarasi gibi kisisel bilgileriniz toplanabilir.</p><h3>Cerezler</h3><p>Web sitemiz kullanici deneyimini iyilestirmek amaciyla cerezler kullanmaktadir.</p><h3>Gizlilik</h3><p>Kisisel verileriniz acik rizaniz olmaksizin ucuncu taraflarla paylasilmaz.</p>', 'Gizlilik Politikasi - Parsal Metal', 'Gizlilik politikamiz hakkinda bilgi edinin.');

INSERT IGNORE INTO `prs_pages` (slug, title, content, meta_title, meta_description) VALUES ('cerez', 'Cerez Politikasi', '<h2>Cerez Politikasi</h2><p>Bu politika, parsal.com.tr web sitesinde kullanilan cerezleri aciklamaktadir.</p><h3>Cerez Nedir?</h3><p>Cerezler, web sitelerinin tarayicinizda depoladigi kucuk metin dosyalaridir.</p><h3>Kullandigimiz Cerezler</h3><ul><li>Zorunlu Cerezler: Sitenin temel islevleri icin gereklidir.</li><li>Analitik Cerezler: Site performansini olcmek icin kullanilir.</li></ul><h3>Cerezleri Kontrol Etme</h3><p>Tarayici ayarlarinizdan cerezleri yonetebilirsiniz.</p>', 'Cerez Politikasi - Parsal Metal', 'Cerez politikamiz hakkinda bilgi edinin.');

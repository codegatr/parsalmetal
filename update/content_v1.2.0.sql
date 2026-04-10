-- =====================================================
-- Parsal Metal - İçerik Güncellemesi v1.2.0
-- Alüminyum Doğrama, Dış Cephe, Dekorasyon - İzmir
-- =====================================================

-- SLIDER
DELETE FROM prs_slider;
INSERT INTO prs_slider (title, subtitle, button_text, button_url, sort_order, is_active) VALUES
('Alüminyum Doğrama''da<br>İzmir''in Tercihi', 'Pencere, kapı, sürme sistemlerinden dış cephe kaplamalarına kadar anahtar teslim çözümler. 20 yıllık deneyim, garantili işçilik.', 'Ürünlerimizi İnceleyin', '/?page=urunler', 1, 1),
('Dış Cephe Sistemleri<br>Uzmanı', 'Kompozit panel, giydirme cephe, silikon cephe ve kapaklı cephe sistemlerinde İzmir''in güvenilir adresi.', 'Teklif Alın', '/?page=teklif', 2, 1),
('Dekorasyon ve<br>Mimari Sistemler', 'Cam balkon, küpeşte, ofis bölme sistemleri ve iç-dış dekorasyon uygulamalarında yılların birikimi.', 'Hizmetlerimiz', '/?page=hizmetler', 3, 1);

-- STATS
DELETE FROM prs_stats;
INSERT INTO prs_stats (label, value, icon, sort_order) VALUES
('Yıllık Deneyim', '20+', 'calendar', 1),
('Tamamlanan Proje', '850+', 'check-circle', 2),
('Mutlu Müşteri', '600+', 'users', 3),
('Uzman Ekip', '25', 'award', 4);

-- KATEGORILER
DELETE FROM prs_categories;
INSERT INTO prs_categories (name, slug, sort_order, is_active) VALUES
('Alüminyum Doğrama', 'aluminyum-dograma', 1, 1),
('Dış Cephe Sistemleri', 'dis-cephe-sistemleri', 2, 1),
('Cam Sistemleri', 'cam-sistemleri', 3, 1),
('Dekorasyon ve Aksesuar', 'dekorasyon-aksesuar', 4, 1);

-- HİZMETLER
DELETE FROM prs_services;
INSERT INTO prs_services (name, slug, short_desc, description, icon, sort_order, is_active) VALUES
('Alüminyum Doğrama', 'aluminyum-dograma',
 'Isı yalıtımlı ve yalıtımsız alüminyum pencere, kapı, sürme ve katlanır sistemler.',
 '<h3>Alüminyum Doğrama Sistemleri</h3><p>Modern yapılarda en çok tercih edilen yapı elemanlarından biri olan alüminyum doğrama sistemleri; dayanıklılığı, estetiği ve uzun ömrüyle öne çıkar. Parsal Metal olarak, konut ve ticari projelerde ısı yalıtımlı ve ısı yalıtımsız alüminyum doğrama çözümleri sunuyoruz.</p><h4>Hizmetlerimiz</h4><ul><li>Isı yalıtımlı alüminyum pencere ve kapı sistemleri</li><li>Sürme ve açılır alüminyum doğramalar</li><li>Alüminyum katlanır kapı ve pencereler</li><li>Giyotin cam sistemleri</li><li>Motorlu panjur ve kepenk sistemleri</li><li>İş yeri ve garaj kapıları</li></ul><p>Kullandığımız profiller Alumil, Linea Rossa, Alfore ve Saray Alüminyum gibi önde gelen markalardan temin edilmektedir.</p>',
 'tool', 1, 1),

('Dış Cephe Sistemleri', 'dis-cephe-sistemleri',
 'Giydirme cephe, kompozit panel kaplama, silikon ve kapaklı cephe uygulamaları.',
 '<h3>Dış Cephe Kaplama Sistemleri</h3><p>Binanızın dış görünümünü modernize etmek ve enerji verimliliğini artırmak için profesyonel dış cephe kaplama çözümleri sunuyoruz. Her projeye özel tasarım ve uygulama yapıyoruz.</p><h4>Cephe Sistemlerimiz</h4><ul><li>Kompozit panel giydirme cephe sistemleri</li><li>Silikon giydirme cephe (strüktürel sistem)</li><li>Kapaklı giydirme cephe sistemleri</li><li>Transparan cam cepheler</li><li>Alüminyum ve çelik konstrüksiyon sistemleri</li><li>Isı yalıtımlı cephe uygulamaları</li></ul><p>İzmir ve çevresinde konut, rezidans, hastane, okul ve AVM projelerinde 20 yılı aşkın deneyimimizle hizmet vermekteyiz.</p>',
 'layers', 2, 1),

('Cam Balkon ve Küpeşte', 'cam-balkon-kupeste',
 'Giyotin cam balkon sistemleri, alüminyum ve camlı küpeşte uygulamaları.',
 '<h3>Cam Balkon ve Küpeşte Sistemleri</h3><p>Balkon ve teraslarınızı dört mevsim kullanılabilir hale getiren cam balkon sistemleri ile estetik ve güvenli küpeşte çözümleri sunuyoruz.</p><h4>Ürün Gamımız</h4><ul><li>Motorlu giyotin cam balkon sistemleri</li><li>Manuel cam balkon sistemleri</li><li>Camlı alüminyum küpeşte</li><li>Paslanmaz çelik küpeşte</li><li>Merdiven ve balkon korkulukları</li><li>Cam korkuluk sistemleri</li></ul>',
 'shield', 3, 1),

('Dekorasyon ve Diğer', 'dekorasyon-ve-diger',
 'Ofis bölme sistemleri, otomatik kapılar, tente ve pergola uygulamaları.',
 '<h3>Dekorasyon ve Mimari Sistemler</h3><p>İç ve dış mekan dekorasyon çözümlerinde geniş ürün yelpazemizle hizmetinizdeyiz.</p><h4>Hizmetlerimiz</h4><ul><li>Alüminyum ofis bölme sistemleri</li><li>Fotoselli ve otomatik kapılar</li><li>Tente ve pergola sistemleri</li><li>Duşakabin sistemleri</li><li>Sineklik sistemleri</li><li>Güneş kırıcı ve saçak uygulamaları</li></ul>',
 'star', 4, 1);

-- ÜRÜNLER
DELETE FROM prs_products;
INSERT INTO prs_products (category_id, name, slug, short_desc, specs, sort_order, is_featured, is_active) VALUES
(1, 'Isı Yalıtımlı Alüminyum Pencere', 'isi-yalitimli-aluminyum-pencere',
 'Enerji tasarrufu sağlayan ısı bariyerli alüminyum pencere sistemleri. Tüm bina tiplerine uygun, 20 yıl garanti.',
 'Profil: Isı bariyerli alüminyum
Cam: Çift cam (4+16+4 veya 4+18+4)
Renk Seçeneği: 200+ RAL renk
Hava Sızdırmazlık: Class 4
Su Sızdırmazlık: Class E1050
Garanti: 10 yıl',
 1, 1, 1),

(1, 'Sürme Alüminyum Kapı-Pencere', 'surme-aluminyum-kapi-pencere',
 'Yatay ve dikey sürme sistemler. Balkon, veranda ve geniş açıklıklar için ideal çözüm.',
 'Profil: Alüminyum ekstrüzyon
Kanat Sayısı: 2, 3 veya 4 kanat
Açılma: Yatay sürme
Kullanım Alanı: Balkon, terasa, konut, ticari
Donanım: Güvenlik kilitli',
 2, 1, 1),

(1, 'Katlanır Kapı Sistemleri', 'katlanir-kapi-sistemleri',
 'Büyük açıklıklar için katlanır alüminyum kapı sistemleri. Kafe, restoran, villa ve konut projelerinde ideal.',
 'Profil: Alüminyum
Açılma: İç veya dış yönde katlanır
Min. Kanat: 3 kanat
Max. Kanat: 8 kanat
Kilitleme: Çok noktalı kilit',
 3, 1, 1),

(1, 'Motorlu Panjur Sistemleri', 'motorlu-panjur-sistemleri',
 'Uzaktan kumandalı, motorlu alüminyum panjur sistemleri. Güvenlik ve enerji verimliliği bir arada.',
 'Materyal: Ekstrüde alüminyum lamel
Tahrik: Elektrikli motor
Kontrol: Uzaktan kumanda / Akıllı ev uyumlu
Yalıtım: Poliüretan dolgulu lamel
Güvenlik: Hırsızlığa karşı dayanıklı',
 4, 0, 1),

(2, 'Kompozit Panel Cephe', 'kompozit-panel-cephe',
 'Alüminyum kompozit panel ile binanıza modern ve çağdaş bir görünüm kazandırın. Binlerce renk seçeneği.',
 'Malzeme: Alüminyum kompozit panel (3mm veya 4mm)
Yüzey: PVDF veya PE kaplama
Renk: Her RAL rengi
Yangın Sınıfı: FR (fire resistant) seçeneği mevcut
Uygulama: Klipsli montaj sistemi
Garanti: 10 yıl',
 1, 1, 1),

(2, 'Silikon Giydirme Cephe', 'silikon-giydirme-cephe',
 'Strüktürel ve yarı strüktürel silikon cephe sistemleri. Ofis binaları, AVM ve hastaneler için ideal.',
 'Sistem: Strüktürel silikon bağlı
Cam: Reflektif, düşük emisyonlu seçenekler
Profil: Alüminyum taşıyıcı sistem
Su ve Hava Sızdırmazlık: EN standartlarına uygun
Uygulama: Proje bazlı tasarım',
 2, 1, 1),

(2, 'Kapaklı Giydirme Cephe', 'kapakli-giydirme-cephe',
 'Klasik görünümlü, bakımsız ve uzun ömürlü kapaklı cephe sistemleri. Her iklim koşuluna uygun.',
 'Kapak Profil: Alüminyum ekstrüzyon
Cam Tutturma: Mekanik baskı çıtaları
Yalıtım: EPDM fitil
Hava Sızdırmazlık: Class 4
İklim Dayanımı: Her iklim koşulu',
 3, 0, 1),

(3, 'Motorlu Giyotin Cam Balkon', 'motorlu-giyotin-cam-balkon',
 'Balkonlarınızı 4 mevsim yaşam alanına dönüştüren motorlu giyotin cam sistemi.',
 'Cam: 8mm temperli güvenlik camı
Hareket: Aşağı veya yukarı giyotin
Tahrik: Motorlu (uzaktan kumandalı)
Profil: Alüminyum
Çerçeve: Çerçevesiz veya çerçeveli seçenek',
 1, 1, 1),

(3, 'Camlı Alüminyum Küpeşte', 'camli-aluminyum-kupeste',
 'Balkon, teras ve merdivenlere estetik ve güvenli camlı küpeşte sistemleri.',
 'Cam: 10mm veya 12mm lamine temperli
Profil: Alüminyum
Montaj: Kimyasal ankraj
Yükseklik: 90cm veya 110cm
Renk: RAL renk seçeneği',
 2, 0, 1),

(4, 'Alüminyum Ofis Bölme Sistemleri', 'aluminyum-ofis-bolme',
 'Çalışma alanlarını fonksiyonel ve estetik şekilde düzenleyen alüminyum ofis bölme çözümleri.',
 'Profil: Alüminyum taşıyıcı sistem
Dolgu: Cam, sağır panel veya karma
Ses Yalıtımı: 35-45 dB
Yükseklik: Tavana kadar veya yarım boy
Cam Kalınlığı: 6mm veya 8mm temperli',
 1, 0, 1),

(4, 'Tente ve Pergola Sistemleri', 'tente-ve-pergola',
 'Terasa, bahçe ve işyerleri için motorlu tente, alüminyum pergola ve güneş kırıcı sistemleri.',
 'Materyal: Alüminyum konstrüksiyon
Kumaş: Su geçirmez, UV korumalı
Tahrik: Manuel veya motorlu
Kontrol: Uzaktan kumanda
Kullanım Alanı: Konut, kafe, restoran, otel',
 2, 0, 1);

-- SAYFALAR - HAKKIMIZDA
UPDATE prs_pages SET
  title = 'Hakkımızda',
  content = '<h2>Parsal Metal Alüminyum Kimdir?</h2>
<p>Parsal Metal Alüminyum Doğrama, Dekorasyon ve Dış Cephe Sistemleri olarak İzmir''de 2000''li yılların başından bu yana alüminyum doğrama, dış cephe sistemleri ve dekorasyon alanında hizmet vermekteyiz.</p>
<p>Toprak Sarnıç''taki modern atölyemizde, deneyimli teknik kadromuz ve son teknoloji ekipmanlarımızla; konutlardan ticari binalara, hastanelerden okullara kadar her ölçekteki projeye anahtar teslim çözümler üretiyoruz.</p>
<h3>Neler Yapıyoruz?</h3>
<p>Alüminyum pencere ve kapı sistemleri, dış cephe giydirme uygulamaları, cam balkon ve küpeşte sistemleri, ofis bölme sistemleri, tente ve pergola uygulamaları başlıca hizmet alanlarımızdır.</p>
<p>Alumil, Linea Rossa, Alfore ve Saray Alüminyum gibi sektörün önde gelen markalarının yetkili üretici/bayi ağında yer alarak müşterilerimize en kaliteli malzeme ve sistemi en uygun fiyatla sunuyoruz.</p>
<h3>Kalite ve Güvence</h3>
<p>Her projede zamanında teslimat ve işçilik garantisi temel ilkelerimizdir. Montaj sonrası satış desteği ve bakım hizmetleriyle müşterilerimizin yanında olmaya devam ediyoruz. İzmir''in farklı ilçelerinde tamamladığımız 850''den fazla proje referansımız, sektördeki güvenilirliğimizin en büyük kanıtıdır.</p>',
  meta_title = 'Hakkımızda - Parsal Metal Alüminyum Doğrama İzmir',
  meta_description = 'İzmir''de 20 yılı aşkın deneyimiyle alüminyum doğrama, dış cephe sistemleri ve dekorasyon alanında güvenilir çözümler sunan Parsal Metal Alüminyum.'
WHERE slug = 'hakkimizda';

-- AYARLAR - İçerik
INSERT INTO prs_settings (setting_key, setting_value) VALUES
('about_short', 'İzmir''in köklü alüminyum doğrama ve dış cephe sistemleri firması olarak konut ve ticari projelerde 20 yılı aşkın deneyimimizle anahtar teslim çözümler sunuyoruz. Alumil, Linea Rossa ve Alfore yetkili üreticisiyiz.'),
('about_features', 'Alumil, Linea Rossa, Alfore yetkili üretici ve bayi
20 yılı aşkın sektör deneyimi
Her projede zamanında teslimat garantisi
İzmir genelinde ücretsiz keşif ve ölçüm
Montaj sonrası satış desteği ve bakım
850+ başarıyla tamamlanmış proje'),
('founded_year', '20'),
('mission_text', 'Müşterilerimize en kaliteli alüminyum doğrama, dış cephe ve dekorasyon sistemlerini, zamanında teslimat ve uygun fiyat garantisiyle sunarak İzmir''in en güvenilir yapı sistemleri çözüm ortağı olmak.'),
('vision_text', 'Alüminyum doğrama ve cephe sistemleri sektöründe teknolojik yatırımlarımızı sürdürerek Ege Bölgesi''nin lider firması konumuna ulaşmak ve kalite standartlarıyla sektöre yön vermek.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);


# Dijital Sanat Portfolyo Yönetim Sistemi (CMS)
Bu proje; sanatçıların eserlerini dinamik, güvenli ve kategorize edilmiş bir şekilde sergilemelerini sağlayan, uçtan uca (end-to-end) geliştirilmiş bir içerik yönetim sistemidir. Sadece bir galeri değil, arka planda gelişmiş bir veri ve dosya yönetim mekanizması barındıran profesyonel bir otomasyondur.

# Proje Gelişim Yol Haritası (Adım Adım Hazırlanış)
Bu projenin geliştirilme süreci, modern web standartları takip edilerek 5 ana aşamada tamamlanmıştır:

# 1. Aşama: Veritabanı Tasarımı ve İlişkilendirme
Projenin temeli, verilerin birbirini bozmadan saklanabilmesi için İlişkisel Veritabanı (RDBMS) mantığıyla MySQL üzerinde kuruldu.

* **Kullanıcı Yönetimi:** Admin girişi için users tablosu oluşturuldu.
* **Kategori Sistemi:** Eserleri gruplandırmak için categories tablosu eklendi.
* **İlişkisel Mimari:** artworks tablosu, category_id üzerinden kategorilere bağlandı. Silme işlemlerinde veri kaybını önlemek için SET NULL mantığı kurgulandı.

# 2. Aşama: Merkezi Bağlantı ve Konfigürasyon (includes/)
* * Sistemin her yerinde aynı ayarların geçerli olması için db_connection.php dosyası "Sistemin Kalbi" olarak tasarlandı.
* * Veritabanı el sıkışması (Handshake) sağlandı.
* * SITE_TITLE, BASE_URL gibi global sabitler tanımlandı.
* * settings tablosundan gelen verilerle sitenin dinamik kimliği oluşturuldu.

# 3. Aşama: Güvenlik ve Oturum Katmanı
* * Erişim güvenliğini sağlamak amacıyla giriş-çıkış mekanizması geliştirildi.

* **Şifreleme:** Güvenlik için düz metin şifreler yerine password_hash kullanılarak veritabanı güvenliği maksimize edildi.
* **SQL Injection Savunması:** Tüm sorgular Prepared Statements (Hazırlanmış Sorgular) ile yazılarak dış müdahalelere karşı izole edildi.

# 4. Aşama: Admin Paneli ve CRUD Operasyonları (admin/)
* * Yöneticinin içerik üzerinde tam kontrol sahibi olabilmesi için 4 temel operasyon kodlandı:

* **Create (Ekleme):** uniqid() ile benzersiz görsel isimlendirme ve sunucuya dosya yükleme.
* **Read (Listeleme):** LEFT JOIN sorguları ile eserleri kategorileriyle birlikte çekme.
* **Update (Düzenleme):** Eski görselleri sunucudan temizleyerek yeni veri girişi yapma.
* **Delete (Silme):** Veritabanı kaydıyla birlikte fiziksel dosyayı (unlink) imha etme.

# 5. Aşama: Frontend ve Kullanıcı Deneyimi
Ziyaretçilerin sanat eserlerine odaklanabilmesi için Dark Mode temalı, Bootstrap 5 destekli bir arayüz geliştirildi. Sadece is_published = TRUE olan eserlerin sergilenmesi sağlandı.

# Dosya Yapısı ve Görevleri
Plaintext

dijital-sanat-portfolyo/
├── admin/                  # CMS ve Yönetim Fonksiyonları
│   ├── artworks_add.php    # Güvenli eser yükleme modülü
│   ├── artworks_edit.php   # Kaynak yönetimi destekli düzenleme
│   ├── categories.php      # Dinamik kategori kontrolü
│   ├── settings.php        # Merkezi site konfigürasyon paneli
│   └── login.php           # Kriptografik doğrulama kapısı
├── assets/                 # Stil ve UI Bileşenleri
│   ├── css/style.css       # Özel "Dark Mode" teması
│   └── js/                 # Etkileşimli öğeler
├── includes/               # Çekirdek Yapı
│   └── db_connection.php   # MySQL köprüsü ve Global sabitler
├── uploads/                # Fiziksel Medya Deposu
└── index.php               # Ziyaretçi Vitrini (Frontend)

# Sistemi Çalıştırma (Kurulum Adımları)
* * Projeyi yerel sunucunuzda (XAMPP/WAMP) ayağa kaldırmak için:

* **Klasörü Taşıyın:** Proje klasörünü htdocs veya www dizinine kopyalayın.
* **Veritabanı Oluşturun:** phpMyAdmin üzerinden dijital_sanat adında bir veritabanı açın.
* **SQL İçe Aktarma:** database.sql (eğer varsa) dosyasını içe aktarın veya tabloları dökümantasyondaki şemaya göre manuel oluşturun.
* **Konfigürasyon:** includes/db_connection.php dosyasındaki kullanıcı adı ve şifre bilgilerini kendi sunucunuza göre düzenleyin.
* **Erişim:** Tarayıcınıza http://localhost/dijital-sanat-portfolyo yazarak galeriyi görebilir, /admin ekleyerek panele girebilirsiniz.

# Güvenlik ve Performans Standartları

* **XSS Koruması:** htmlspecialchars() ile tüm veriler arındırılır.
* **Veri Bütünlüğü:** Kategori silindiğinde eserlerin kategori ID'si otomatik NULL yapılarak veri kaybı önlenir.
* **Hafıza Yönetimi:** Silinen her eserin görseli sunucu diskinden de fiziksel olarak kaldırılarak disk doluluğu önlenir.

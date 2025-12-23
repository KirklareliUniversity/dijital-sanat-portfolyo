<?php
// Veritabanı ayarları
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dijital_sanat";

// Bağlantı oluşturma
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı hatasını kontrol et
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Türkçe karakter desteği
$conn->set_charset("utf8mb4");

// BASE_URL Hatasını kökten çözelim
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/dijital-sanat-portfolyo');
}

// Site ayarlarını çek
$settings_sql = "SELECT * FROM settings WHERE id = 1";
$settings_res = mysqli_query($conn, $settings_sql);
$site_data = mysqli_fetch_assoc($settings_res);

// Diğer sabitler
if (!defined('SITE_TITLE')) define('SITE_TITLE', $site_data['site_title'] ?? 'Dijital Portfolyo');
if (!defined('SITE_NAME')) define('SITE_NAME', SITE_TITLE);
if (!defined('FOOTER_TEXT')) define('FOOTER_TEXT', $site_data['footer_text'] ?? '© 2024');
?>
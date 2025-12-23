<?php
session_start();
require_once '../includes/db_connection.php';

// Yetki kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$artwork_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($artwork_id > 0) {

    // Silinecek dosya yolunu al
    $stmt = $conn->prepare("SELECT image_path FROM artworks WHERE id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $file_path = $res->fetch_assoc()['image_path'];

        // Database kaydını sil
        $del = $conn->prepare("DELETE FROM artworks WHERE id = ?");
        $del->bind_param("i", $artwork_id);
        $del->execute();

        // Dosyayı sil (varsa)
        if (!empty($file_path) && file_exists($file_path)) {
            unlink($file_path);
        }
    }
}

// DOĞRU YÖNLENDİRME
header("Location: artworks.php?status=deleted");
exit;


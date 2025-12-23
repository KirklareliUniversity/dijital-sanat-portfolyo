<?php
session_start();
require_once '../includes/db_connection.php'; 

// Oturum ve Yetki Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$category_id = (int)$_GET['id'] ?? 0;

if ($category_id > 0) {
    
    // 1. Kategoriye bağlı eserlerin category_id'sini NULL yapma
    // Bu, kategoriyi silsek bile eserlerin kaybolmamasını sağlar.
    $update_sql = "UPDATE artworks SET category_id = NULL WHERE category_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $category_id);
    $update_stmt->execute();
    $update_stmt->close();


    // 2. Kategoriyi silme
    $delete_sql = "DELETE FROM categories WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $category_id);
    
    if ($delete_stmt->execute()) {
        $delete_stmt->close();
        $conn->close();
        // Başarılı yönlendirme
        header('Location: categories.php?status=deleted');
        exit;
    } else {
        $delete_stmt->close();
        $conn->close();
        // Hatalı yönlendirme
        header('Location: categories.php?status=error&msg=delete_failed');
        exit;
    }

} else {
    // ID yoksa listeye geri dön
    header('Location: categories.php');
    exit;
}
?>
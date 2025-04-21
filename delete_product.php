<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: admin.php');
    exit;
}

// Ambil data produk untuk mendapatkan path foto
$stmt = $conn->prepare("SELECT photo FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($product) {
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Hapus related order_items
        $stmt = $conn->prepare("DELETE FROM order_items WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();

        // Hapus produk dari database
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();

        // Hapus foto dari server
        if (file_exists($product['photo'])) {
            unlink($product['photo']);
        }

        // Commit the transaction
        $conn->commit();
        $_SESSION['message'] = "Product deleted successfully.";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "Failed to delete product: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Product not found.";
}

$conn->close();
header('Location: admin.php');
exit;
?>
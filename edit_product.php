<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$message = '';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data produk
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $photo = $_FILES['photo'];

    // Validasi input
    if (empty($name) || empty($description) || empty($price) || empty($stock)) {
        $message = "Semua field harus diisi!";
    } else {
        $target_file = $product['photo']; // Gunakan foto lama jika tidak ada foto baru
        if (!empty($photo['name'])) {
            // Validasi foto
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            if (!in_array($photo['type'], $allowed_types) || $photo['size'] > $max_size) {
                $message = "Foto harus berupa JPG, PNG, atau GIF dan maksimal 2MB!";
            } else {
                $target_dir = "uploads/";
                $target_file = $target_dir . uniqid() . '_' . basename($photo['name']);
                if (move_uploaded_file($photo['tmp_name'], $target_file)) {
                    // Hapus foto lama jika ada
                    if (file_exists($product['photo'])) {
                        unlink($product['photo']);
                    }
                } else {
                    $message = "Gagal mengunggah foto!";
                }
            }
        }

        if (!$message) {
            // Update produk menggunakan prepared statement
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, photo = ? WHERE id = ?");
            $stmt->bind_param("ssdssi", $name, $description, $price, $stock, $target_file, $product_id);
            $stmt->execute();
            $message = "Produk berhasil diperbarui!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Toko Elektronik</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin.php">Kembali ke Admin</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container mt-4">
        <h1>Edit Produk</h1>
        <?php if ($message) echo "<div class='alert alert-info'>$message</div>"; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Harga</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" class="form-control" value="<?php echo $product['stock']; ?>" required>
            </div>
            <div class="form-group">
                <label>Foto Produk (JPG, PNG, GIF, max 2MB, kosongkan jika tidak ingin mengganti)</label>
                <input type="file" name="photo" class="form-control">
                <img src="<?php echo $product['photo']; ?>" alt="Foto Produk" width="100" class="mt-2">
            </div>
            <input type="submit" value="Perbarui Produk" class="btn btn-primary">
        </form>
    </div>
</body>
</html>
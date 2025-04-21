<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $photo = $_FILES['photo'];

    // Validasi input
    if (empty($name) || empty($description) || empty($price) || empty($stock) || empty($photo['name'])) {
        $message = "Semua field harus diisi!";
    } else {
        // Validasi foto
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        if (!in_array($photo['type'], $allowed_types) || $photo['size'] > $max_size) {
            $message = "Foto harus berupa JPG, PNG, atau GIF dan maksimal 2MB!";
        } else {
            $target_dir = "uploads/";
            $target_file = $target_dir . uniqid() . '_' . basename($photo['name']);
            if (move_uploaded_file($photo['tmp_name'], $target_file)) {
                // Simpan produk menggunakan prepared statement
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, photo) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdss", $name, $description, $price, $stock, $target_file);
                $stmt->execute();
                $message = "Produk berhasil ditambahkan!";
            } else {
                $message = "Gagal mengunggah foto!";
            }
        }
    }
}

// Ambil daftar produk
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Produk</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">Toko Elektronik</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container mt-4">
        <h1>Kelola Produk</h1>
        <!-- Tampilkan pesan sukses dari session (misalnya dari delete_product.php) -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class='alert alert-success'>
                <?php 
                echo htmlspecialchars($_SESSION['message']); 
                unset($_SESSION['message']); // Hapus setelah ditampilkan
                ?>
            </div>
        <?php endif; ?>
        <!-- Tampilkan pesan error dari session (misalnya dari delete_product.php) -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class='alert alert-danger'>
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']); // Hapus setelah ditampilkan
                ?>
            </div>
        <?php endif; ?>
        <!-- Tampilkan pesan dari proses tambah produk -->
        <?php if ($message): ?>
            <div class='alert alert-info'>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulir Tambah Produk -->
        <h3>Tambah Produk</h3>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label>Harga</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Foto Produk (JPG, PNG, GIF, max 2MB)</label>
                <input type="file" name="photo" class="form-control" required>
            </div>
            <input type="submit" name="add_product" value="Tambah Produk" class="btn btn-primary">
        </form>

        <!-- Daftar Produk -->
        <h3 class="mt-5">Daftar Produk</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Deskripsi</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Foto</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td>Rp <?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td><img src="<?php echo $product['photo']; ?>" alt="Foto Produk" width="50"></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
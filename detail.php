<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM products WHERE id=$id";
$result = $conn->query($sql);
$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - <?php echo $product['name']; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Toko Elektronik</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">Keranjang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container mt-4">
        <h1><?php echo $product['name']; ?></h1>
        <img src="<?php echo $product['photo']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid mb-3">
        <p><?php echo $product['description']; ?></p>
        <p>Harga: Rp <?php echo number_format($product['price'], 2); ?></p>
        <p>Stok: <?php echo $product['stock']; ?></p>
        <form method="post" action="cart.php">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            Jumlah: <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
            <input type="submit" value="Tambah ke Keranjang" class="btn btn-success">
        </form>
        <a href="index.php" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</body>
</html>
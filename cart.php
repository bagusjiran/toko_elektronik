<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
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
        <h1>Keranjang Belanja</h1>
        <?php
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            echo '<table class="table">';
            echo '<thead><tr><th>Produk</th><th>Jumlah</th><th>Subtotal</th></tr></thead><tbody>';
            $total = 0;
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $sql = "SELECT * FROM products WHERE id=$product_id";
                $result = $conn->query($sql);
                $product = $result->fetch_assoc();
                $subtotal = $product['price'] * $quantity;
                $total += $subtotal;
                echo "<tr><td>{$product['name']}</td><td>$quantity</td><td>Rp " . number_format($subtotal, 2) . "</td></tr>";
            }
            echo '</tbody></table>';
            echo '<p>Total: Rp ' . number_format($total, 2) . '</p>';
            echo '<a href="checkout.php" class="btn btn-primary">Checkout</a>';
        } else {
            echo "<p>Keranjang kosong</p>";
        }
        ?>
        <a href="index.php" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</body>
</html>
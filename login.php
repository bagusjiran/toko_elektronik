<?php
session_start();
require_once 'vendor/autoload.php';
include 'config.php';

$client = new Google_Client();
$client->setClientId($google_client_id);
$client->setClientSecret($google_client_secret);
$client->setRedirectUri($google_redirect_uri);
$client->addScope("email");
$client->addScope("profile");

$login_url = $client->createAuthUrl();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validasi input
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        // Cek pengguna di database
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                if ($user['role'] == 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Elektronik</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Login Toko Elektronik</h2>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- Login Admin -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h4 class="card-title">Login Admin</h4>
                        <form method="post">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <input type="submit" name="admin_login" value="Login" class="btn btn-primary w-100">
                        </form>
                    </div>
                </div>
                <!-- Login Google -->
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="card-title">Login Pengguna</h4>
                        <a href="<?php echo $login_url; ?>" class="btn btn-danger">Login with Google</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
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

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    $email = $google_account_info->email;
    $name = $google_account_info->name;

    // Cek apakah email sudah terdaftar
    $sql = "SELECT * FROM users WHERE username='$email'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
    } else {
        // Daftarkan pengguna baru
        $sql = "INSERT INTO users (username, password, role) VALUES ('$email', '', 'customer')";
        $conn->query($sql);
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['role'] = 'customer';
    }
    header('Location: index.php');
} else {
    header('Location: login.php');
}
?>
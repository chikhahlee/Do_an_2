<?php
session_start();

$conn = new mysqli("localhost", "root", "", "doan_2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// xử lí đăng nhập
if (isset($_POST['login_submit'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, email, password, ten, role FROM users WHERE email = '$email' LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // so sánh mật khẩu 
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['ten'];
            $_SESSION['is_logged_in'] = true;

            // kiểm tra role
            $_SESSION['is_admin'] = ($user['role'] === 'admin');

            header("Location: /index.php");
            exit;
        }
    }

    $_SESSION['login_error'] = "Email hoặc mật khẩu không đúng.";
    header("Location: /index.php");
    exit;
}
?>
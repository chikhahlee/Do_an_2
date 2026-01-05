<?php
session_start();

$conn = new mysqli("localhost", "root", "", "doan_2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// XỬ LÝ ĐĂNG NHẬP
if (isset($_POST['login_submit'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // include 'role' so we can detect admin users from DB
    $sql = "SELECT id, email, password, ten, role FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            if ($password === $user['password']) { 
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['ten'];
                $_SESSION['is_logged_in'] = true;

                // mark admin based on DB role if available, otherwise fallback to configured admin emails
                $isAdmin = false;
                if (isset($user['role']) && $user['role'] === 'admin') {
                    $isAdmin = true;
                } else {
                    $cfg = [];
                    $cfgPath = __DIR__ . '/../Other/config.php';
                    if (file_exists($cfgPath)) {
                        $cfg = include $cfgPath;
                    }
                    $adminEmails = isset($cfg['admin_emails']) && is_array($cfg['admin_emails']) ? $cfg['admin_emails'] : [];
                    if (in_array($user['email'], $adminEmails, true)) {
                        $isAdmin = true;
                    }
                }
                $_SESSION['is_admin'] = $isAdmin;

                header("Location: /index.php");
                $stmt->close();
                $conn->close();
                exit;
            }
        }
    }

    $_SESSION['login_error'] = "Email hoặc mật khẩu không đúng.";
}

header("Location: /index.php");
$conn->close();
exit;
?>
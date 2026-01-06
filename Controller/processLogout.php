<?php
session_start();

// gán mảng rỗng để xóa dữ liệu hiện tại
$_SESSION = array(); 

// Xóa Session cookie 
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// hủy Session trên máy chủ
session_destroy();

header("Location: /index.php");
exit;
?>
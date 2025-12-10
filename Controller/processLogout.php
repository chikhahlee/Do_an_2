<?php
session_start();

// Gán mảng rỗng cho $_SESSION để xóa toàn bộ dữ liệu hiện có
$_SESSION = array(); 

// Xóa Session cookie 
// Nếu sử dụng cookie để lưu Session ID, cần xóa cookie đó.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy Session trên máy chủ
session_destroy();

header("Location: /index.php");
exit;
?>
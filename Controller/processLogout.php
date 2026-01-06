<?php
session_start();

// xóa session data
$_SESSION = array();

// xóa cookie session
setcookie(session_name(), '', time() - 3600, '/');

// hủy session
session_destroy();

header("Location: /index.php");
exit;

<?php
session_start();
require_once __DIR__ . '/ProductsController.php';

$ctl = new ProductController();

// Simple router for add/edit/delete
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Helper to sanitize input
function get_post($k) {
    return isset($_POST[$k]) ? trim($_POST[$k]) : null;
}

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = get_post('ten');
    $gia = (int)get_post('gia');
    $idDanhmuc = isset($_POST['idDanhmuc']) ? (int)$_POST['idDanhmuc'] : null;

    $anhFilename = null;
    if (isset($_FILES['anh']) && $_FILES['anh']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['anh']['tmp_name'];
        $orig = basename($_FILES['anh']['name']);
        $targetDir = __DIR__ . '/../Other/image/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig);
        if (move_uploaded_file($tmp, $targetDir . $safeName)) {
            $anhFilename = $safeName;
        }
    }

    $res = $ctl->createProduct($ten, $gia, $anhFilename, $idDanhmuc);
    $_SESSION['admin_msg'] = $res ? 'Thêm sản phẩm thành công.' : 'Thêm sản phẩm thất bại.';
    header('Location: /View/manage-products.php');
    exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $ten = get_post('ten');
    $gia = (int)get_post('gia');
    $idDanhmuc = isset($_POST['idDanhmuc']) ? (int)$_POST['idDanhmuc'] : null;

    $anhFilename = null;
    if (isset($_FILES['anh']) && $_FILES['anh']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['anh']['tmp_name'];
        $orig = basename($_FILES['anh']['name']);
        $targetDir = __DIR__ . '/../Other/image/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig);
        if (move_uploaded_file($tmp, $targetDir . $safeName)) {
            $anhFilename = $safeName;
        }
    }

    $res = $ctl->updateProduct($id, $ten, $gia, $anhFilename, $idDanhmuc);
    $_SESSION['admin_msg'] = $res ? 'Cập nhật sản phẩm thành công.' : 'Cập nhật sản phẩm thất bại.';
    header('Location: /View/manage-products.php');
    exit;
}

if ($action === 'delete') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $res = $ctl->deleteProductById($id);
    $_SESSION['admin_msg'] = $res ? 'Xóa sản phẩm thành công.' : 'Xóa sản phẩm thất bại.';
    header('Location: /View/manage-products.php');
    exit;
}

// fallback
header('Location: /View/manage-products.php');
exit;

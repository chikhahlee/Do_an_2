<?php
session_start();

// kiểm tra đăng nhập
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    // thông báo đăng nhập
    $_SESSION['login_alert'] = "Vui lòng đăng nhập để thêm/xóa sản phẩm khỏi giỏ hàng.";

    header("Location: /View/product-list.php"); 
    exit;
}

$conn = new mysqli("localhost", "root", "", "doan_2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

$session_id = session_id(); 


require_once "../Controller/ProductsController.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$id = (int)$id;

switch ($action) {

    case 'add':
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $ctl = new ProductController();
            $p = $ctl->getProductById($id); 

            $_SESSION['cart'][$id] = [
                'id' => $p->id,
                'ten' => $p->ten,
                'gia' => $p->gia,
                'anh' => $p->anh,
                'quantity' => 1
            ];
        }

        $new_quantity = $_SESSION['cart'][$id]['quantity'];

        $sql_check = "SELECT id FROM cart WHERE session_id = ? AND id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("si", $session_id, $id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            // cập nhật số lượng
            $sql_update = "UPDATE cart SET soLuong = ? WHERE session_id = ? AND id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("isi", $new_quantity, $session_id, $id);
            $stmt_update->execute();
        } else {
            $sql_insert = "INSERT INTO cart (session_id, id, soLuong) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sii", $session_id, $id, $new_quantity);
            $stmt_insert->execute();
        }
        $stmt_check->close();
        
        break;

    case 'remove':
        unset($_SESSION['cart'][$id]);

        $sql_delete = "DELETE FROM cart WHERE session_id = ? AND id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("si", $session_id, $id);
        $stmt_delete->execute();
        
        break;

    case 'update':
        $quantity = intval($_GET['quantity'] ?? 0);

        if ($quantity <= 0) {
            unset($_SESSION['cart'][$id]);

            $sql_delete = "DELETE FROM cart WHERE session_id = ? AND id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("si", $session_id, $id);
            $stmt_delete->execute();
            
        } else {

            $_SESSION['cart'][$id]['quantity'] = $quantity;

            $sql_update = "UPDATE cart SET soLuong = ? WHERE session_id = ? AND id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("isi", $quantity, $session_id, $id);
            $stmt_update->execute();
        }
        break;
}

$conn->close(); 

header("Location: /View/product-list.php");
exit;
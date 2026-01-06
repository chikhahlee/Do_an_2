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

// khởi tạo giỏ hàng trong SESSION
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

switch ($action) {

    // thêm sản phẩm
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

        // kiểm tra sản phẩm đã có trong DB cart chưa
        $sql_check = "SELECT id FROM cart 
                      WHERE session_id = '$session_id' AND id = $id";
        $result = $conn->query($sql_check);

        if ($result && $result->num_rows > 0) {
            // thêm sản phẩm khi đã có trong cart
            $sql_update = "UPDATE cart SET soLuong = $new_quantity WHERE session_id = '$session_id' AND id = $id";
            $conn->query($sql_update);
        } else {
            // thêm mới sản phẩm khi chưa có trong cart
            $sql_insert = "INSERT INTO cart (session_id, id, soLuong) VALUES ('$session_id', $id, $new_quantity)";
            $conn->query($sql_insert);
        }

        break;

    // xóa sản phẩm khỏi cart
    case 'remove':

        unset($_SESSION['cart'][$id]);

        $sql_delete = "DELETE FROM cart WHERE session_id = '$session_id' AND id = $id";
        $conn->query($sql_delete);

        break;

}


$conn->close(); 

header("Location: /View/product-list.php");
exit;
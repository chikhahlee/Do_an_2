<?php
session_start();

// yêu cầu phải đăng nhập để thanh toán
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    $_SESSION['login_alert'] = "Vui lòng đăng nhập để thanh toán";
    header("Location: /View/product-list.php");
    exit;
}

// kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    $_SESSION['checkout_error'] = "Giỏ hàng trống.";
    header("Location: /View/product-list.php");
    exit;
}

// kết nối DB
$conn = new mysqli("localhost", "root", "", "doan_2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// determine which invoice table naming is present in the database
$mainTable = 'hoadon';
$itemTable = 'hoadon_items';
$checkInvoices = $conn->query("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('invoices','hoadon')");
if ($checkInvoices) {
    $rowCnt = $checkInvoices->fetch_assoc();
    // prefer 'invoices' if it exists, otherwise 'hoadon'
    $hasInvoices = $conn->query("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'invoices'");
    if ($hasInvoices && intval($hasInvoices->fetch_assoc()['cnt']) > 0) {
        $mainTable = 'invoices';
        $itemTable = 'invoice_items';
    } else {
        $mainTable = 'hoadon';
        $itemTable = 'hoadon_items';
    }
}

// create appropriate tables if missing (keep column names consistent with chosen table set)
if ($mainTable === 'invoices') {
    $sql_create_invoices = "CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        session_id VARCHAR(255),
        total DECIMAL(10,2),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    if ($conn->query($sql_create_invoices) === false) {
        error_log("[checkout] create invoices failed: " . $conn->error);
    }

    $sql_create_items = "CREATE TABLE IF NOT EXISTS invoice_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_id INT,
        product_id INT,
        product_name VARCHAR(255),
        price DECIMAL(10,2),
        quantity INT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    if ($conn->query($sql_create_items) === false) {
        error_log("[checkout] create invoice_items failed: " . $conn->error);
    }
} else {
    $sql_create_invoices = "CREATE TABLE IF NOT EXISTS hoadon (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        session_id VARCHAR(255),
        total DECIMAL(10,2),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    if ($conn->query($sql_create_invoices) === false) {
        error_log("[checkout] create hoadon failed: " . $conn->error);
    }

    $sql_create_items = "CREATE TABLE IF NOT EXISTS hoadon_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hoadon_id INT,
        product_id INT,
        product_name VARCHAR(255),
        price DECIMAL(10,2),
        quantity INT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    if ($conn->query($sql_create_items) === false) {
        error_log("[checkout] create hoadon_items failed: " . $conn->error);
    }
}

// tính tổng
$total = 0;
foreach ($_SESSION['cart'] as $c) {
    $total += $c['gia'] * $c['quantity'];
}

$user_id = $_SESSION['user_id'] ?? null; // nếu có
$session_id = session_id();

// sử dụng transaction để rollback nếu có lỗi
$conn->begin_transaction();
try {
    // chèn hóa đơn vào bảng thích hợp (invoices OR hoadon)
    if ($mainTable === 'invoices') {
        $stmt = $conn->prepare("INSERT INTO invoices (user_id, session_id, total) VALUES (?, ?, ?)");
    } else {
        $stmt = $conn->prepare("INSERT INTO hoadon (user_id, session_id, total) VALUES (?, ?, ?)");
    }
    if (!$stmt) {
        throw new Exception('Prepare invoice insert failed: ' . $conn->error);
    }

    $uid_for_insert = $user_id ? $user_id : 0;
    if (!$stmt->bind_param("isd", $uid_for_insert, $session_id, $total)) {
        throw new Exception('Bind params invoice failed: ' . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Execute invoice failed: ' . $stmt->error);
    }

    $invoice_id = $conn->insert_id;
    $stmt->close();

    // chèn các mục hàng (sử dụng tên cột PK theo bảng tương ứng)
    if ($itemTable === 'invoice_items') {
        $insert_item_sql = "INSERT INTO invoice_items (invoice_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)";
        $bindTypes = "iisdi"; // invoice_id(i), product_id(i), product_name(s), price(d), quantity(i)
    } else {
        $insert_item_sql = "INSERT INTO hoadon_items (hoadon_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)";
        $bindTypes = "iisdi"; // hoadon_id(i), product_id(i), product_name(s), price(d), quantity(i)
    }

    foreach ($_SESSION['cart'] as $id => $c) {
        $stmt_item = $conn->prepare($insert_item_sql);
        if (!$stmt_item) {
            throw new Exception('Prepare invoice_items failed: ' . $conn->error);
        }
        $pid = isset($c['id']) ? (int)$c['id'] : (int)$id;
        $pname = $c['ten'];
        $price = (float)$c['gia'];
        $qty = (int)$c['quantity'];
        if (!$stmt_item->bind_param($bindTypes, $invoice_id, $pid, $pname, $price, $qty)) {
            throw new Exception('Bind params invoice_items failed: ' . $stmt_item->error);
        }
        if (!$stmt_item->execute()) {
            throw new Exception('Execute invoice_items failed: ' . $stmt_item->error);
        }
        $stmt_item->close();
    }

    // xóa giỏ hàng trong DB của session (nếu có)
    $stmt_del = $conn->prepare("DELETE FROM cart WHERE session_id = ?");
    if ($stmt_del) {
        if (!$stmt_del->bind_param("s", $session_id)) {
            throw new Exception('Bind params delete cart failed: ' . $stmt_del->error);
        }
        if (!$stmt_del->execute()) {
            throw new Exception('Execute delete cart failed: ' . $stmt_del->error);
        }
        $stmt_del->close();
    }

    // commit transaction
    $conn->commit();

    // xóa giỏ hàng phiên làm việc
    unset($_SESSION['cart']);

    // lưu id hóa đơn để hiển thị
    $_SESSION['last_invoice_id'] = $invoice_id;

    $conn->close();

    // chuyển đến trang chi tiết hóa đơn
    header("Location: /View/invoice-detail.php?id={$invoice_id}");
    exit;

} catch (Exception $e) {
    // rollback and log
    $conn->rollback();
    error_log('[checkout] Transaction failed: ' . $e->getMessage());
    $_SESSION['checkout_error'] = 'Thanh toán thất bại: ' . $e->getMessage();
    // close connection safely
    $conn->close();
    header("Location: /View/product-list.php");
    exit;
}

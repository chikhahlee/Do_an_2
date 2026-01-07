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

$conn = new mysqli("localhost", "root", "", "doan_2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

$checkInvoices = $conn->query("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('invoices','hoadon')");
if ($checkInvoices) {
    $rowCnt = $checkInvoices->fetch_assoc();
    $hasInvoices = $conn->query("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'invoices'");
    if ($hasInvoices && intval($hasInvoices->fetch_assoc()['cnt']) > 0) {
        $mainTable = 'invoices';
        $itemTable = 'invoice_items';
    }
}

// tính tổng
$total = 0;
foreach ($_SESSION['cart'] as $c) {
    $total += $c['gia'] * $c['quantity'];
}

$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

$conn->begin_transaction();
try {

    if ($mainTable === 'invoices') {
        $stmt = $conn->prepare("INSERT INTO invoices (user_id, session_id, total) VALUES (?, ?, ?)");
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

    if ($itemTable === 'invoice_items') {
        $insert_item_sql = "INSERT INTO invoice_items (invoice_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)";
        $bindTypes = "iisdi";
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

    $conn->rollback();
    error_log('[checkout] Transaction failed: ' . $e->getMessage());
    $_SESSION['checkout_error'] = 'Thanh toán thất bại: ' . $e->getMessage();
    $conn->close();
    header("Location: /View/product-list.php");
    exit;
}

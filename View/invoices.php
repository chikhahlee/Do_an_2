<?php
session_start();
// only allow logged-in non-admin users to access invoices
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in'] || !empty($_SESSION['is_admin'])) {
    header("Location: /View/product-list.php");
    exit;
}
$hide_user_popup = true; // vô hiệu hóa popup "Xin chào" trên trang hóa đơn
require_once __DIR__ . '/../Model/InvoiceModel.php';
$invoiceModel = new InvoiceModel();

$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();
try {
    $invoices = $invoiceModel->getInvoicesByUserOrSession($user_id, $session_id);
} catch (Exception $e) {
    $invoices = [];
    $error_msg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/Other/image/iconlogo.png">
    <title>Hóa Đơn</title>
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/Other/css/style.css">
    <style>
        .shopping-cart { max-height: 400px; overflow-y: auto; padding-bottom: 1rem; }
    </style>
</head>
<body>

<!-- header starts -->
<header class="header">

    <a href="/index.php" class="logo">
        <img src="/Other/image/iconlogo.png" alt="logo" class="logo-img">
    </a>

    <nav class="navbar">
        <a href="/index.php">Trang Chủ</a>
        <a href="/View/categories-list.php">Danh Mục</a>
        <a href="/View/product-list.php">Sản Phẩm</a>
        <a href="/View/invoices.php" class="active">Hóa Đơn</a>
        <?php if (!empty($_SESSION['is_admin'])): ?>
            <a href="/View/manage-products.php">Quản lý sản phẩm</a>
        <?php endif; ?>
    </nav>

    <div class="icons">
        <div class="fas fa-bars" id="menu-btn"></div>
        <div class="fas fa-search" id="search-btn"></div>
        <div class="fas fa-shopping-cart" id="cart-btn"></div>
        <div class="fas fa-user" id="login-btn"></div>
    </div>
<!-- search-form starts -->
    <form action="/index.php" method="GET" class="search-form">
        <input type="search" id="search-box" name="search_query" placeholder="Tìm Kiếm ... ">
        <button type="submit" class="search-submit fas fa-search" aria-label="Tìm kiếm"></button>
    </form>
<!-- search-form ends -->
 
<!-- shopping-cart starts  -->    
<div class="shopping-cart">
    <?php 
    $total = 0;
    if (!empty($_SESSION['cart'])):
        foreach ($_SESSION['cart'] as $c):
            $total += $c['gia'] * $c['quantity'];
    ?>
    <div class="box">
        <a href="/Controller/processCart.php?action=remove&id=<?php echo $c['id']; ?>" class="fas fa-trash"></a>
        <img src="/Other/image/<?php echo $c['anh']; ?>" alt="">
        <div class="content">
            <h3><?php echo $c['ten']; ?></h3>
            <span class="price"><?php echo number_format($c['gia']); ?> VNĐ</span>
            <span class="quantity">SL: <?php echo $c['quantity']; ?></span>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
        <p style="padding:10px;">Giỏ hàng trống!</p>
    <?php endif; ?>
    <div class="checkout-area">
        <div class="total"> Tổng: <?php echo number_format($total); ?> VNĐ </div>
        <a href="/Controller/checkout.php" class="btn">Thanh toán</a>
    </div>
</div>
<!-- shopping-cart ends  -->

<!-- login-form starts -->
<?php if (empty($hide_user_popup)): ?>
    <?php if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']): ?>
        <div class="login-form active" id="user-logout-menu">
            <h3>Xin chào, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Bạn'); ?>!</h3>
            <a href="/Controller/processLogout.php" class="btn">Đăng Xuất</a>
        </div>
    <?php else: ?>
        <form action="/Controller/processLogin.php" method="POST" class="login-form">
            <h3>Đăng Nhập</h3>
            <?php 
            if (isset($_SESSION['login_error'])) {
                echo '<p style="color:red; margin-bottom: 10px; font-size: 1.5rem;">' . htmlspecialchars($_SESSION['login_error']) . '</p>';
                unset($_SESSION['login_error']); 
            }
            ?>
            <input type="email" name="email" placeholder="your email" class="box" required>
            <input type="password" name="password" placeholder="your password" class="box" required>
            <p>Không Có Tài Khoản <a href="#">Đăng Kí Ngay</a></p>
            <input type="submit" name="login_submit" value="Xác Nhận" class="btn">
        </form>
    <?php endif; ?>
<?php endif; ?>
<!-- login-form ends -->

</header>
<!-- header ends -->

<section class="manage-wrap invoice-wrap">
    <h1>Danh sách hóa đơn</h1>
    <?php if (!empty(
        $error_msg
    )): ?>
        <div class="msg" style="background:#ffecec;color:#900;">Lỗi: <?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>
    <?php if (empty($invoices)): ?>
        <p>Không có hóa đơn nào.</p>
    <?php else: ?>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ngày</th>
                    <th style="text-align:right;">Tổng</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td><?php echo htmlspecialchars($inv['id']); ?></td>
                    <td><?php echo htmlspecialchars($inv['created_at']); ?></td>
                    <td class="right"><?php echo number_format($inv['total'], 0, '.', ','); ?> VNĐ</td>
                    <td><a href="/View/invoice-detail.php?id=<?php echo $inv['id']; ?>">Xem</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

</body>
</html>
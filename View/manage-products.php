<?php
session_start();
require_once __DIR__ . '/../Controller/ProductsController.php';
$ctl = new ProductController();
$products = $ctl->getProducts();

// Only allow admin users to access this page
if (empty($_SESSION['is_admin'])) {
    header('Location: /index.php');
    exit;
}

$editProduct = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $editProduct = $ctl->getProductById($edit_id);
}

$admin_msg = null;
if (isset($_SESSION['admin_msg'])) {
    $admin_msg = $_SESSION['admin_msg'];
    unset($_SESSION['admin_msg']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/Other/css/style.css">
    <!-- All manage-products styles moved into /Other/css/style.css -->
</head>
<body>
<header class="header">
    <a href="/index.php" class="logo">
        <img src="/Other/image/iconlogo.png" alt="logo" class="logo-img">
    </a>

    <nav class="navbar">
        <a href="/index.php">Trang Chủ</a>
        <a href="/View/categories-list.php">Danh Mục</a>
        <a href="/View/product-list.php">Sản Phẩm</a>
        <?php if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] && empty($_SESSION['is_admin'])): ?>
            <a href="/View/invoices.php">Hóa Đơn</a>
        <?php endif; ?>
        <a href="/View/manage-products.php">Quản Lý Sản Phẩm</a>
    </nav>

    <div class="icons">
        <div class="fas fa-search" id="search-btn"></div>
        <div class="fas fa-shopping-cart" id="cart-btn"></div>
        <div class="fas fa-user" id="login-btn"></div>
    </div>
</header>

<section class="manage-wrap">
    <h2>Quản lý sản phẩm</h2>

    <?php if ($admin_msg): ?>
        <div class="msg"><?php echo htmlspecialchars($admin_msg); ?></div>
    <?php endif; ?>

    <h3><?php echo $editProduct ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm mới'; ?></h3>
    <form action="/Controller/processProduct.php?action=<?php echo $editProduct ? 'edit' : 'add'; ?>" method="POST" enctype="multipart/form-data" class="form-inline">
        <?php if ($editProduct): ?>
            <input type="hidden" name="id" value="<?php echo (int)$editProduct->id; ?>">
        <?php endif; ?>
        <input type="text" name="ten" placeholder="Tên sản phẩm" value="<?php echo $editProduct ? htmlspecialchars($editProduct->ten) : ''; ?>" required>
        <input type="number" name="gia" placeholder="Giá" value="<?php echo $editProduct ? (int)$editProduct->gia : ''; ?>" required>
        <input type="file" name="anh" accept="image/*">
        <input type="number" name="idDanhmuc" placeholder="ID Danh mục" value="<?php echo $editProduct ? (int)$editProduct->idDanhmuc : ''; ?>">
        <button class="btn" type="submit"><?php echo $editProduct ? 'Cập nhật' : 'Thêm'; ?></button>
        <?php if ($editProduct): ?>
            <a href="/View/manage-products.php" class="btn btn-cancel">Hủy</a>
        <?php endif; ?>
    </form>

    <hr class="sep">

    <h3>Danh sách sản phẩm</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tên</th>
                <th>Giá</th>
                <th>ID Danh mục</th>
                <th class="right">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo (int)$p->id; ?></td>
                    <td><?php if (!empty($p->anh)): ?><img class="thumb" src="/Other/image/<?php echo htmlspecialchars($p->anh); ?>" alt=""><?php endif; ?></td>
                    <td><?php echo htmlspecialchars($p->ten); ?></td>
                    <td><?php echo number_format($p->gia); ?></td>
                    <td><?php echo htmlspecialchars($p->idDanhmuc ?? ''); ?></td>
                    <td class="right">
                        <a class="btn small-btn" href="/View/manage-products.php?edit_id=<?php echo (int)$p->id; ?>">Sửa</a>
                        <a class="btn small-btn btn-delete" href="/Controller/processProduct.php?action=delete&id=<?php echo (int)$p->id; ?>" onclick="return confirm('Xóa sản phẩm này?')">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</section>

</body>
</html>
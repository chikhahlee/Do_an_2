<?php
session_start();
require_once __DIR__ . '/../Controller/ProductsController.php';

$ctl = new ProductController();

$products = [];
$heading = "Danh sách <span>sản phẩm</span>";
$search_query = '';

// --- LẤY THÔNG BÁO LỖI ĐĂNG NHẬP / THANH TOÁN ---
$login_alert_message = null;
if (isset($_SESSION['login_alert'])) {
    $login_alert_message = $_SESSION['login_alert'];
    unset($_SESSION['login_alert']);
}
$checkout_error_message = null;
if (isset($_SESSION['checkout_error'])) {
    $checkout_error_message = $_SESSION['checkout_error'];
    unset($_SESSION['checkout_error']);
}
// --- END LẤY THÔNG BÁO ---

// --- LOGIC XỬ LÝ TÌM KIẾM ---
if (isset($_GET['search_query']) && !empty(trim($_GET['search_query']))) {
    $search_query = htmlspecialchars(trim($_GET['search_query']));
    $products = $ctl->searchProducts($search_query);
    $heading = "Kết Quả Tìm Kiếm cho: <span>\"{$search_query}\"</span>";
    
// --- LOGIC LỌC THEO DANH MỤC ---
} else {
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
    
    if ($category_id > 0) {
        $products = $ctl->getProductsByCategory($category_id);
    } else {
        $products = $ctl->getProducts();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/Other/image/iconlogo.png">
    <title>Fresh Phố</title>
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/Other/css/style.css">

    <style>
        .shopping-cart {
            max-height: 400px;
            overflow-y: auto;
            padding-bottom: 1rem;
        }
        .login-alert {
            position: fixed; top: 0; left: 0; right: 0; 
            z-index: 10000; padding: 1.5rem; 
            text-align: center; font-size: 1.8rem; 
            background: #ffe3e3; 
            color: #d11; 
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.2);
            font-weight: 600;
        }
    </style>
</head>
<body>
<!-- thông báo đăng nhập / lỗi thanh toán -->
<?php if ($login_alert_message): ?>
<div class="login-alert">
    <?php echo htmlspecialchars($login_alert_message); ?>
</div>
<script>
    // Tự động đóng thông báo sau 5 giây
    setTimeout(function() {
        const alertBox = document.querySelector('.login-alert');
        if (alertBox) {
            alertBox.style.display = 'none';
        }
    }, 5000); 
</script>
<?php endif; ?>

<?php if (!empty($checkout_error_message)): ?>
<div class="login-alert" style="background:#ffecec; color:#900;">
    <?php echo htmlspecialchars($checkout_error_message); ?>
</div>
<script>
    setTimeout(function() {
        const alertBox = document.querySelector('.login-alert[style]');
        if (alertBox) alertBox.style.display = 'none';
    }, 7000);
</script>
<?php endif; ?>

<!-- header starts -->
<header class="header">

    <a href="/index.php" class="logo">
        <img src="/Other/image/iconlogo.png" style="width:70px; height:70px; margin:0px 50px;">
    </a>

    <nav class="navbar">
        <a href="/index.php">Trang Chủ</a>
        <a href="/View/categories-list.php">Danh Mục</a>
        <a href="/View/product-list.php">Sản Phẩm</a>
        <?php if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] && empty($_SESSION['is_admin'])): ?>
            <a href="/View/invoices.php">Hóa Đơn</a>
        <?php endif; ?>
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
    <form action="/View/product-list.php" method="GET" class="search-form">
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
<!-- login-form ends -->

</header>
<!-- header ends -->

<!-- home section starts -->
<section class="home" id="home">
    <div class="content">
        <h3>Fresh <span>phố</span></h3>
        <p>nơi tụ họp của những loại thực phẩm tươi ngon, chất lượng vượt trội với mức giá vô cùng hợp lí</p>
    </div>
</section>
<!-- home section ends -->

<!-- products section starts -->
<section class="products" id="products">
    <h1 class="heading"><?php echo $heading; ?></h1> 
    <div class="swiper product-slider">
        <div class="swiper-wrapper">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $p): ?>
                <div class="swiper-slide box">
                    <img src="/Other/image/<?php echo $p->anh; ?>">
                    <h3><?php echo $p->ten; ?></h3>
                    <div class="price"><?php echo number_format($p->gia); ?> VNĐ</div>
                        <a href="/Controller/processCart.php?action=add&id=<?php echo $p->id; ?>" class="btn">Thêm vào giỏ</a>
                    </div>
                <?php endforeach;?>
            <?php else: ?>
                <p>
                <?php if (!empty($search_query)): ?>
                    Không tìm thấy sản phẩm nào khớp với từ khóa "<?php echo $search_query; ?>".
                <?php else: ?>
                    Không có sản phẩm nào.
                <?php endif; ?>
                </p>
            <?php endif;?>
        </div>
    </div>
</section>
<!-- products section ends -->

<!-- footer section starts -->
    <section class="footer">
      <div class="box-container">
        <div class="box">
          <img src="/Other/image/iconlogo.png"  style="position:static ;width:70px; height:70px; margin:0px 50px" alt="logo"></a>
          <p>
          nếu có vấn đề gì vui lòng liên hệ với chúng tôi qua các kênh dưới đây
          </p>
          <div class="share">
            <a href="https://www.facebook.com/" class="fab fa-facebook-f"></a>
            <a href="https://x.com/" class="fab fa-twitter"></a>
            <a href="https://www.instagram.com/" class="fab fa-instagram"></a>
            <a href="https://www.linkedin.com/" class="fab fa-linkedin"></a>
          </div>
        </div>

        <div class="box">
          <h3>Thông tin liên hệ</h3>
          <a href="#" class="links">
            <i class="fas fa-phone"></i> 0839651104
          </a>
          <a href="#" class="links">
            <i class="fas fa-envelope"></i> khanhlc.24itb@vku.udn.vn
          </a>
          <a href="#" class="links">
            <i class="fas fa-map-marker-alt"></i> đà nẵng, việt nam
          </a>
        </div>
        
        <div class="box">
          <h3>liên kết nhanh</h3>
          <a href="/index.php" class="links">
            <i class="fas fa-arrow-right"></i> trang chủ
          </a>
          <a href="/View/product-list.php" class="links">
            <i class="fas fa-arrow-right"></i> sản phẩm
          </a>
          <a href="/View/categories-list.php" class="links">
            <i class="fas fa-arrow-right"></i> danh mục
          </a>
        </div>
      </div>
    </section>
    <!-- footer section ends -->
    <script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>
    <!-- custom js file link  -->
    <script src="/Other/js/script.js"></script>
  </body>
</html>
let searchForm = document.querySelector(".search-form");
let shoppingCart = document.querySelector(".shopping-cart");
let loginForm = document.querySelector(".login-form");
let navbar = document.querySelector(".navbar");

// Hàm bổ trợ để gán sự kiện an toàn
const safe = (el, fn) => {
  if (!el) return;
  el.onclick = (e) => {
    e.preventDefault(); // Ngăn chặn nhảy trang nếu là thẻ link
    fn();
  };
};

// Bật/tắt Tìm kiếm
safe(document.querySelector("#search-btn"), () => {
  searchForm.classList.toggle("active");
  shoppingCart.classList.remove("active");
  loginForm.classList.remove("active");
  navbar.classList.remove("active");
});

// Bật/tắt Giỏ hàng
safe(document.querySelector("#cart-btn"), () => {
  shoppingCart.classList.toggle("active");
  searchForm.classList.remove("active");
  loginForm.classList.remove("active");
  navbar.classList.remove("active");
});

// Bật/tắt Đăng nhập
safe(document.querySelector("#login-btn"), () => {
  loginForm.classList.toggle("active");
  searchForm.classList.remove("active");
  shoppingCart.classList.remove("active");
  navbar.classList.remove("active");
});

// Bật/tắt Menu (Navbar)
safe(document.querySelector("#menu-btn"), () => {
  navbar.classList.toggle("active");
  searchForm.classList.remove("active");
  shoppingCart.classList.remove("active");
  loginForm.classList.remove("active");
});

// Đóng tất cả khi cuộn chuột để tránh bị đè nội dung
window.onscroll = () => {
  searchForm.classList.remove("active");
  shoppingCart.classList.remove("active");
  loginForm.classList.remove("active");
  navbar.classList.remove("active");
};

// Cấu hình Swiper cho Sản phẩm
var productSwiper = new Swiper(".product-slider", {
    loop: false,
    spaceBetween: 20,
    grabCursor: true,
    centeredSlides: false,
    breakpoints: {
        0: {
            slidesPerView: 1,
        },
        480: {
            slidesPerView: 2,
        },
        768: {
            slidesPerView: 3,
        },
        1024: {
            slidesPerView: 4,
        },
    },
});

// Đóng navbar khi click vào link điều hướng
document.querySelectorAll('.navbar a').forEach(link => {
  link.onclick = () => {
    navbar.classList.remove('active');
  };
});
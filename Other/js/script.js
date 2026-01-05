let searchForm = document.querySelector(".search-form");
let icons = document.querySelector(".icons");

const safe = (el, fn) => {
  if (!el) return;
  el.onclick = fn;
};

safe(document.querySelector("#search-btn"), () => {
  if (searchForm) searchForm.classList.toggle("active");
  if (typeof shoppingCart !== "undefined" && shoppingCart)
    shoppingCart.classList.remove("active");
  if (typeof loginForm !== "undefined" && loginForm)
    loginForm.classList.remove("active");
  if (typeof navbar !== "undefined" && navbar)
    navbar.classList.remove("active");
  // on small screens, collapse icons list when opening search
  if (icons) icons.classList.remove("expanded");
});

let shoppingCart = document.querySelector(".shopping-cart");

safe(document.querySelector("#cart-btn"), () => {
  if (shoppingCart) shoppingCart.classList.toggle("active");
  if (searchForm) searchForm.classList.remove("active");
  if (typeof loginForm !== "undefined" && loginForm)
    loginForm.classList.remove("active");
  if (typeof navbar !== "undefined" && navbar)
    navbar.classList.remove("active");
  if (icons) icons.classList.remove("expanded");
});

let loginForm = document.querySelector(".login-form");

safe(document.querySelector("#login-btn"), () => {
  if (loginForm) loginForm.classList.toggle("active");
  if (searchForm) searchForm.classList.remove("active");
  if (shoppingCart) shoppingCart.classList.remove("active");
  if (typeof navbar !== "undefined" && navbar)
    navbar.classList.remove("active");
  if (icons) icons.classList.remove("expanded");
});

let navbar = document.querySelector(".navbar");

safe(document.querySelector("#menu-btn"), () => {
  // toggle navbar (for nav links) and toggle icons visibility on small screens
  if (navbar) navbar.classList.toggle("active");
  if (icons) icons.classList.toggle("expanded");
  if (searchForm) searchForm.classList.remove("active");
  if (shoppingCart) shoppingCart.classList.remove("active");
  if (loginForm) loginForm.classList.remove("active");
});

window.onscroll = () => {
  if (searchForm) searchForm.classList.remove("active");
  if (shoppingCart) shoppingCart.classList.remove("active");
  if (loginForm) loginForm.classList.remove("active");
  if (navbar) navbar.classList.remove("active");
  if (icons) icons.classList.remove("expanded");
};
// product
var productSwiper = new Swiper(".product-slider", {
  loop: false,
  spaceBetween: 20,
  autoplay: {
    delay: 7500,
    disableOnInteraction: false,
  },
  centeredSlides: true,
  centeredSlides: false,
  breakpoints: {
    0: {
      slidesPerView: 1,
    },
    768: {
      slidesPerView: 2,
    },
    1020: {
      slidesPerView: 3,
    },
  },
});

<?php
class CartController {

    public function addToCart($productId, $quantity) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function removeItem($productId) {
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
    }

    public function updateQuantity($productId, $quantity) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }

    public function getCart() {
        return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    }

    public function clearCart() {
        unset($_SESSION['cart']);
    }
}

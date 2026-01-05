<?php
class CartModel {

    public function addToCart($product) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $id = $product->id;

        if (!isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] = [
                'ten' => $product->ten,
                'gia' => $product->gia,
                'quantity' => 1,
                'anh' => $product->anh,
            ];
        } else {
            $_SESSION['cart'][$id]['quantity']++;
        }
    }

    public function removeFromCart($id) {
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
    }

    public function clearCart() {
        unset($_SESSION['cart']);
    }

    public function getCart() {
        return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    }
}
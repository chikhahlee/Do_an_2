<?php
require_once __DIR__ . '/../Model/ProductModel.php';

class ProductController {
    private $model;

    public function __construct() {
        $this->model = new ProductModel();
    }

    public function getProducts() {
        return $this->model->getAllProducts();
    }

    public function getProductsByCategory($cat_id) {
        return $this->model->getProductsByCategory($cat_id);
    }

    public function getProductById($id) {
        return $this->model->getProductById($id);
    }

    public function searchProducts($query) {
        return $this->model->searchProducts($query);
    }
}
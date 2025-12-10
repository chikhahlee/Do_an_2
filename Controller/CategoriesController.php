<?php
require_once __DIR__ . '/../Model/CategoriesModel.php';

class CategoriesController {
    private $model;

    public function __construct() {
        $this->model = new CategoriesModel();
    }

    public function getCategories() {
        return $this->model->getAllCategories();
    }
}

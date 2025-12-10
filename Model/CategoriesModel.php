<?php
class CategoriesModel {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli("localhost", "root", "", "doan_2");
    }

    public function getAllCategories() {
        $sql = "SELECT * FROM danhmuc";
        $result = $this->conn->query($sql);
        $categories = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_object()) {
                $categories[] = $row;
            }
        }
        return $categories;
    }
}

<?php
class ProductModel {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli("localhost", "root", "", "doan_2");
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8");
    }

    public function getAllProducts() {
        $sql = "SELECT * FROM sanpham";
        $result = $this->conn->query($sql);
        $products = [];
        if ($result) {
            while ($row = $result->fetch_object()) {
                $products[] = $row;
            }
            $result->free();
        }
        return $products;
    }

    public function getProductsByCategory($cat_id) {
        $products = [];
        $sql = "SELECT * FROM sanpham WHERE idDanhmuc = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $cat_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_object()) {
                $products[] = $row;
            }
            $res->free();
            $stmt->close();
        }
        return $products;
    }

    public function getProductById($id) {
        $sql = "SELECT * FROM sanpham WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $product = ($res && $res->num_rows > 0) ? $res->fetch_object() : null;
            if ($res) $res->free();
            $stmt->close();
            return $product;
        }
        return null;
    }
    
    public function searchProducts($query) {
        $products = [];
        
        $search_param = "%" . $query . "%"; 
        
        // Truy vấn SQL tìm kiếm theo cột 'ten'
        $sql = "SELECT * FROM sanpham WHERE ten LIKE ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $search_param); 
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_object()) {
                $products[] = $row;
            }
            $res->free();
            $stmt->close();
        }
        return $products;
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
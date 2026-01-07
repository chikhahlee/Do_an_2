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

    // hiển thị tất cả sản phẩm
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

    // lấy sản phẩm theo danh mục
    public function getProductsByCategory($cat_id) {
        $products = [];
        $cat_id = (int)$cat_id;

        $sql = "SELECT * FROM sanpham WHERE idDanhmuc = $cat_id";
        $result = $this->conn->query($sql);

        if ($result) {
            while ($row = $result->fetch_object()) {
                $products[] = $row;
            }
            $result->free();
        }
        return $products;
    }

    // lấy sản phẩm theo id
    public function getProductById($id) {
        $id = (int)$id;

        $sql = "SELECT * FROM sanpham WHERE id = $id LIMIT 1";
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_object();
            $result->free();
            return $product;
        }
        return null;
    }

    // tìm kiếm sản phẩm
    public function searchProducts($query) {
        $products = [];
        $query = $this->conn->real_escape_string($query);

        $sql = "SELECT * FROM sanpham WHERE ten LIKE '%$query%'";
        $result = $this->conn->query($sql);

        if ($result) {
            while ($row = $result->fetch_object()) {
                $products[] = $row;
            }
            $result->free();
        }
        return $products;
    }

    // thêm sản phẩm
    public function addProduct($ten, $gia, $anh = null, $idDanhmuc = null) {
        $ten = $this->conn->real_escape_string($ten);
        $gia = (int)$gia;
        $idDanhmuc = $idDanhmuc !== null ? (int)$idDanhmuc : "NULL";
        $anh = $anh ? "'" . $this->conn->real_escape_string($anh) . "'" : "NULL";

        $sql = "INSERT INTO sanpham (ten, gia, anh, idDanhmuc)
                VALUES ('$ten', $gia, $anh, $idDanhmuc)";

        $res = $this->conn->query($sql);
        return $res ? $this->conn->insert_id : false;
    }

    // update sản phẩm
    public function updateProduct($id, $ten, $gia, $anh = null, $idDanhmuc = null) {
        $id = (int)$id;
        $ten = $this->conn->real_escape_string($ten);
        $gia = (int)$gia;
        $idDanhmuc = $idDanhmuc !== null ? (int)$idDanhmuc : "NULL";

        if ($anh !== null) {
            $anh = $this->conn->real_escape_string($anh);
            $sql = "UPDATE sanpham 
                    SET ten = '$ten', gia = $gia, anh = '$anh', idDanhmuc = $idDanhmuc
                    WHERE id = $id";
        } else {
            $sql = "UPDATE sanpham 
                    SET ten = '$ten', gia = $gia, idDanhmuc = $idDanhmuc
                    WHERE id = $id";
        }

        return $this->conn->query($sql);
    }

    // xóa sản phẩm
    public function deleteProduct($id) {
        $id = (int)$id;
        $sql = "DELETE FROM sanpham WHERE id = $id";
        return $this->conn->query($sql);
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

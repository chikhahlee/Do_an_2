<?php
class InvoiceModel {

    private function getConnection() {
        $conn = new mysqli("localhost", "root", "", "doan_2");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8");
        return $conn;
    }

    // lấy danh sách hóa đơn 
    public function getInvoicesByUserOrSession($user_id = null, $session_id = null) {
        $conn = $this->getConnection();

        if ($user_id && $user_id != 0) {
            $user_id = (int)$user_id;
            $sql = "
                SELECT *
                FROM invoices
                WHERE user_id = $user_id
                ORDER BY created_at DESC
            ";
        } else {
            $session_id = $conn->real_escape_string($session_id);
            $sql = "
                SELECT *
                FROM invoices
                WHERE session_id = '$session_id'
                ORDER BY created_at DESC
            ";
        }

        $res = $conn->query($sql);
        if (!$res) {
            throw new Exception("DB Error: " . $conn->error);
        }

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }

        $conn->close();
        return $data;
    }

    // lấy chi tiết hóa đơn
    public function getInvoiceById($invoice_id) {
        $conn = $this->getConnection();
        $invoice_id = (int)$invoice_id;

        $sqlInvoice = "
            SELECT *
            FROM invoices
            WHERE id = $invoice_id
        ";
        $res = $conn->query($sqlInvoice);
        if (!$res) {
            throw new Exception("DB Error: " . $conn->error);
        }

        $invoice = $res->fetch_assoc();
        if (!$invoice) {
            return null;
        }

        $sqlItems = "
            SELECT *
            FROM invoice_items
            WHERE invoice_id = $invoice_id
        ";
        $res2 = $conn->query($sqlItems);
        if (!$res2) {
            throw new Exception("DB Error: " . $conn->error);
        }

        $items = [];
        while ($row = $res2->fetch_assoc()) {
            $items[] = $row;
        }

        $conn->close();

        return [
            'invoice' => $invoice,
            'items'   => $items
        ];
    }
}

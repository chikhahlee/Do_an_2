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

    // Ensure hoadon tables exist; if not, try to create them from the SQL migration
    private function ensureTablesExist($conn) {
        $dbRow = $conn->query("SELECT DATABASE() AS db");
        if (!$dbRow) return;
        $db = $dbRow->fetch_object()->db;
        $dbEsc = $conn->real_escape_string($db);

        $res = $conn->query("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = '$dbEsc' AND table_name IN ('hoadon','hoadon_items')");
        if ($res) {
            $row = $res->fetch_assoc();
            // if either table is missing (less than 2 found), run creation SQL
            if (intval($row['cnt']) < 2) {
                $sqlFile = __DIR__ . '/../Other/sql/create_invoices.sql';
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    $stmts = array_filter(array_map('trim', explode(';', $sql)));
                    foreach ($stmts as $st) {
                        if (empty($st)) continue;
                        $conn->query($st);
                    }
                }
            }
        }
    }

    public function getInvoicesByUserOrSession($user_id = null, $session_id = null) {
        $conn = $this->getConnection();
        // try to detect which table set exists: prefer 'invoices'/'invoice_items' if present
        $hasInvoices = $conn->query("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'invoices'");
        if ($hasInvoices && intval($hasInvoices->fetch_assoc()['cnt']) > 0) {
            $mainTable = 'invoices';
        } else {
            $mainTable = 'hoadon';
        }

        if ($user_id && $user_id != 0) {
            $sql = "SELECT * FROM {$mainTable} WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new \Exception('DB error: ' . $conn->error);
            }
            $stmt->bind_param("i", $user_id);
        } else {
            $sql = "SELECT * FROM {$mainTable} WHERE session_id = ? ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new \Exception('DB error: ' . $conn->error);
            }
            $stmt->bind_param("s", $session_id);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $invoices = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        return $invoices;
    }

    public function getInvoiceById($invoice_id) {
        $conn = $this->getConnection();

        // choose which table naming is present
        $hasInvoices = $conn->query("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'invoices'");
        if ($hasInvoices && intval($hasInvoices->fetch_assoc()['cnt']) > 0) {
            $mainTable = 'invoices';
            $itemTable = 'invoice_items';
            $itemFk = 'invoice_id';
        } else {
            $mainTable = 'hoadon';
            $itemTable = 'hoadon_items';
            $itemFk = 'hoadon_id';
        }

        $sql = "SELECT * FROM {$mainTable} WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new \Exception('DB error: ' . $conn->error);
        }
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $invoice = $res->fetch_assoc();
        $stmt->close();

        $sql2 = "SELECT * FROM {$itemTable} WHERE {$itemFk} = ?";
        $stmt2 = $conn->prepare($sql2);
        if (!$stmt2) {
            throw new \Exception('DB error: ' . $conn->error);
        }
        $stmt2->bind_param("i", $invoice_id);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $items = $res2->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();
        $conn->close();

        return ['invoice' => $invoice, 'items' => $items];
    }
}

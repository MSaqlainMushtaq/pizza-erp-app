<?php
include_once __DIR__ . '/../config/db.php';

function getAllDeals($conn) {
    $sql = "SELECT * FROM deals ORDER BY created_at DESC";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function getActiveDeals($conn) {
    $sql = "SELECT * FROM deals WHERE status='active' ORDER BY created_at DESC";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function getDealById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM deals WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getDealItems($conn, $deal_id) {
    $stmt = $conn->prepare("SELECT di.*, p.name FROM deal_items di 
                            JOIN products p ON di.product_id = p.id 
                            WHERE deal_id = ?");
    $stmt->bind_param("i", $deal_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function addDeal($conn, $name, $price, $items) {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO deals (name, price, status) VALUES (?, ?, 'active')");
        $stmt->bind_param("sd", $name, $price);
        $stmt->execute();
        $deal_id = $conn->insert_id;

        $stmt2 = $conn->prepare("INSERT INTO deal_items (deal_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($items as $item) {
            $stmt2->bind_param("iii", $deal_id, $item['product_id'], $item['quantity']);
            $stmt2->execute();
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function updateDeal($conn, $id, $name, $price, $items) {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE deals SET name=?, price=? WHERE id=?");
        $stmt->bind_param("sdi", $name, $price, $id);
        $stmt->execute();

        $conn->query("DELETE FROM deal_items WHERE deal_id = $id");

        $stmt2 = $conn->prepare("INSERT INTO deal_items (deal_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($items as $item) {
            $stmt2->bind_param("iii", $id, $item['product_id'], $item['quantity']);
            $stmt2->execute();
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function archiveDeal($conn, $id) {
    // Instead of deleting, mark as inactive
    $stmt = $conn->prepare("UPDATE deals SET status='inactive' WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>

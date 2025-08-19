<?php
require_once '../config/db.php';

if (!isset($_GET['id'], $_GET['type'])) {
    echo json_encode(['price' => 0]);
    exit;
}

$id = intval($_GET['id']);
$type = $_GET['type'];

if ($type === 'product') {
    $res = mysqli_query($conn, "SELECT price FROM products WHERE id = $id");
} else {
    $res = mysqli_query($conn, "SELECT price FROM deals WHERE id = $id");
}

$row = mysqli_fetch_assoc($res);
echo json_encode(['price' => $row ? $row['price'] : 0]);
?>

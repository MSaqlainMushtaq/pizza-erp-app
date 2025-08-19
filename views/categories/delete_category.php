<?php
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../controllers/categories.php';

$id = $_GET['id'];
deleteCategory($conn, $id);
header("Location: index.php");
?>

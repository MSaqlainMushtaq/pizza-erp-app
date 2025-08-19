<?php
include_once '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

// Check if ingredient is used in sales
$checkQuery = "
    SELECT COUNT(*) as total 
    FROM ingredient_stock_log 
    WHERE ingredient_id = $id
";
$result = mysqli_query($conn, $checkQuery);
$row = mysqli_fetch_assoc($result);

if ($row['total'] > 0) {
    // Used in sales → do not delete
    echo "<script>alert('This ingredient cannot be deleted because it is used in sales.'); window.location.href='index.php';</script>";
    exit();
}

// Not used in sales → delete immediately
mysqli_query($conn, "DELETE FROM ingredients WHERE id = $id");

// Redirect to index without any message
header("Location: index.php");
exit();
?>

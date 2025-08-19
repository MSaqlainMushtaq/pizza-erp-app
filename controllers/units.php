<?php
include __DIR__ . '/../config/db.php';

// Add unit
if (isset($_POST['add_unit'])) {
    $unit_name = $_POST['unit_name'];   // ✅ Use same name
    $base_unit = $_POST['base_unit'];
    $factor = $_POST['conversion_factor'];

    $stmt = $conn->prepare("INSERT INTO units (unit_name, base_unit, conversion_factor) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $unit_name, $base_unit, $factor);
    $stmt->execute();

    header("Location: /pizza-erp-app/views/units/index.php");
    exit;
}

// Edit unit
if (isset($_POST['edit_unit'])) {
    $id = $_POST['id'];
    $unit_name = $_POST['unit_name'];  // ✅ Use same name
    $base_unit = $_POST['base_unit'];
    $factor = $_POST['conversion_factor'];

    $stmt = $conn->prepare("UPDATE units SET unit_name=?, base_unit=?, conversion_factor=? WHERE id=?");
    $stmt->bind_param("ssdi", $unit_name, $base_unit, $factor, $id);
    $stmt->execute();

    header("Location: /pizza-erp-app/views/units/index.php");
    exit;
}

// Delete unit
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    try {
        $stmt = $conn->prepare("DELETE FROM units WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: /pizza-erp-app/views/units/index.php");
        exit;
    } catch (mysqli_sql_exception $e) {
        // Check if the error is due to foreign key constraint
        if ($e->getCode() == 1451) {
            echo "<script>
                alert('❌ This unit cannot be deleted because it is used in other records.');
                window.location.href = '/pizza-erp-app/views/units/index.php';
            </script>";
            exit;
        } else {
            throw $e; // rethrow if it’s another kind of error
        }
    }
}
?>

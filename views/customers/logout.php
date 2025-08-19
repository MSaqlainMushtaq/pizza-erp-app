<?php
session_start();
session_unset(); // Clear all session variables
session_destroy(); // Destroy the session

// Redirect to customer login page
header("Location: /pizza-erp-app/views/customers/login.php?logout=success");
exit();
?>

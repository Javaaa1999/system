<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "agrilink");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete product
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    $delete_sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: manage_products.php");
        exit();
    } else {
        echo "Error deleting product.";
    }
} else {
    echo "Product ID is missing.";
}

$conn->close();
?>

<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "agrilink");

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all products to display in a table
$products = $conn->query("
    SELECT products.*, categories.category_name, barangays.name AS barangay_name
    FROM products
    JOIN categories ON products.category_id = categories.id
    JOIN barangays ON products.barangay_id = barangays.id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
        }
        .add-product-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .add-product-btn:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #8A724A;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f4f4f4;
        }
        .product-img {
            width: 50px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Products</h2>

        <a href="add_product.php" class="add-product-btn">Add New Product</a>
        <a href="admin.php" class="add-product-btn">back</a>

        <h3>All Products</h3>
        <table>
            <thead>
                <tr>
                    <th>Product Image</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Barangay</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
    <?php while ($product = $products->fetch_assoc()) { ?>
        <tr>
            <td>
                <?php if (!empty($product['image_path'])) { ?>
                    <img src="<?php echo $product['image_path']; ?>" class="product-img" alt="Product Image">
                <?php } else { ?>
                    No Image
                <?php } ?>
            </td>
            <td><?php echo $product['product_name']; ?></td>
            <td>₱<?php echo number_format($product['price'], 2); ?></td>
            <td><?php echo $product['description']; ?></td>
            <td><?php echo $product['category_name']; ?></td>
            <td><?php echo $product['barangay_name']; ?></td>
            <td>
                <a href="update_product.php?id=<?php echo $product['id']; ?>" class="add-product-btn" style="background-color: #FF9800;">Update</a>
                <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="add-product-btn" style="background-color: #F44336;" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
            </td>
        </tr>
    <?php } ?>
</tbody>

        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>

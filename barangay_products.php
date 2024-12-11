<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "agrilink");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the selected barangay ID from the URL
$barangay_id = isset($_GET['barangay_id']) ? $_GET['barangay_id'] : 0;
$barangay_name = '';
$products = [];

// Fetch barangay name and products
if ($barangay_id) {
    // Get barangay name
    $barangay_query = "SELECT name FROM barangays WHERE id = ?";
    $stmt = $conn->prepare($barangay_query);
    $stmt->bind_param("i", $barangay_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $barangay_name = $result->fetch_assoc()['name'];
    }

    // Get products by barangay
    $product_query = "SELECT * FROM products WHERE barangay_id = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("i", $barangay_id);
    $stmt->execute();
    $products = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products in <?php echo htmlspecialchars($barangay_name); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #f8ffae, #43c6ac);
            margin: 0;
            padding: 0;
            color: #333;
        }
        h1 {
            text-align: center;
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            margin: 0;
            border-radius: 10px 10px 0 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
        }

        /* Product Card Styles */
        .product-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease-in-out;
            text-align: center;
        }
        .product-card img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        .product-title {
            font-size: 24px;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .product-description {
            font-size: 16px;
            color: #555;
            margin-bottom: 15px;
        }
        .product-price {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        /* Grid Layout for Products */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        /* Back Button */
        .back-btn {
            display: inline-block;
            margin: 20px 0;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-align: center;
            font-size: 14px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #45a049;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 95%;
            }
        }
    </style>
</head>
<body>

    <h1>Products in <?php echo htmlspecialchars($barangay_name); ?></h1>
    <a href="barangay.php" class="back-btn">&larr; Back</a>
    <div class="container">
        <?php if ($products->num_rows > 0) { ?>
            <div class="product-grid">
                <?php while ($product = $products->fetch_assoc()) { ?>
                    <div class="product-card">
                        <!-- Check if the image_path exists in the database -->
                        <?php if (!empty($product['image_path'])) { ?>
                            <img src="<?php echo $product['image_path']; ?>" alt="Product Image">
                        <?php } else { ?>
                            <img src="default-image.jpg" alt="No Image Available">
                        <?php } ?>
                        <div class="product-title"><?php echo $product['product_name']; ?></div>
                        <div class="product-description"><?php echo $product['description']; ?></div>
                        <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p>No products found in this barangay.</p>
        <?php } ?>
    </div>

</body>
</html>

<?php
$conn->close();
?>

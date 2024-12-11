<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "agrilink");

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the selected barangay ID and category from the URL
$barangay_id = isset($_GET['barangay_id']) ? $_GET['barangay_id'] : 0;
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$barangay_name = '';
$products = [];

// Fetch barangay name and products by category
if ($barangay_id) {
    // Get barangay name
    $barangay_query = "SELECT name FROM barangays WHERE id = $barangay_id";
    $barangay_result = $conn->query($barangay_query);
    if ($barangay_result->num_rows > 0) {
        $barangay_name = $barangay_result->fetch_assoc()['name'];
    }

    // Get products for the selected category
    if ($selected_category == 'All') {
        $product_query = "SELECT id, category, name, phone_number, seller_description, seller_information, image FROM products WHERE barangay_id = $barangay_id";
    } else {
        $product_query = "SELECT id, category, name, phone_number, seller_description, seller_information, image FROM products WHERE barangay_id = $barangay_id AND category = '$selected_category'";
    }
    $product_result = $conn->query($product_query);

    while ($row = $product_result->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'category' => $row['category'],
            'name' => $row['name'],
            'phone_number' => $row['phone_number'],
            'seller_description' => $row['seller_description'],
            'seller_information' => $row['seller_information'],
            'image' => $row['image']
        ];
    }
}

// Add new product to the barangay
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_name'], $_POST['phone_number'], $_POST['seller_description'], $_POST['seller_information'], $_FILES['product_image'])) {
    $product_name = $_POST['product_name'];
    $phone_number = $_POST['phone_number'];
    $seller_description = $_POST['seller_description'];
    $seller_information = $_POST['seller_information'];
    $category = $selected_category;

    // Handle image upload
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        // Insert new product into the database
        $stmt = $conn->prepare("INSERT INTO products (barangay_id, category, name, phone_number, seller_description, seller_information, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $barangay_id, $category, $product_name, $phone_number, $seller_description, $seller_information, $target_file);
        $stmt->execute();
        $stmt->close();

        // Reload the page to display the new product
        header("Location: category_products.php?barangay_id=" . $barangay_id . "&category=" . $selected_category);
        exit();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $selected_category; ?> Products from <?php echo $barangay_name; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #d4f7d4, #f7e1ac);
            margin: 0;
        }
        h1 {
            text-align: center;
            padding: 20px;
        }
        .product-container {
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .product-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 250px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        .product-info {
            margin: 10px 0;
            text-align: left;
            font-size: 14px;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 10px;
        }
        .delete-btn:hover {
            background-color: #e53935;
        }
        .back-btn {
            display: inline-block;
            margin: 20px auto;
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
        .form-container {
            margin: 20px auto;
            width: 40%;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
        .form-container input[type="text"], .form-container textarea, .form-container input[type="file"] {
            width: calc(100% - 20px);
            padding: auto;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-container button {
            background-color: #4CAF50;
            border: none;
            padding: 15px;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
            transition: background-color 0.3s;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <h1><?php echo $selected_category; ?> Products from <?php echo $barangay_name; ?></h1>

    <div class="product-container">
        <?php if (!empty($products)) { ?>
            <?php foreach ($products as $product) { ?>
                <div class="product-card">
                    <h2><?php echo $product['name']; ?></h2>
                    <?php if (!empty($product['image'])) { ?>
                        <img src="<?php echo $product['image']; ?>" alt="Product Image" style="width:100%; height:auto; border-radius: 10px;">
                    <?php } ?>
                    <div class="product-info">
                        <strong>Category:</strong> <?php echo $product['category']; ?><br>
                        <strong>Phone Number:</strong> <?php echo $product['phone_number']; ?><br>
                        <strong>Seller Description:</strong> <?php echo $product['seller_description']; ?><br>
                        <strong>Seller Information:</strong> <?php echo $product['seller_information']; ?><br>
                    </div>
                    <!-- Delete Product Button -->
                    <form action="category_products.php" method="GET">
                        <input type="hidden" name="barangay_id" value="<?php echo $barangay_id; ?>">
                        <input type="hidden" name="category" value="<?php echo $selected_category; ?>">
                        
                    </form>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No products found in this category for this barangay.</p>
        <?php } ?>
    </div>

    <!-- Form to add new product -->
    <div class="form-container">
        <h2>Add New Product to <?php echo $selected_category; ?> Category</h2>
        <form action="category_products.php?barangay_id=<?php echo $barangay_id; ?>&category=<?php echo $selected_category; ?>" method="POST" enctype="multipart/form-data">
            <input type="text" name="product_name" placeholder="Product Name" required>
            <input type="text" name="phone_number" placeholder="Phone Number" required>
            <textarea name="seller_description" placeholder="Seller Description" rows="3" required></textarea>
            <textarea name="seller_information" placeholder="Seller Information" rows="3" required></textarea>
            <input type="file" name="product_image" accept="image/*" required>
            <button type="submit">Add Product</button>
        </form>
    </div>

    <!-- Back to Barangay List Button -->
    <div style="text-align: center;">
        <a href="barangay_products.php?barangay_id=<?php echo $barangay_id; ?>" class="back-btn">&larr; Back</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>

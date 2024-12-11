<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "agrilink");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Directory to store uploaded images
$image_dir = "uploads/";

if (!is_dir($image_dir)) {
    mkdir($image_dir, 0777, true);  // Create the directory if it doesn't exist
}

// Add product with image
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $barangay_id = $_POST['barangay_id'];
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $image_name = basename($_FILES['product_image']['name']);
        $image_path = $image_dir . $image_name;
        
        // Move the uploaded file to the correct directory
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $image_path)) {
            // Prepare SQL to insert product with the image
            $stmt = $conn->prepare("INSERT INTO products (product_name, price, description, category_id, barangay_id, image_path) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sdssis", $product_name, $price, $description, $category_id, $barangay_id, $image_path);
            
            if ($stmt->execute()) {
                header("Location: manage_products.php");
                exit();
            } else {
                echo "Error adding product: " . $stmt->error;
            }
        } else {
            echo "Failed to upload image.";
        }
    } else {
        echo "Image upload failed or no image selected.";
    }
}

// Fetch categories and barangays for the dropdowns
$categories = $conn->query("SELECT * FROM categories");
$barangays = $conn->query("SELECT * FROM barangays");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
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
        .form-container {
            margin-top: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container img {
            display: block;
            margin: 0 auto;
            width: 100px;
            height: auto;
        }
        input[type="text"], input[type="number"], textarea, select, input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add Product</h2>

        <!-- Add Product Form -->
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="product_name" placeholder="Enter Product Name" required>
                <input type="number" name="price" step="0.01" placeholder="Enter Product Price" required>
                <textarea name="description" placeholder="Enter Product Description" required></textarea>

                <!-- Category Dropdown -->
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php while ($category = $categories->fetch_assoc()) { ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
                    <?php } ?>
                </select>

                <!-- Barangay Dropdown -->
                <select name="barangay_id" required>
                    <option value="">Select Barangay</option>
                    <?php while ($barangay = $barangays->fetch_assoc()) { ?>
                        <option value="<?php echo $barangay['id']; ?>"><?php echo $barangay['name']; ?></option>
                    <?php } ?>
                </select>

                <!-- Image Upload -->
                <input type="file" name="product_image" accept="image/*" required>

                <button type="submit" name="add_product">Add Product</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>

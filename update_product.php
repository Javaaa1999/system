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

// Fetch product details by ID
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $result = $conn->query("SELECT * FROM products WHERE id = $product_id");

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Product not found");
    }
}

// Fetch categories
$categories_result = $conn->query("SELECT id, category_name FROM categories");

// Fetch barangays
$barangays_result = $conn->query("SELECT id, name FROM barangays");

// Update product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $barangay_id = $_POST['barangay_id'];

    // Handle image upload
    $image_path = $product['image_path']; // Keep existing image by default

    if (isset($_FILES['image']['tmp_name']) && !empty($_FILES['image']['tmp_name'])) {
        $image_name = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);
        $new_image_name = "product_" . $product_id . "." . $image_extension;
        $upload_dir = "uploads/";

        // Create the uploads directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Move the uploaded image to the upload directory
        if (move_uploaded_file($image_tmp_name, $upload_dir . $new_image_name)) {
            // If a new image is uploaded, update the image path in the database
            $image_path = $upload_dir . $new_image_name;
            // Optionally, delete the old image from the server if a new one is uploaded
            if (file_exists($product['image_path']) && $product['image_path'] !== $image_path) {
                unlink($product['image_path']);
            }
        } else {
            echo "Error uploading the image.";
        }
    }

    // Update the product in the database
    $update_sql = "UPDATE products SET product_name = ?, price = ?, description = ?, category_id = ?, barangay_id = ?, image_path = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sdssssi", $product_name, $price, $description, $category_id, $barangay_id, $image_path, $product_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: manage_products.php");
        exit();
    } else {
        echo "Error updating product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <style>
        /* Basic styles for body and layout */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin-top: 50px;
        }

        /* Main container */
        .container {
            width: 70%;
            margin: 40px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Header Styling */
        h2 {
            text-align: center;
            color: #4CAF50;
            font-size: 28px;
            margin-bottom: 20px;
        }

        /* Form label styling */
        label {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            display: block;
        }

        /* Form input fields */
        input[type="text"], input[type="number"], textarea, input[type="file"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        /* Form textarea for description */
        textarea {
            height: 100px;
            resize: vertical;
        }

        /* Submit button styling */
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Image styling */
        img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }

        /* Back Button */
        .back-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #45a049;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .container {
                width: 90%;
            }

            input[type="submit"] {
                font-size: 16px;
            }

            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <h2>Update Product</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="product_name">Product Name:</label><br>
        <input type="text" id="product_name" name="product_name" value="<?php echo $product['product_name']; ?>" required><br><br>

        <label for="price">Price:</label><br>
        <input type="number" id="price" name="price" value="<?php echo $product['price']; ?>" required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description" required><?php echo $product['description']; ?></textarea><br><br>

        <label for="category_id">Category:</label><br>
        <select id="category_id" name="category_id" required>
            <?php while ($category = $categories_result->fetch_assoc()) { ?>
                <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                    <?php echo $category['category_name']; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <label for="barangay_id">Barangay:</label><br>
        <select id="barangay_id" name="barangay_id" required>
            <?php while ($barangay = $barangays_result->fetch_assoc()) { ?>
                <option value="<?php echo $barangay['id']; ?>" <?php echo ($barangay['id'] == $product['barangay_id']) ? 'selected' : ''; ?>>
                    <?php echo $barangay['name']; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <!-- Image upload input -->
        <label for="image">Product Image:</label><br>
        <?php if (!empty($product['image_path'])) { ?>
            <img src="<?php echo $product['image_path']; ?>" width="100" alt="Current Image"><br><br>
        <?php } ?>
        <input type="file" id="image" name="image"><br><br>

        <input type="submit" value="Update Product">
    </form>
</body>
</html>

<?php $conn->close(); ?>

<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Initialize cart if not already
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Database connection
$conn = new mysqli("localhost", "root", "", "agrilink");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the selected category from the URL
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';

// Get the search term from the form or URL
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch categories for the dropdown
$categories = $conn->query("SELECT * FROM categories");

// Fetch products based on selected category and search term
$products = [];
if ($category_id || $search_term) {
    // Build the query with both category filter and search term
    $sql = "SELECT p.*, b.name as barangay_name, c.category_name FROM products p
            JOIN barangays b ON p.barangay_id = b.id
            JOIN categories c ON p.category_id = c.id
            WHERE 1=1"; // Always true condition to build query dynamically
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
    }
    if ($search_term) {
        $sql .= " AND p.product_name LIKE ?"; // Match product name to search term
    }

    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    if ($category_id && $search_term) {
        $stmt->bind_param("is", $category_id, $search_term_param);
        $search_term_param = "%$search_term%";
    } elseif ($category_id) {
        $stmt->bind_param("i", $category_id);
    } elseif ($search_term) {
        $stmt->bind_param("s", $search_term_param);
        $search_term_param = "%$search_term%";
    }
    
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    // Fetch all products if no category or search term is specified
    $stmt = $conn->prepare("SELECT p.*, b.name as barangay_name, c.category_name FROM products p
                            JOIN barangays b ON p.barangay_id = b.id
                            JOIN categories c ON p.category_id = c.id");
    $stmt->execute();
    $products = $stmt->get_result();
}

// Add product to cart functionality
if (isset($_GET['add_to_cart'])) {
    $product_id = $_GET['add_to_cart'];

    // Check if product is already in the cart
    if (!in_array($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $product_id; // Add product to session cart
        echo "<script>alert('Product added to cart!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom right, #6CE28D, #F5F6F9, #F9EC52);
            background-attachment: fixed;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
        }

        .navbar {
            background-color: #C3F5C7;
            padding: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .navbar p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: black;
        }

        .navbar a {
            color: #4E6A4A;
            text-decoration: none;
            font-size: 16px;
            margin-left: 20px;
            font-weight: normal;
            transition: color 0.3s;
        }

        .navbar a:hover {
            color: #6F8B68;
        }

        .navbar-right {
            display: flex;
            align-items: center;
        }

        .search-bar {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            width: 200px;
        }

        /* Dropdown Style for Categories */
        .dropdown {
            position: relative;
            display: inline-block;
            margin-left: 20px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
            border-radius: 5px;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .container {
            width: 80%;
            margin: 20px auto;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: scale(1.05);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            background-color: #f3f3f3;
        }

        .product-title {
            font-size: 18px;
            color: #333;
            margin-top: 10px;
        }

        .product-price {
            font-size: 16px;
            color: #4CAF50;
            margin-top: 5px;
        }

        .product-barangay {
            font-size: 14px;
            color: #777;
            margin-top: 5px;
        }

        .product-buttons {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .product-buttons button {
            background-color: #4E6A4A;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .product-buttons button:hover {
            background-color: #6F8B68;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 30%;
            max-width: 650px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .modal-details {
            text-align: center;
        }

        .modal-details h2 {
            font-size: 24px;
            margin-top: 20px;
        }

        .modal-details p {
            font-size: 16px;
            color: #555;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-left">
            <p>AgriLink</p>
        </div>
        <div class="navbar-right">
            <input type="text" class="search-bar" placeholder="Search products" onkeyup="searchProducts()">
            <div class="dropdown">
                <a href="javascript:void(0)">Categories</a>
                <div class="dropdown-content">
                    <?php while ($category = $categories->fetch_assoc()) { ?>
                        <a href="?category_id=<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></a>
                    <?php } ?>
                </div>
            </div>
            <a href="barangay.php">Barangays</a>
            <a href="mycart.php">My Cart (<?php echo count($_SESSION['cart']); ?>)</a>
            <a href="profile.php">Profile</a>
            <a href="login.php">Logout</a>
        </div>
    </div>

    <!-- Product List -->
    <div class="container">
        <h2>Available Products</h2>
        <?php if ($products && $products->num_rows > 0) { ?>
            <div class="product-grid">
                <?php while ($product = $products->fetch_assoc()) { ?>
                    <div class="product-card" onclick="openModal(<?php echo $product['id']; ?>)">
                        <img src="<?php echo !empty($product['image_path']) ? $product['image_path'] : 'default-image.jpg'; ?>" alt="Product Image">
                        <div class="product-title"><?php echo $product['product_name']; ?></div>
                        <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                        <div class="product-barangay">Barangay: <?php echo $product['barangay_name']; ?></div>
                        <div class="product-buttons">
                            <a href="?add_to_cart=<?php echo $product['id']; ?>"><button>Add to Cart</button></a>
                            <a href="checkout.php"><button>Buy Now</button></a>
                        </div>
                    </div>

                    <!-- Modal for Product Preview -->
                    <div id="modal-<?php echo $product['id']; ?>" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="closeModal(<?php echo $product['id']; ?>)">&times;</span>
                            <img src="<?php echo !empty($product['image_path']) ? $product['image_path'] : 'default-image.jpg'; ?>" alt="Product Image">
                            <div class="modal-details">
                                <h2><?php echo $product['product_name']; ?></h2>
                                <p>Category: <?php echo $product['category_name']; ?></p>
                                <p>Price: ₱<?php echo number_format($product['price'], 2); ?></p>
                                <p>Barangay: <?php echo $product['barangay_name']; ?></p>
                                <p><?php echo $product['description']; ?></p>
                                <a href="?add_to_cart=<?php echo $product['id']; ?>"><button>Add to Cart</button></a>
                                <a href="checkout.php"><button>Buy Now</button></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p>No products found for this category or search term.</p>
        <?php } ?>
    </div>

    <script>
        function openModal(productId) {
            var modal = document.getElementById("modal-" + productId);
            modal.style.display = "block";
        }

        function closeModal(productId) {
            var modal = document.getElementById("modal-" + productId);
            modal.style.display = "none";
        }

        // Close modal if clicked outside of the modal content
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id.replace('modal-', ''));
            }
        };

        function searchProducts() {
            let searchTerm = document.querySelector('.search-bar').value;
            window.location.href = '?search=' + searchTerm;
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>

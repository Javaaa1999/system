<?php
session_start();

// Check if user is logged in
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

// Fetch cart items from session
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cart_products = [];
$total_price = 0;

if (!empty($cart_items)) {
    // Get product details from the database
    $placeholders = implode(",", array_fill(0, count($cart_items), "?"));
    $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($cart_items)), ...$cart_items);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($product = $result->fetch_assoc()) {
        $cart_products[] = $product;
        $total_price += $product['price']; // Calculate total price
    }
}

// Update quantity or remove product from cart
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'update_quantity') {
        $product_id = $_POST['product_id'];
        $new_quantity = $_POST['quantity'];

        // Update session cart quantity
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = $new_quantity;
        }
    } elseif ($_POST['action'] == 'remove_product') {
        $product_id = $_POST['product_id'];

        // Remove product from session cart
        if (($key = array_search($product_id, $_SESSION['cart'])) !== false) {
            unset($_SESSION['cart'][$key]);
        }
    }

    // Redirect back to the cart page
    header("Location: mycart.php");
    exit();
}

// Place order handling
if (isset($_POST['place_order'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $zipcode = $_POST['zipcode'];
    $payment_method = $_POST['payment_method'];

    // Store the order in the database (you may need an 'orders' table)
    $order_sql = "INSERT INTO orders (username, name, email, address, contact, zipcode, payment_method, total_amount)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($order_sql);
    $stmt->bind_param("ssssssss", $_SESSION['username'], $name, $email, $address, $contact, $zipcode, $payment_method, $total_price);
    $stmt->execute();

    // Clear the cart after placing the order
    unset($_SESSION['cart']);

    echo "<script>alert('Your order has been placed successfully!');</script>";
    echo "<script>window.location.href = 'index.php';</script>"; // Redirect to homepage or order confirmation page
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
    <style>
       /* General Page Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.cart-container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #ddd;
}

.cart-item img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 5px;
    margin-right: 20px;
}

.cart-item-info {
    flex-grow: 1;
}

.cart-item-title {
    font-size: 18px;
    font-weight: bold;
    color: #333;
}

.cart-item-price {
    font-size: 16px;
    color: #4CAF50;
}

.cart-item-remove, .cart-item-update button {
    background-color: #F44336;
    color: white;
    padding: 6px 12px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 14px;
}

.cart-item-remove:hover, .cart-item-update button:hover {
    background-color: #D32F2F;
}

.cart-summary {
    text-align: right;
    font-size: 20px;
    font-weight: bold;
    margin-top: 20px;
}

.total-amount {
    color: #333;
}

button {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    font-size: 16px;
    border-radius: 5px;
}

button:hover {
    background-color: #45a049;
}

/* Modal Styling */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0, 0, 0);
    background-color: rgba(0, 0, 0, 0.4);
    padding-top: 60px;
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border-radius: 8px;
    width: 50%;
    max-width: 600px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    top: 10px;
    right: 20px;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

input[type="text"], input[type="email"], select {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 100%;
}

select {
    padding: 10px;
}

button[type="submit"] {
    background-color: #4CAF50;
    padding: 12px 20px;
    border-radius: 5px;
    border: none;
    font-size: 16px;
}

button[type="submit"]:hover {
    background-color: #45a049;
}

/* Responsive Design */
@media (max-width: 768px) {
    .cart-container {
        padding: 10px;
    }

    .modal-content {
        width: 80%;
    }

    .cart-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .cart-item-info {
        flex-grow: 0;
        margin-top: 10px;
    }

    .cart-item-title {
        font-size: 16px;
    }

    .cart-item-price {
        font-size: 14px;
    }

    button {
        width: 100%;
        padding: 12px;
    }
}

    </style>
</head>
<body>
    <div class="cart-container">
        <h2>My Cart</h2>
        <a href="user_dashboard.php" class="add-product-btn">back</a>


        <?php if (!empty($cart_products)) { ?>
            <?php foreach ($cart_products as $product) { ?>
                <div class="cart-item">
                    <img src="<?php echo !empty($product['image_path']) ? $product['image_path'] : 'default-image.jpg'; ?>" alt="Product Image">
                    <div class="cart-item-info">
                        <div class="cart-item-title"><?php echo $product['product_name']; ?></div>
                        <div class="cart-item-price">₱<?php echo number_format($product['price'], 2); ?></div>
                    </div>

                    <!-- Quantity Update and Remove Product -->
                    <form method="POST" style="display:inline;">
                        <input type="number" name="quantity" value="<?php echo $_SESSION['cart'][$product['id']] ?? 1; ?>" min="1">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="action" value="update_quantity">
                        <button type="submit">Update Quantity</button>
                    </form>

                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="action" value="remove_product">
                        <button type="submit" class="cart-item-remove">Remove</button>
                    </form>
                </div>
            <?php } ?>

            <!-- Display total amount -->
            <div class="cart-summary">
                Total Amount: <span class="total-amount">₱<?php echo number_format($total_price, 2); ?></span>
            </div>

            <!-- Button to trigger Place Order modal -->
            <button id="placeOrderButton" onclick="openOrderModal()">Place Order</button>
        <?php } else { ?>
            <p>Your cart is empty.</p>
        <?php } ?>
    </div>

    <!-- Modal for Place Order -->
    <div id="orderModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeOrderModal()">&times;</span>
            <h2>Place Your Order</h2>
            <form method="POST">
                <input type="hidden" name="place_order" value="1">
                <div>
                    <label>Name:</label>
                    <input type="text" name="name" required>
                </div>
                <div>
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div>
                    <label>Address:</label>
                    <input type="text" name="address" required>
                </div>
                <div>
                    <label>Contact Number:</label>
                    <input type="text" name="contact" required>
                </div>
                <div>
                    <label>Zip Code:</label>
                    <input type="text" name="zipcode" required>
                </div>
                <div>
                    <label>Payment Method:</label>
                    <select name="payment_method" required>
                        <option value="cash_on_delivery">Cash on Delivery</option>
                        <option value="credit_card">Credit Card</option>
                    </select>
                </div>
                <div>
                    <button type="submit">Place Order</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open the modal
        function openOrderModal() {
            document.getElementById('orderModal').style.display = 'block';
        }

        // Close the modal
        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        // Close modal if clicked outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('orderModal')) {
                closeOrderModal();
            }
        };
    </script>
</body>
</html>

<?php
$conn->close();
?>

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

// Fetch data for the dashboard
$totalProducts = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$totalOrders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$pendingOrders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'")->fetch_assoc()['total'];

/// Fetch recent orders (3 most recent)
$recentOrders = $conn->query("
SELECT o.id as order_id, u.username as customer_name, p.product_name as product, o.status, o.order_date as date, o.total
FROM orders o
JOIN users u ON o.user_id = u.id
JOIN products p ON o.product_id = p.id
ORDER BY o.order_date DESC
LIMIT 3
");


// Handle barangay addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_barangay'])) {
    $barangay_name = $_POST['barangay_name'];
    if (!empty($barangay_name)) {
        $stmt = $conn->prepare("INSERT INTO barangays (name) VALUES (?)");
        $stmt->bind_param("s", $barangay_name);
        if ($stmt->execute()) {
            echo "<script>alert('Barangay added successfully!');</script>";
        } else {
            echo "<script>alert('Failed to add barangay.');</script>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriLink Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #b2f7b6, #f7d490);
            margin: 0;
            padding: 0;
        }
        .sidebar {
            background-color: #8A724A;
            height: 100vh;
            width: 200px;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        .sidebar a {
            text-decoration: none;
            color: white;
            padding: 15px;
            margin: 5px 0;
            background-color: #6F8B68;
            border-radius: 5px;
            text-align: left;
            font-size: 18px;
        }
        .sidebar a:hover {
            background-color: #4E6A4A;
        }
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }
        .admin-header {
            background-color: #4E6A4A;
            padding: 20px;
            color: white;
            text-align: center;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .overview-cards {
            display: flex;
            padding: 20px 10px;
            justify-content: space-between;
        }
        .card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 17%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .card h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .card p {
            font-size: 22px;
            font-weight: bold;
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
        /* Add Barangay Form Styles */
        .form-container {
            background-color: white;
            padding: 20px;
            margin-top: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="admin.php">Dashboard</a>
        <a href="manage_products.php">Manage Products</a>
        <a href="manage_barangay.php">Barangays</a>
        <a href="orderlist.php">Orders</a>
        <a href="reports.php">Reports</a>
        <a href="settings.php">Settings</a>
        <a href="login.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="admin-header">AgriLink Admin Dashboard</div>

        <!-- Overview Cards -->
        <div class="overview-cards">
            <div class="card">
                <h3>Total Products</h3>
                <p><?php echo $totalProducts; ?></p>
            </div>
            <div class="card">
                <h3>Total Users</h3>
                <p><?php echo $totalUsers; ?></p>
            </div>
            <div class="card">
                <h3>Total Orders</h3>
                <p><?php echo $totalOrders; ?></p>
            </div>
            <div class="card">
                <h3>Pending Orders</h3>
                <p><?php echo $pendingOrders; ?></p>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <h3>Recent Orders</h3>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $recentOrders->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['customer_name']; ?></td>
                    <td><?php echo $order['product']; ?></td>
                    <td><?php echo $order['status']; ?></td>
                    <td><?php echo $order['date']; ?></td>
                    <td>$<?php echo $order['total']; ?></td>
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

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

// Fetch admin-related data like total products, users, and orders
$totalProducts = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
$pendingOrders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'Pending'")->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriLink Admin Dashboard</title>
    <style>
        html, body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }

        body {
            background: url('images/brown.png') no-repeat center center fixed;
            background-size: cover;
        }

        .navbar {
            background-color: #8A724A;
            padding: 25px;
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
            color: white;
        }

        .navbar button {
            background-color: #4E6A4A;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            margin-left: 20px;
        }

        .navbar button:hover {
            background-color: #6F8B68;
            transform: scale(1.05);
        }

        .navbar-right {
            display: flex;
            align-items: center;
        }

        .dashboard-section {
            margin: 20px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
        }

        .dashboard-section h1 {
            text-align: center;
            color: #4E6A4A;
        }

        .card-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 23%;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            font-size: 18px;
            color: #4E6A4A;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 22px;
            color: #333;
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
    </style>
</head>
<body>

    <!-- Navbar with AGR-ECOMMERCE, Logout -->
    <div class="navbar">
        <div class="navbar-left">
            <p>AGR-ECOMMERCE ADMIN</p>
            <a href="logout.php"><button>Logout</button></a>
        </div>
    </div>

    <!-- Dashboard Overview Section -->
    <div class="dashboard-section">
        <h1>Admin Dashboard</h1>

        <!-- Overview Cards -->
        <div class="card-container">
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

        <!-- Recent Orders Section -->
        <h2>Recent Orders</h2>
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
                <?php
                $recentOrders = $conn->query("SELECT * FROM orders ORDER BY date DESC LIMIT 5");
                while ($order = $recentOrders->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['customer_name']; ?></td>
                    <td><?php echo $order['product']; ?></td>
                    <td><?php echo $order['status']; ?></td>
                    <td><?php echo $order['date']; ?></td>
                    <td><?php echo $order['total']; ?></td>
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

<?php
session_start();

// Try to connect to MySQL
$conn = new mysqli("localhost", "root", "", "agrilink");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully!";

// Perform SELECT query to fetch order data
$sql = "SELECT order_id, customer_name, order_date, order_status, total_amount FROM orders"; // Adjust the table name if needed
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order List</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
/* CSS Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
}

.container {
    width: 80%;
    margin: 20px auto;
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.table th,
.table td {
    padding: 10px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.table th {
    background-color: #4CAF50;
    color: white;
    position: sticky;
    top: 0;
    z-index: 1;
}

.table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.table tr:hover {
    background-color: #f1f1f1;
}

.table td {
    color: #333;
}

table {
    border-spacing: 0;
    border-collapse: collapse;
}

/* Back Button Styles */
.back-btn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    border-radius: 5px;
    display: inline-block;
    margin-bottom: 20px;
    transition: background-color 0.3s ease;
}

.back-btn:hover {
    background-color: #45a049;
}
</style>

<body>
    <div class="container">
        <!-- Back Button -->
        <a href="javascript:history.back()" class="back-btn">Back</a>
        
        <h2>Order List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if data is returned from the database
                if ($result->num_rows > 0) {
                    // Loop through the rows of data
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['order_id'] . "</td>";
                        echo "<td>" . $row['customer_name'] . "</td>";
                        echo "<td>" . $row['order_date'] . "</td>";
                        echo "<td>" . $row['order_status'] . "</td>";
                        echo "<td>" . "$" . number_format($row['total_amount'], 2) . "</td>"; // Format as currency
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No orders found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>

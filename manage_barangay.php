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

// Add barangay
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_barangay'])) {
    $barangay_name = $_POST['barangay_name'];

    $stmt = $conn->prepare("INSERT INTO barangays (name) VALUES (?)");
    $stmt->bind_param("s", $barangay_name);

    if ($stmt->execute()) {
        header("Location: manage_barangay.php");
        exit();
    }
}

// Fetch barangays
$barangays = $conn->query("SELECT * FROM barangays");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Barangays</title>
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
        <h2>Manage Barangays</h2>

        <a href="add_barangay.php" class="add-product-btn">Add Barangay</a>
        <a href="admin.php" class="add-product-btn">back</a>
        

        <!-- List of Barangays -->
        <h3>Existing Barangays</h3>
        <table>
            <thead>
                <tr>
                    
                    <th>Barangay Name</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($barangay = $barangays->fetch_assoc()) { ?>
                    <tr>
                        
                        <td><?php echo $barangay['name']; ?></td>
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

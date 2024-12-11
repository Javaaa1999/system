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

// Handle barangay addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_barangay'])) {
    $barangay_name = $_POST['barangay_name'];
    
    if (!empty($barangay_name)) {
        $stmt = $conn->prepare("INSERT INTO barangays (name) VALUES (?)");
        $stmt->bind_param("s", $barangay_name);

        if ($stmt->execute()) {
            // Redirect to manage_products.php after successful product addition
            header("Location: manage_barangay.php");
            exit();
        } else {
            echo "Error adding barangay: " . $stmt->error;
        }
    } else {
        echo "<script>alert('Please enter a barangay name.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Barangay</title>
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
        .form-container h3 {
            text-align: center;
        }
        .form-container img {
            display: block;
            margin: 0 auto;
            width: 100px;
            height: auto;
        }
        input[type="text"] {
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
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 16px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Add Barangay</h2>

        <!-- Add Barangay Form -->
        <div class="form-container">
            <!-- Logo at the top of the form -->
            <img src="images/agrilink logo.png" alt="AgriLink Logo">

            <h3>Add New Barangay</h3>
            <form method="POST" action="add_barangay.php">
                <input type="text" name="barangay_name" placeholder="Enter Barangay Name" required>
                <button type="submit" name="add_barangay">Add Barangay</button>
            </form>

            <div class="back-link">
                <a href="manage_barangay.php">&larr; Back</a>
            </div>
        </div>
    </div>

</body>
</html>

<?php
$conn->close();
?>

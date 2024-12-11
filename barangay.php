<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "agrilink");

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add new barangay if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['barangay_name'])) {
    $barangay_name = $_POST['barangay_name'];

    // Insert new barangay into the database
    $stmt = $conn->prepare("INSERT INTO barangays (name) VALUES (?)");
    $stmt->bind_param("s", $barangay_name);
    $stmt->execute();
    $stmt->close();
}

// Delete barangay if delete is requested
if (isset($_GET['delete_barangay_id'])) {
    $delete_barangay_id = $_GET['delete_barangay_id'];
    // Delete the barangay and all related products
    $delete_query = "DELETE FROM barangays WHERE id = $delete_barangay_id";
    $conn->query($delete_query);
    header("Location: barangay.php");
    exit();
}

// Fetch all barangays from the database
$barangays = [];
$barangay_query = "SELECT * FROM barangays";
$result = $conn->query($barangay_query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $barangays[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #6CE28D, #F5F6F9, #F9EC52);
            background-size: cover;
            margin: 0;
            color: green;
        }
        h1 {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            width: fit-content;
            margin: 20px auto;
        }
        .barangay-container {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .barangay-card {
            background-color: mediumaquamarine;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        .barangay-card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        .barangay-card a {
            text-decoration: none;
            color: black;
            font-weight: bold;
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 12px;
        }
        .delete-btn:hover {
            background-color: #e53935;
        }

        .form-container {
            margin: 20px auto;
            width: 50%;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
        .form-container input[type="text"] {
            width: calc(100% - 20px);
            padding: 12px;
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

        .add-product-btn {
    display: inline-block;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    text-align: center;
    text-decoration: none;
    font-size: 16px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
}

.add-product-btn:hover {
    background-color: #45a049;
    transform: scale(1.05);
}

.add-product-btn:active {
    background-color: #388e3c;
    transform: scale(1);
}

    </style>
</head>
<body>

    <h1>Barangays in Roxas, Isabela</h1>
    <a href="user_dashboard.php" class="add-product-btn">back</a>

    <div class="barangay-container">
        <!-- Loop through the barangays from the database -->
        <?php foreach ($barangays as $barangay) { ?>
            <div class="barangay-card">
                <a href="barangay_products.php?barangay_id=<?php echo $barangay['id']; ?>">
                    <h3><?php echo $barangay['name']; ?></h3>
                </a>
                <!-- Delete Button -->
                <form action="barangay.php" method="GET" style="display:inline;">
                    <input type="hidden" name="delete_barangay_id" value="<?php echo $barangay['id']; ?>">
                    <!-- <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this barangay?')">Delete</button> -->
                </form>
            </div>

            
        <?php } ?>
    </div>

   

</body>
</html>

<?php
$conn->close();
?>

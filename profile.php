<?php
session_start();
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

// Fetch user details from the database
$username = $_SESSION['username']; // Assuming the username is stored in session
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "uploads/"; // Folder to save the image
    $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the file is an image
    $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
    if ($check !== false) {
        // Move uploaded image to the uploads directory
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            // Update the profile image in the database
            $sql = "UPDATE users SET profile_image = ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $target_file, $_SESSION['username']);
            $stmt->execute();
            // Reload the page to reflect the new profile image
            header("Location: profile.php");
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "File is not an image.";
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 1000px;
            margin: 30px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .profile-header img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-right: 20px;
        }

        .profile-header h2 {
            margin: 0;
            color: #333;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            margin-top: 20px;
        }

        .profile-info div {
            margin: 10px 0;
        }

        .profile-info label {
            font-weight: bold;
            color: #555;
        }

        .profile-info p {
            margin: 0;
            color: #777;
        }

        .button-container {
            margin-top: 20px;
            text-align: center;
        }

        .button-container a {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .button-container a:hover {
            background-color: #45a049;
        }

        .upload-form {
            margin-top: 20px;
            text-align: center;
        }

        .upload-form input[type="file"] {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .upload-form button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .upload-form button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="profile-header">
            <img src="<?php echo !empty($user['profile_image']) ? $user['profile_image'] : 'default-profile.jpg'; ?>" alt="Profile Image">
            <h2><?php echo $user['username']; ?></h2>
        </div>

        <div class="profile-info">
            <div>
                <label>Name:</label>
                <p><?php echo $user['full_name']; ?></p>
            </div>
            <div>
                <label>Address:</label>
                <p><?php echo $user['address']; ?></p>
            </div>
            <div>
                <label>Contact Number:</label>
                <p><?php echo $user['contact_number']; ?></p>
            </div>
            <div>
                <label>Email:</label>
                <p><?php echo $user['email']; ?></p>
            </div>
            <div>
                <label>Gender:</label>
                <p><?php echo ucfirst($user['gender']); ?></p>
            </div>
            <div>
                <label>Birth Date:</label>
                <p><?php echo date('F j, Y', strtotime($user['birth_date'])); ?></p>
            </div>
        </div>

        <div class="button-container">
            <a href="edit-profile.php">Edit Profile</a>
        </div>

        <!-- Profile Image Upload Form -->
        <div class="upload-form">
            <form method="POST" enctype="multipart/form-data">
                <label for="profile_image">Change Profile Image:</label><br><br>
                <input type="file" name="profile_image" id="profile_image" accept="image/*"><br><br>
                <button type="submit">Upload Image</button>
            </form>
        </div>
    </div>

</body>
</html>

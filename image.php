<?php
// Enable error reporting for debugging (remove this in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$database = "micro";

// Attempt to connect to the database
// Attempt to connect to the database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_POST['submit'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Ensure the directory exists
    }
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    
    // Check if the file is an actual image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        // Try to upload file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            echo "The file ". htmlspecialchars( basename( $_FILES["image"]["name"])). " has been uploaded.";

            // Prepare an insert statement
            $stmt = $conn->prepare("INSERT INTO image (name, image) VALUES (?, ?)");
            $name = $_POST['name'];
            $stmt->bind_param("ss", $name, $target_file); // 'ss' means two strings are being bound

            // Execute the prepared statement
            if ($stmt->execute()) {
                echo "New record created successfully";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close(); // Close statement
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "File is not an image.";
    }
}

$conn->close(); // Close connection
?>

<!DOCTYPE html>
<html>
<head>
    <title>Image Upload Form</title>
</head>
<body>
    <form action="image.php" method="post" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>
        <label for="image">Select image:</label>
        <input type="file" id="image" name="image" accept="image/*" required><br><br>
        <input type="submit" name="submit" value="Submit">
    </form>
</body>
</html>

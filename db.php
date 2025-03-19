<?php
$servername = "localhost";  // Change if your server is different
$username = "root";         // Default username for localhost
$password = "";             // Default password for localhost
$database = "capstone";     // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Uncomment below if you want a success confirmation
// echo "Connected successfully";
?>

<?php
session_start();
include('db.php'); 

// Check if client is logged in
if (!isset($_SESSION['client_id'])) {
    echo "<script>alert('Please log in first.'); window.location='login_client.php';</script>";
    exit();
}

// Check for POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $worker_id = intval($_POST['worker_id']);
    $client_id = intval($_SESSION['client_id']);  // Ensure client_id is correctly retrieved
    $rating = floatval($_POST['rating']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    $feedback = isset($_POST['feedback']) ? mysqli_real_escape_string($conn, $_POST['feedback']) : '';

    // Check if the client already rated the worker
    $check_sql = "SELECT * FROM ratings WHERE worker_id = ? AND client_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $worker_id, $client_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('You have already rated this worker.'); window.location='worker_profile.php';</script>";
        exit();
    }

    // Insert new rating
    $insert_sql = "INSERT INTO ratings (worker_id, client_id, rating, comment, created_at, feedback) 
                   VALUES (?, ?, ?, ?, NOW(), ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iiiss", $worker_id, $client_id, $rating, $comment, $feedback);

    if ($insert_stmt->execute()) {
        echo "<script>alert('Rating submitted successfully!'); window.location='client_home.php';</script>";
    } else {
        echo "<script>alert('Error submitting rating. Please try again.');</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location='client_home.php';</script>";
}
?>

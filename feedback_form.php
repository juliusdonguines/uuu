<?php
session_start();
include('db.php');

if (!isset($_SESSION['client_username'])) {
    header("Location: login_client.php");
    exit();
}

// Retrieve client's username and ID
$client_username = htmlspecialchars($_SESSION['client_username']);

$client_sql = "SELECT id FROM clients WHERE username = ?";
$client_stmt = $conn->prepare($client_sql);
$client_stmt->bind_param("s", $client_username);
$client_stmt->execute();
$client_result = $client_stmt->get_result();

if ($client_result->num_rows === 0) {
    echo "<script>alert('Client not found. Please log in again.'); window.location='login_client.php';</script>";
    exit();
}

$client = $client_result->fetch_assoc();
$client_id = $client['id'];

// Check if worker's full name is provided
if (!isset($_GET['worker_name'])) {
    echo "<script>alert('Worker not found.'); window.location='client_home.php';</script>";
    exit();
}

$worker_name = htmlspecialchars($_GET['worker_name']);

// Fetch worker ID based on full name
$worker_sql = "SELECT id FROM workers WHERE full_name = ?";
$worker_stmt = $conn->prepare($worker_sql);
$worker_stmt->bind_param("s", $worker_name);
$worker_stmt->execute();
$worker_result = $worker_stmt->get_result();

if ($worker_result->num_rows === 0) {
    echo "<script>alert('Worker not found.'); window.location='client_home.php';</script>";
    exit();
}

$worker = $worker_result->fetch_assoc();
$worker_id = $worker['id'];

// Submit feedback
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = intval($_POST['rating']);
    $comment = htmlspecialchars($_POST['comment']);

    $insert_sql = "INSERT INTO ratings (worker_id, client_id, rating, comment, created_at) 
                   VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iiis", $worker_id, $client_id, $rating, $comment);

    if ($insert_stmt->execute()) {
        echo "<script>alert('Feedback submitted successfully!'); window.location='client_home.php';</script>";
    } else {
        echo "<script>alert('Failed to submit feedback. Please try again.'); window.location='feedback_form.php?worker_name=$worker_name';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Feedback Form</title>
</head>
<body>
    <h2>Leave Feedback for <?php echo htmlspecialchars($worker_name); ?></h2>
    <form method="POST">
        <label for="rating">Rating (1-5):</label>
        <select name="rating" required>
            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
            <option value="4">⭐⭐⭐⭐ Very Good</option>
            <option value="3">⭐⭐⭐ Good</option>
            <option value="2">⭐⭐ Fair</option>
            <option value="1">⭐ Poor</option>
        </select>

        <label for="comment">Comment:</label>
        <textarea name="comment" rows="4" required></textarea>

        <button type="submit" class="btn">Submit Feedback</button>
        <a href="client_home.php" class="btn">Cancel</a>
    </form>
</body>
</html>

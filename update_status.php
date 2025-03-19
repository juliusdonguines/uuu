<?php
session_start();
include('db.php');

if (!isset($_SESSION['worker_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_worker.php';</script>";
    exit();
}

// Ensure the required parameters are provided
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    echo "<script>alert('Invalid request.'); window.location='worker_home.php';</script>";
    exit();
}

$hire_id = intval($_GET['id']);
$status = mysqli_real_escape_string($conn, $_GET['status']);

// Validate status values
$valid_statuses = ['Accepted', 'Cancelled', 'On Duty'];
if (!in_array($status, $valid_statuses)) {
    echo "<script>alert('Invalid status.'); window.location='worker_home.php';</script>";
    exit();
}

// Update the status in the database
$sql = "UPDATE hires SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $hire_id);

if ($stmt->execute()) {
    // Fetch client and worker details for notification
    $client_sql = "
        SELECT h.client_id, c.full_name AS client_name, w.full_name AS worker_name
        FROM hires h
        JOIN clients c ON h.client_id = c.id
        JOIN workers w ON h.worker_id = w.id
        WHERE h.id = ?
    ";
    $client_stmt = $conn->prepare($client_sql);
    $client_stmt->bind_param("i", $hire_id);
    $client_stmt->execute();
    $client_result = $client_stmt->get_result();
    
    if ($client = $client_result->fetch_assoc()) {
        $client_id = $client['client_id'];
        $client_name = htmlspecialchars($client['client_name']);
        $worker_name = htmlspecialchars($client['worker_name']);

        // Insert notification with worker's name
        $notif_sql = "INSERT INTO notifications (client_id, message) VALUES (?, ?)";
        $notif_stmt = $conn->prepare($notif_sql);
        $notif_message = "Your hire request with **$worker_name** has been marked as '$status'.";
        $notif_stmt->bind_param("is", $client_id, $notif_message);
        $notif_stmt->execute();
    }

    echo "<script>alert('Status updated successfully.'); window.location='worker_home.php';</script>";
} else {
    echo "<script>alert('Failed to update status.'); window.location='worker_home.php';</script>";
}

$stmt->close();
$conn->close();
?>

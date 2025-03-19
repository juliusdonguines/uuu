<?php
session_start();
include('db.php');

// Check if the hire ID exists
if (!isset($_GET['hire_id'])) {
    echo "<script>alert('Invalid request.'); window.location='available_services.php';</script>";
    exit();
}

$hire_id = intval($_GET['hire_id']);

// Update the status to "Completed"
$sql = "UPDATE hires SET status = 'Completed' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hire_id);

if ($stmt->execute()) {
    echo "<script>
        alert('Work marked as completed! You can now submit a recommendation.');
        window.location='recommend_worker.php?worker_id=" . $_GET['worker_id'] . "';
    </script>";
} else {
    echo "<script>alert('Error marking work as completed.');</script>";
}

$conn->close();
?>

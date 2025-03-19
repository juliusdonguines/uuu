<?php
session_start();
include('db.php');

if (!isset($_SESSION['worker_username'])) {
    header("Location: login_worker.php");
    exit();
}

$worker_username = $_SESSION['worker_username'];

// Fetch Worker ID
$sql = "SELECT id FROM workers WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $worker_username);
$stmt->execute();
$worker_result = $stmt->get_result();

if ($worker_result->num_rows === 0) {
    echo "<script>alert('Worker not found. Please try again.'); window.location='worker_home.php';</script>";
    exit();
}

$worker = $worker_result->fetch_assoc();
$worker_id = $worker['id'];

// Accept the Job
if (isset($_POST['job_id'])) {
    $job_id = intval($_POST['job_id']);

    // Fetch job details
    $fetch_sql = "SELECT client_id, client_username, schedule_date, schedule_time FROM job_offers WHERE id = ?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param("i", $job_id);
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();

    if ($fetch_result->num_rows === 0) {
        echo "<script>alert('Job not found.'); window.location='worker_home.php';</script>";
        exit();
    }

    $job_data = $fetch_result->fetch_assoc();
    $client_id = $job_data['client_id'];
    $client_username = $job_data['client_username'];
    $schedule_date = $job_data['schedule_date'];
    $schedule_time = $job_data['schedule_time'];

    // Insert into `hires` table
    $insert_sql = "INSERT INTO hires (job_id, client_id, client_username, worker_id, schedule_date, schedule_time, status, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?, 'Accepted', NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iisiss", $job_id, $client_id, $client_username, $worker_id, $schedule_date, $schedule_time);
    $insert_stmt->execute();

    // Update job offer status
    $update_sql = "UPDATE job_offers SET status = 'Accepted', accepted_by = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $worker_id, $job_id);
    $update_stmt->execute();

    // Notify the client
    $notif_sql = "INSERT INTO notifications (client_id, message, created_at) 
                  VALUES (?, ?, NOW())";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_message = "Worker '$worker_username' accepted your job offer.";
    $notif_stmt->bind_param("is", $client_id, $notif_message);
    $notif_stmt->execute();

    echo "<script>alert('Job accepted successfully!'); window.location='worker_home.php';</script>";
} else {
    echo "<script>alert('No job offer selected.'); window.location='worker_home.php';</script>";
}

$conn->close();
?>

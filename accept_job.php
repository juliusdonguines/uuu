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

    // Insert into hires table (remove worker_username if it's not in the table)
    $insert_sql = "INSERT INTO hires (job_id, client_id, client_username, worker_id, schedule_date, schedule_time, status, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?, 'Accepted', NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iisiss", $job_id, $client_id, $client_username, $worker_id, $schedule_date, $schedule_time);
    if (!$insert_stmt->execute()) {
        die("Error inserting into hires: " . $insert_stmt->error);
    }

    // Update job offer status
    $update_sql = "UPDATE job_offers SET status = 'Accepted', accepted_by = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $worker_id, $job_id);
    if (!$update_stmt->execute()) {
        die("Error updating job status: " . $update_stmt->error);
    }

    // Notify the client that the worker accepted the job
    $notif_sql_client = "INSERT INTO notifications (client_id, message, created_at) VALUES (?, ?, NOW())";
    $notif_stmt_client = $conn->prepare($notif_sql_client);
    $notif_message_client = "Worker '$worker_username' accepted your job offer.";
    $notif_stmt_client->bind_param("is", $client_id, $notif_message_client);
    if (!$notif_stmt_client->execute()) {
        die("Error inserting client notification: " . $notif_stmt_client->error);
    }

    // Notify the worker about the appointment
    $worker_notif_sql = "INSERT INTO notification_worker (worker_id, message, created_at) VALUES (?, ?, NOW())";
    $worker_notif_stmt = $conn->prepare($worker_notif_sql);
    $worker_notif_message = "You have an appointment on " . date("F j, Y", strtotime($schedule_date)) . 
                            " at " . date("g:i A", strtotime($schedule_time)) . "  " . $client_username . ".";
    $worker_notif_stmt->bind_param("is", $worker_id, $worker_notif_message);
    if (!$worker_notif_stmt->execute()) {
        die("Error inserting worker notification: " . $worker_notif_stmt->error);
    }

    echo "<script>alert('Job accepted successfully!'); window.location='worker_home.php';</script>";
} else {
    echo "<script>alert('No job offer selected.'); window.location='worker_home.php';</script>";
}

$conn->close();
?>

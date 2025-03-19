<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['client_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_client.php';</script>";
    exit();
}

// Validate incoming data
if (!isset($_POST['worker_id'], $_POST['schedule_date'], $_POST['schedule_time'])) {
    echo "<script>alert('Invalid request. Please fill out all fields.'); window.location='available_services.php';</script>";
    exit();
}

$client_username = $_SESSION['client_username'];
$worker_id = intval($_POST['worker_id']);
$schedule_date = $_POST['schedule_date'];
$schedule_time = $_POST['schedule_time'];

// Date format validation (YYYY-MM-DD)
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $schedule_date)) {
    echo "<script>alert('Invalid date format. Please select a valid date.'); window.location='available_services.php';</script>";
    exit();
}

// Fetch client ID
$client_query = "SELECT id FROM clients WHERE username = ?";
$stmt = $conn->prepare($client_query);
$stmt->bind_param("s", $client_username);
$stmt->execute();
$client_result = $stmt->get_result();

if ($client_result->num_rows === 0) {
    echo "<script>alert('Client not found. Please try again.'); window.location='available_services.php';</script>";
    exit();
}

$client = $client_result->fetch_assoc();
$client_id = $client['id'];

// Insert hire record
$sql = "INSERT INTO hires (client_id, worker_id, schedule_date, schedule_time, status, created_at, client_username) 
        VALUES (?, ?, ?, ?, 'Pending', NOW(), ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisss", $client_id, $worker_id, $schedule_date, $schedule_time, $client_username);

if ($stmt->execute()) {
    // Notify the client
    $client_notif_sql = "INSERT INTO notifications (client_id, message, created_at) 
                         VALUES (?, 'Your hire request has been sent successfully.', NOW())";
    $client_notif_stmt = $conn->prepare($client_notif_sql);
    $client_notif_stmt->bind_param("i", $client_id);
    $client_notif_stmt->execute();

    // Fetch worker's client_id (to use for notifications)
    $worker_query = "SELECT id FROM workers WHERE id = ?";
    $worker_stmt = $conn->prepare($worker_query);
    $worker_stmt->bind_param("i", $worker_id);
    $worker_stmt->execute();
    $worker_result = $worker_stmt->get_result();

    if ($worker_result->num_rows > 0) {
        $worker = $worker_result->fetch_assoc();
        $worker_client_id = $worker['id']; // Use worker's ID as the recipient in notifications

        // Notify the worker
        $worker_notif_sql = "INSERT INTO notifications (client_id, message, created_at)
                             VALUES (?, 'You have a new hire request scheduled on $schedule_date at $schedule_time.', NOW())";
        $worker_notif_stmt = $conn->prepare($worker_notif_sql);
        $worker_notif_stmt->bind_param("i", $worker_client_id);
        $worker_notif_stmt->execute();
    }

    echo "<script>
        alert('Worker hired successfully! Notification sent.');
        window.location='available_services.php';
    </script>";
} else {
    echo "<script>
        alert('Error hiring worker: " . $stmt->error . "');
        window.location='available_services.php';
    </script>";
}

$conn->close();
?>

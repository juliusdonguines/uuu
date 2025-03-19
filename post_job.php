<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['client_username'])) {
    header("Location: login_client.php");
    exit();
}

$client_username = $_SESSION['client_username'];

// Fetch client ID
$sql = "SELECT id FROM clients WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $client_username);
$stmt->execute();
$client_result = $stmt->get_result();
$client = $client_result->fetch_assoc();
$client_id = $client['id'];

// Insert job details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $job_description = mysqli_real_escape_string($conn, $_POST['job_description']);
    $schedule_date = $_POST['schedule_date'];
    $schedule_time = $_POST['schedule_time'];

    $insert_sql = "INSERT INTO job_offers (client_id, job_title, job_description, schedule_date, schedule_time) 
                   VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("issss", $client_id, $job_title, $job_description, $schedule_date, $schedule_time);
    $insert_stmt->execute();

    echo "<script>alert('Job posted successfully!'); window.location='client_home.php';</script>";
}
?>

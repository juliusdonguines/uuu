<?php
session_start();
include('db.php');

if (!isset($_SESSION['client_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_client.php';</script>";
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Invalid worker ID.'); window.location='available_services.php';</script>";
    exit();
}

$worker_id = intval($_GET['id']);

// Fetch worker's resume
$sql = "SELECT full_name, resume FROM workers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Worker not found.'); window.location='available_services.php';</script>";
    exit();
}

$worker = $result->fetch_assoc();
$resume_path = 'uploads/' . htmlspecialchars($worker['resume']);

// Check if file exists
if (!file_exists($resume_path)) {
    echo "<script>alert('Resume not found.'); window.location='available_services.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($worker['full_name']); ?>'s Resume</title>
</head>
<body>
    <h2><?php echo htmlspecialchars($worker['full_name']); ?>'s Resume</h2>
    <iframe src="<?php echo $resume_path; ?>" width="100%" height="600px" style="border: none;"></iframe>
    <br>
    <a href="available_services.php" class="btn">Back to Services</a>
</body>
</html>

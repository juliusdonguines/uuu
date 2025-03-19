<?php
session_start();
include('db.php');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Please log in first.'); window.location='login.php';</script>";
    exit();
}

$loggedInUser = $_SESSION['username'];
$receiver = isset($_GET['worker']) ? $_GET['worker'] : '';

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO chat_messages (sender, receiver, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $loggedInUser, $receiver, $message);
    $stmt->execute();
}

// Display messages
$sql = "SELECT * FROM chat_messages WHERE (sender = ? AND receiver = ?) OR (sender = ? AND receiver = ?) ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $loggedInUser, $receiver, $receiver, $loggedInUser);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Direct Chat</title>
</head>
<body>
    <h2>Chat with <?php echo htmlspecialchars($receiver); ?></h2>

    <div class="chat-box">
        <?php while ($row = $result->fetch_assoc()): ?>
            <p><strong><?php echo htmlspecialchars($row['sender']); ?>:</strong> 
                <?php echo htmlspecialchars($row['message']); ?>
            </p>
        <?php endwhile; ?>
    </div>

    <form method="POST">
        <textarea name="message" required placeholder="Type your message here..."></textarea>
        <button type="submit">Send</button>
    </form>

    <a href="worker_home.php">Back to Home</a>
</body>
</html>

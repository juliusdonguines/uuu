<?php
session_start();
include('db.php');

// Ensure client is logged in
if (!isset($_SESSION['client_username'])) {
    header("Location: login_client.php");
    exit();
}

$client_username = $_SESSION['client_username'];
$worker_id = isset($_GET['worker_id']) ? intval($_GET['worker_id']) : 0;

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = htmlspecialchars($_POST['message']);

    $insert_sql = "INSERT INTO messages (client_username, worker_id, message, created_at, sender) 
                   VALUES (?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("siss", $client_username, $worker_id, $message, $client_username);
    $stmt->execute();
}

// Fetch chat messages
$msg_sql = "SELECT client_username, worker_id, message, created_at, sender 
             FROM messages
             WHERE client_username = ? AND worker_id = ?";
$msg_stmt = $conn->prepare($msg_sql);
$msg_stmt->bind_param("si", $client_username, $worker_id);
$msg_stmt->execute();
$msg_result = $msg_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat with Worker</title>
    <style>
        .chat-container { width: 100%; max-width: 600px; margin: auto; background-color: #f9f9f9; padding: 20px; border-radius: 8px; }
        .chat-box { height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background-color: #fff; margin-bottom: 10px; }
        .chat-message { margin-bottom: 5px; }
        .chat-input { display: flex; gap: 10px; }
        .chat-input textarea { width: 80%; height: 60px; }
        .btn { background-color: #444; color: #fff; border: none; padding: 5px 10px; cursor: pointer; border-radius: 5px; }
        .btn:hover { background-color: #333; }
    </style>
</head>
<body>
    <div class="chat-container">
        <h2>Chat with Worker</h2>

        <div class="chat-box">
            <?php while ($msg = $msg_result->fetch_assoc()): ?>
                <div class="chat-message">
                    <strong><?php echo htmlspecialchars($msg['sender']); ?>:</strong>
                    <?php echo htmlspecialchars($msg['message']); ?>
                    <small><?php echo htmlspecialchars($msg['created_at']); ?></small>
                </div>
            <?php endwhile; ?>
        </div>

        <form method="POST" class="chat-input">
            <textarea name="message" placeholder="Type your message..." required></textarea>
            <button type="submit" class="btn">Send</button>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>

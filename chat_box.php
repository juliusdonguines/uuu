<?php
session_start();
include('db.php');

// Identify sender and receiver
if (isset($_SESSION['client_username'])) { 
    $sender = $_SESSION['client_username']; 
    $receiver_table = "workers"; 
    $account_type = "worker"; 
} elseif (isset($_SESSION['worker_username'])) { 
    $sender = $_SESSION['worker_username']; 
    $receiver_table = "clients"; 
    $account_type = "client"; 
} else { 
    echo "<script>alert('Unauthorized access. Please log in.'); window.location='login.php';</script>"; 
    exit(); 
}

// Correctly assign receiver
$receiver_username = isset($_GET['receiver']) ? trim($_GET['receiver']) : '';

// Ensure receiver exists
$receiver_query = "SELECT id, full_name FROM $receiver_table WHERE username = ?";
$stmt = $conn->prepare($receiver_query);
$stmt->bind_param("s", $receiver_username);
$stmt->execute();
$receiver_result = $stmt->get_result(); 

if ($receiver_result->num_rows === 0) { 
    echo "<script>alert('Receiver not found.'); window.location='home.php';</script>"; 
    exit(); 
} else { 
    $receiver_data = $receiver_result->fetch_assoc(); 
    $receiver_id = $receiver_data['id'];
    $receiver_name = htmlspecialchars($receiver_data['full_name']);
}

// Get sender ID from their respective table
$sender_query = "SELECT id FROM $receiver_table WHERE username = ?";
$stmt = $conn->prepare($sender_query);
$stmt->bind_param("s", $sender);
$stmt->execute();
$sender_result = $stmt->get_result();
$sender_data = $sender_result->fetch_assoc();
$sender_id = $sender_data['id'];

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $message = mysqli_real_escape_string($conn, $_POST['message']); 

    $sql = "INSERT INTO chat_messages (sender_id, receiver_id, message, account_type, created_at) 
            VALUES (?, ?, ?, ?, NOW())"; 
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $account_type); 
    $stmt->execute(); 
}

// Fetch chat messages
$chat_sql = "SELECT * FROM chat_messages 
             WHERE ((sender_id = ? AND receiver_id = ? AND account_type = ?) 
                OR (sender_id = ? AND receiver_id = ? AND account_type = ?)) 
             ORDER BY created_at ASC"; 

$stmt = $conn->prepare($chat_sql);
$stmt->bind_param("iisiii", $sender_id, $receiver_id, $account_type, $receiver_id, $sender_id, $account_type);
$stmt->execute();
$chat_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat with <?php echo $receiver_name; ?></title>
</head>
<body>
    <div class="container">
        <div class="chat-header">
            <span>Chat with <?php echo $receiver_name; ?></span>
        </div>

        <div class="chat-box" id="chatBox">
            <?php while ($chat = $chat_result->fetch_assoc()): ?>
                <div class="message <?php echo ($chat['sender_id'] == $sender_id) ? 'sent' : 'received'; ?>">
                    <?php echo htmlspecialchars($chat['message']); ?>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="form-container">
            <form method="POST">
                <textarea name="message" rows="2" required placeholder="Type your message..."></textarea>
                <button type="submit">Send</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #f3f4f6;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f9fafb;
            color: var(--text-dark);
            line-height: 1.5;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .chat-header {
            background-color: var(--primary);
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: 600;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-box {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background-color: #f9fafb;
            border: none;
            margin-bottom: 0;
        }

        .message {
            margin-bottom: 12px;
            padding: 10px 15px;
            border-radius: 18px;
            max-width: 70%;
            position: relative;
            animation: fadeIn 0.3s ease-out;
            clear: both;
        }

        .received {
            background-color: white;
            color: var(--text-dark);
            float: left;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .sent {
            background-color: var(--primary);
            color: white;
            float: right;
            border-bottom-right-radius: 4px;
        }

        .message-sender {
            font-weight: 500;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .form-container {
            background-color: white;
            padding: 15px 20px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
        }

        textarea {
            flex-grow: 1;
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 12px 20px;
            resize: none;
            outline: none;
            font-size: 14px;
        }

        button {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        button:hover {
            background-color: var(--primary-dark);
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }

        .back-link:hover {
            background-color: var(--secondary);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

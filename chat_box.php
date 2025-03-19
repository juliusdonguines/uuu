<?php session_start(); include('db.php'); 

// Identify sender and receiver
if (isset($_SESSION['client_username'])) { 
    $sender = $_SESSION['client_username']; 
    $receiver_table = "workers"; // Client messages Worker
} elseif (isset($_SESSION['worker_username'])) { 
    $sender = $_SESSION['worker_username']; 
    $receiver_table = "clients"; // Worker messages Client
} else { 
    echo "<script>alert('Unauthorized access. Please log in.'); window.location='login.php';</script>"; 
    exit(); 
} 

// Correctly assign receiver
$receiver_username = isset($_GET['receiver']) ? trim($_GET['receiver']) : ''; 

// Ensure receiver exists and get receiver's full name
$receiver_query = "SELECT full_name FROM $receiver_table WHERE username = ?";
$stmt = $conn->prepare($receiver_query);
$stmt->bind_param("s", $receiver_username);
$stmt->execute();
$receiver_result = $stmt->get_result(); 

if ($receiver_result->num_rows === 0) { 
    echo "<script>alert('Receiver not found.'); window.location='home.php';</script>"; 
    exit(); 
} else { 
    $receiver_data = $receiver_result->fetch_assoc(); 
    $receiver_name = htmlspecialchars($receiver_data['full_name']); 
} 

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $message = mysqli_real_escape_string($conn, $_POST['message']); 
    
    $sql = "INSERT INTO chat_messages (sender, receiver, message, created_at) 
            VALUES (?, ?, ?, NOW())"; 
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("sss", $sender, $receiver_username, $message); 
    $stmt->execute(); 
} 

// Fetch chat messages
$chat_sql = "SELECT * FROM chat_messages 
             WHERE (sender = ? AND receiver = ?) 
                OR (sender = ? AND receiver = ?) 
             ORDER BY created_at ASC"; 

$stmt = $conn->prepare($chat_sql);
$stmt->bind_param("ssss", $sender, $receiver_username, $receiver_username, $sender);
$stmt->execute();
$chat_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat with <?php echo $receiver_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        
        /* Scrollbar customization */
        .chat-box::-webkit-scrollbar {
            width: 6px;
        }
        
        .chat-box::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .chat-box::-webkit-scrollbar-thumb {
            background: #c5c5c5;
            border-radius: 10px;
        }
        
        .chat-box::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Animation for new messages */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .message {
                max-width: 85%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="chat-header">
            <span>Chat with <?php echo $receiver_name; ?></span>
        </div>
        
        <div class="chat-box" id="chatBox">
            <?php while ($chat = $chat_result->fetch_assoc()): ?>
                <div class="message <?php echo ($chat['sender'] == $sender) ? 'sent' : 'received'; ?>">
                    <div class="message-sender"><?php echo htmlspecialchars($chat['sender']); ?></div>
                    <?php echo htmlspecialchars($chat['message']); ?>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="form-container">
            <form method="POST" style="display: flex; width: 100%; gap: 10px;">
                <textarea name="message" rows="2" required placeholder="Type your message..."></textarea>
                <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
    
    <div style="text-align: center;">
        <a href="<?php echo isset($_SESSION['client_username']) ? 'chat_worker.php' : 'chat_client.php'; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
    
    <script>
        // Auto-scroll to bottom when page loads
        window.onload = function() {
            var chatBox = document.getElementById('chatBox');
            chatBox.scrollTop = chatBox.scrollHeight;
        };
    </script>
</body>
</html>

<?php $conn->close(); ?>
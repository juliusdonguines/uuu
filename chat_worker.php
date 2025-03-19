<?php session_start(); include('db.php');   

// Ensure client is logged in 
if (!isset($_SESSION['client_username'])) {     
    echo "<script>alert('Please log in first as a client.'); window.location='login_client.php';</script>";     
    exit(); 
}  

// Display workers for client to chat with 
$sql = "SELECT username, full_name FROM workers"; 
$result = $conn->query($sql); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message a Worker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --primary-dark: #4338ca;
            --secondary: #f3f4f6;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border: #e5e7eb;
            --success: #10b981;
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
        
        .header {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            text-align: center;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        
        .header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 24px;
        }
        
        .user-logged-in {
            background: var(--primary-dark);
            color: white;
            padding: 10px 20px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .worker-list {
            padding: 20px;
        }
        
        .worker-card {
            list-style-type: none;
            margin-bottom: 15px;
        }
        
        .worker-link {
            display: flex;
            align-items: center;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            border: 1px solid var(--border);
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.2s ease;
        }
        
        .worker-link:hover {
            border-color: var(--primary-light);
            box-shadow: var(--shadow);
            transform: translateY(-2px);
        }
        
        .worker-avatar {
            width: 50px;
            height: 50px;
            background-color: var(--primary-light);
            border-radius: 50%;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 20px;
        }
        
        .worker-info {
            flex-grow: 1;
        }
        
        .worker-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 4px;
        }
        
        .start-chat {
            color: var(--primary);
            font-size: 14px;
        }
        
        .arrow-icon {
            color: var(--primary);
            margin-left: 10px;
        }
        
        .no-workers {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }
        
        .footer {
            padding: 20px;
            background-color: var(--secondary);
            text-align: center;
            font-size: 14px;
            color: var(--text-light);
            border-top: 1px solid var(--border);
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .worker-card {
            animation: fadeIn 0.3s ease-out;
            animation-fill-mode: both;
        }
        
        .worker-card:nth-child(1) { animation-delay: 0.1s; }
        .worker-card:nth-child(2) { animation-delay: 0.2s; }
        .worker-card:nth-child(3) { animation-delay: 0.3s; }
        .worker-card:nth-child(4) { animation-delay: 0.4s; }
        .worker-card:nth-child(5) { animation-delay: 0.5s; }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .header h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-comments"></i> Message a Worker</h2>
        </div>
        
        <div class="user-logged-in">
            <span>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['client_username']); ?></strong></span>
            <a href="logout.php" style="color: white; text-decoration: none;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <div class="worker-list">
            <?php if ($result->num_rows > 0): ?>
                <ul>
                    <?php while ($worker = $result->fetch_assoc()): ?>
                        <li class="worker-card">
                            <a href="chat_box.php?receiver=<?php echo urlencode($worker['username']); ?>" class="worker-link">
                                <div class="worker-avatar">
                                    <?php echo strtoupper(substr($worker['full_name'], 0, 1)); ?>
                                </div>
                                <div class="worker-info">
                                    <div class="worker-name"><?php echo htmlspecialchars($worker['full_name']); ?></div>
                                    <div class="start-chat">Start conversation</div>
                                </div>
                                <div class="arrow-icon">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="no-workers">
                    <i class="fas fa-user-slash" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                    <p>No workers found in the system.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>Select a worker from the list above to start a conversation</p>
        </div>
    </div>
</body>
</html>
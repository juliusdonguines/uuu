<?php session_start(); include('db.php'); // Database connection  

// Check if the worker is logged in
if (!isset($_SESSION['worker_username'])) {     
    header("Location: login_worker.php");     
    exit(); 
}  

// Retrieve worker's username 
$worker_username = htmlspecialchars($_SESSION['worker_username']);  

// Fetch worker ID 
$sql = "SELECT id FROM workers WHERE username = ?"; 
$stmt = $conn->prepare($sql); 
$stmt->bind_param("s", $worker_username); 
$stmt->execute(); 
$worker_result = $stmt->get_result(); 
$worker = $worker_result->fetch_assoc(); 
$worker_id = $worker['id'];  

// Fetch completed job history with ratings and feedback 
$sql = "SELECT c.full_name AS client_name, h.schedule_date, h.schedule_time, h.status,                 
        r.rating, r.feedback         
        FROM hires h         
        JOIN clients c ON h.client_id = c.id         
        LEFT JOIN ratings r ON h.id = r.worker_id         
        WHERE h.worker_id = ? AND h.status = 'Completed'";  

$history_stmt = $conn->prepare($sql); 
$history_stmt->bind_param("i", $worker_id); 
$history_stmt->execute(); 
$history_result = $history_stmt->get_result(); 
?>  

<!DOCTYPE html> 
<html lang="en"> 
<head>     
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">     
    <title>Worker History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>         
        :root {
            --primary-color: #4a6fa5;
            --secondary-color: #166088;
            --accent-color: #4caf50;
            --text-color: #333;
            --text-light: #666;
            --background-color: #f8f9fa;
            --card-color: #fff;
            --border-color: #e0e0e0;
            --shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            --hover-shadow: 0 5px 24px rgba(0, 0, 0, 0.12);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {             
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;             
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .page-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .header p {
            opacity: 0.8;
            font-size: 16px;
        }
        
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
            flex-grow: 1;
        }
        
        .card {
            background-color: var(--card-color);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: var(--hover-shadow);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }
        
        .card-title {
            font-size: 20px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .card-body {
            overflow-x: auto;
        }
        
        table {             
            width: 100%;             
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background-color: rgba(74, 111, 165, 0.08);
            color: var(--secondary-color);
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background-color: rgba(0, 0, 0, 0.01);
        }
        
        .rating {
            display: flex;
            align-items: center;
        }
        
        .stars {
            color: #ffc107;
            margin-left: 5px;
        }
        
        .empty-message {
            padding: 50px 0;
            text-align: center;
            color: var(--text-light);
            font-size: 16px;
        }
        
        .empty-message i {
            font-size: 50px;
            margin-bottom: 20px;
            opacity: 0.3;
            display: block;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
            color: white;
            background-color: var(--accent-color);
        }
        
        .feedback-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .feedback-cell:hover {
            white-space: normal;
            overflow: visible;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .footer {
            background-color: var(--secondary-color);
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: auto;
        }
        
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            th, td {
                padding: 10px 8px;
                font-size: 14px;
            }
            
            th {
                font-size: 12px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
        }
    </style> 
</head> 
<body>
    <div class="page-container">
        <div class="header">
            <h1>Work History</h1>
            <p>Your completed jobs and client feedback</p>
        </div>
        
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-history"></i> Completed Jobs
                    </h2>
                </div>
                
                <div class="card-body">
                    <?php if ($history_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user"></i> Client</th>
                                        <th><i class="fas fa-calendar"></i> Date</th>
                                        <th><i class="fas fa-clock"></i> Time</th>
                                        <th><i class="fas fa-info-circle"></i> Status</th>
                                        <th><i class="fas fa-star"></i> Rating</th>
                                        <th><i class="fas fa-comment"></i> Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($history = $history_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($history['client_name']); ?></td>
                                            <td><?php echo htmlspecialchars($history['schedule_date']); ?></td>
                                            <td><?php echo htmlspecialchars($history['schedule_time']); ?></td>
                                            <td>
                                                <span class="badge">
                                                    <?php echo htmlspecialchars($history['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($history['rating']): ?>
                                                    <div class="rating">
                                                        <?php echo htmlspecialchars($history['rating']); ?>
                                                        <div class="stars">
                                                            <?php 
                                                            $rating = intval($history['rating']);
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                if ($i <= $rating) {
                                                                    echo '<i class="fas fa-star"></i>';
                                                                } else {
                                                                    echo '<i class="far fa-star"></i>';
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span>N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="feedback-cell">
                                                <?php echo $history['feedback'] ? htmlspecialchars($history['feedback']) : '<em>No feedback</em>'; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-message">
                            <i class="fas fa-clipboard-list"></i>
                            <p>You don't have any completed jobs yet.</p>
                            <p>Completed jobs and client feedback will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="actions">
                    <a href="worker_home.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer">
            &copy; <?php echo date('Y'); ?> Worker Services Platform
        </div>
    </div>
</body> 
</html>  

<?php $conn->close(); ?>
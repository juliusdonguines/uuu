<?php 
session_start(); 
include('db.php');   

if (!isset($_SESSION['worker_username'])) {     
    echo "<script>alert('Please log in first.'); window.location='login_worker.php';</script>";     
    exit(); 
}  

// Ensure client ID is passed 
if (!isset($_GET['client_id']) || empty($_GET['client_id'])) {     
    echo "<script>alert('Invalid request.'); window.location='view_clients.php';</script>";     
    exit(); 
}  

$client_id = intval($_GET['client_id']);  

// Get client information to display
$client_sql = "SELECT full_name FROM clients WHERE id = ?";
$client_stmt = $conn->prepare($client_sql);
$client_stmt->bind_param("i", $client_id);
$client_stmt->execute();
$client_result = $client_stmt->get_result();
$client_name = "Client";

if($client_result->num_rows > 0) {
    $client_data = $client_result->fetch_assoc();
    $client_name = $client_data['full_name'];
}

// Handle report submission 
if ($_SERVER["REQUEST_METHOD"] === "POST") {     
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);     
    $worker_username = $_SESSION['worker_username'];      
    
    $sql = "INSERT INTO client_reports (client_id, worker_username, reason, created_at)              
            VALUES (?, ?, ?, NOW())";     
    $stmt = $conn->prepare($sql);     
    $stmt->bind_param("iss", $client_id, $worker_username, $reason);      
    
    if ($stmt->execute()) {         
        echo "<script>alert('Report submitted successfully!'); window.location='view_clients.php';</script>";     
    } else {         
        echo "<script>alert('Failed to submit report. Please try again.'); window.location='view_clients.php';</script>";     
    } 
} 
?>  

<!DOCTYPE html> 
<html lang="en"> 
<head>     
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Client</title> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head> 
<body>     
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <h1><i class="fas fa-flag"></i> Report Client</h1>
                <p>You are reporting: <span class="highlight"><?php echo htmlspecialchars($client_name); ?></span></p>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="reason">Reason for Reporting:</label>
                    <textarea name="reason" id="reason" rows="6" required placeholder="Please describe the issue in detail..."></textarea>
                    <small>Your report will be reviewed by our administrators.</small>
                </div>
                
                <div class="form-actions">
                    <a href="view_clients.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</body> 
</html>  

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
        background-color: #f5f5f5;
        color: #333;
        line-height: 1.6;
        padding: 20px;
    }
    
    .container {
        max-width: 600px;
        margin: 40px auto;
    }
    
    .form-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .form-header {
        background-color: #f8f8f8;
        padding: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .form-header h1 {
        color: #333;
        font-size: 24px;
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .form-header h1 i {
        color: #e74c3c;
        margin-right: 10px;
    }
    
    .form-header p {
        color: #666;
        font-size: 15px;
    }
    
    .form-header .highlight {
        font-weight: 600;
        color: #333;
    }
    
    form {
        padding: 25px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #444;
    }
    
    textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: vertical;
        font-size: 15px;
        transition: border 0.3s;
    }
    
    textarea:focus {
        border-color: #3498db;
        outline: none;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.1);
    }
    
    small {
        display: block;
        margin-top: 8px;
        color: #777;
        font-size: 13px;
    }
    
    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 10px;
    }
    
    .btn {
        padding: 10px 20px;
        border-radius: 4px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .btn i {
        margin-right: 8px;
    }
    
    .btn-primary {
        background-color: #e74c3c;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #c0392b;
    }
    
    .btn-secondary {
        background-color: #f2f2f2;
        color: #333;
    }
    
    .btn-secondary:hover {
        background-color: #e6e6e6;
    }
    
    /* Responsive adjustments */
    @media (max-width: 480px) {
        .container {
            margin: 20px auto;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
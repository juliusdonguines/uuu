<?php
session_start();
include('db.php');

if (!isset($_SESSION['worker_username'])) {
    echo "<script>alert('Unauthorized access. Please log in as a worker.'); window.location='login_worker.php';</script>";
    exit();
}

$worker_username = $_SESSION['worker_username'];

// Fetch worker ID
$sql = "SELECT id FROM workers WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $worker_username);
$stmt->execute();
$worker_result = $stmt->get_result();
$worker = $worker_result->fetch_assoc();

if (!$worker) {
    echo "<script>alert('Worker not found.'); window.location='worker_home.php';</script>";
    exit();
}

$worker_id = $worker['id'];

// Update hire status logic
if (isset($_GET['id']) && isset($_GET['status'])) {
    $hire_id = intval($_GET['id']);
    $status = $_GET['status'];

    $update_sql = "UPDATE hires SET status = ? WHERE id = ? AND worker_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $status, $hire_id, $worker_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('Status updated successfully!'); window.location='manage_hires.php';</script>";
    } else {
        echo "<script>alert('Failed to update status. Please try again.'); window.location='manage_hires.php';</script>";
    }
}

// Fetch Service Requests with All Statuses
$fetch_sql = "SELECT h.id, h.client_username, h.schedule_date, h.schedule_time, h.status
               FROM hires h
               WHERE h.worker_id = ?
               ORDER BY 
                    CASE 
                        WHEN h.status = 'Pending' THEN 1
                        WHEN h.status = 'Accepted' THEN 2
                        WHEN h.status = 'On Duty' THEN 3
                        WHEN h.status = 'Completed' THEN 4
                        WHEN h.status = 'Cancelled' THEN 5
                        ELSE 6
                    END";

$fetch_stmt = $conn->prepare($fetch_sql);
$fetch_stmt->bind_param("i", $worker_id);
$fetch_stmt->execute();
$result = $fetch_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Hires</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2>Manage Hires</h2>

        <?php if ($result->num_rows > 0): ?>
            <div class="requests-container">
                <?php while ($hire = $result->fetch_assoc()): 
                    $statusClass = strtolower(str_replace(' ', '', $hire['status']));
                ?>
                    <div class="request <?php echo $statusClass; ?>">
                        <div class="status-badge <?php echo $statusClass; ?>">
                            <?php 
                                switch($hire['status']) {
                                    case 'Pending': echo '<i class="fas fa-clock"></i> '; break;
                                    case 'Accepted': echo '<i class="fas fa-thumbs-up"></i> '; break;
                                    case 'On Duty': echo '<i class="fas fa-briefcase"></i> '; break;
                                    case 'Completed': echo '<i class="fas fa-check-circle"></i> '; break;
                                    case 'Cancelled': echo '<i class="fas fa-times-circle"></i> '; break;
                                }
                                echo htmlspecialchars($hire['status']); 
                            ?>
                        </div>

                        <p><strong>Client Username:</strong> <?php echo htmlspecialchars($hire['client_username']); ?></p>
                        <p><strong>Schedule Date:</strong> <?php echo htmlspecialchars($hire['schedule_date']); ?></p>
                        <p><strong>Schedule Time:</strong> <?php echo htmlspecialchars($hire['schedule_time']); ?></p>

                        <div class="btn-container">
                            <?php if ($hire['status'] === 'Accepted'): ?>
                                <a href='manage_hires.php?id=<?php echo $hire['id']; ?>&status=Cancelled' class="btn cancel">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <a href='manage_hires.php?id=<?php echo $hire['id']; ?>&status=On Duty' class="btn duty">
                                    <i class="fas fa-briefcase"></i> On Duty
                                </a>
                            <?php elseif ($hire['status'] === 'Pending'): ?>
                                <a href='manage_hires.php?id=<?php echo $hire['id']; ?>&status=Accepted' class="btn accept">
                                    <i class="fas fa-check"></i> Accept
                                </a>
                                <a href='manage_hires.php?id=<?php echo $hire['id']; ?>&status=Cancelled' class="btn cancel">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php elseif ($hire['status'] === 'On Duty'): ?>
                                <a href='manage_hires.php?id=<?php echo $hire['id']; ?>&status=Completed' class="btn complete">
                                    <i class="fas fa-check-circle"></i> Complete
                                </a>
                            <?php elseif ($hire['status'] === 'Cancelled'): ?>
                                <p class="status-text cancelled"><i class="fas fa-times-circle"></i> Request Cancelled</p>
                            <?php elseif ($hire['status'] === 'Completed'): ?>
                                <p class="status-text completed"><i class="fas fa-check-circle"></i> Job Completed</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="no-requests"><i class="fas fa-inbox"></i> No service requests found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>

<style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --cancel-color: #e74c3c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h2 {
            font-size: 32px;
            color: var(--secondary-color);
            margin-bottom: 30px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            position: relative;
        }
        
        h2:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background-color: var(--accent-color);
        }
        
        .requests-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .request {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .request:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }
        
        .request::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .request.pending::before {
            background-color: var(--warning-color);
        }
        
        .request.accepted::before {
            background-color: var(--primary-color);
        }
        
        .request.onduty::before {
            background-color: var(--accent-color);
        }
        
        .request.completed::before {
            background-color: var(--success-color);
        }
        
        .request.cancelled::before {
            background-color: var(--cancel-color);
        }
        
        .request p {
            margin-bottom: 10px;
            font-size: 15px;
        }
        
        .request strong {
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 15px;
            color: #fff;
        }
        
        .status-badge.pending {
            background-color: var(--warning-color);
        }
        
        .status-badge.accepted {
            background-color: var(--primary-color);
        }
        
        .status-badge.onduty {
            background-color: var(--accent-color);
        }
        
        .status-badge.completed {
            background-color: var(--success-color);
        }
        
        .status-badge.cancelled {
            background-color: var(--cancel-color);
        }
        
        .btn-container {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            color: #fff;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .btn.accept {
            background-color: var(--primary-color);
        }
        
        .btn.accept:hover {
            background-color: #2980b9;
        }
        
        .btn.duty {
            background-color: var(--accent-color);
        }
        
        .btn.duty:hover {
            background-color: #c0392b;
        }
        
        .btn.complete {
            background-color: var(--success-color);
        }
        
        .btn.complete:hover {
            background-color: #219653;
        }
        
        .btn.cancel {
            background-color: var(--cancel-color);
        }
        
        .btn.cancel:hover {
            background-color: #c0392b;
        }
        
        .status-text {
            margin-top: 10px;
            font-weight: 500;
        }
        
        .status-text.cancelled {
            color: var(--cancel-color);
        }
        
        .status-text.completed {
            color: var(--success-color);
        }
        
        .no-requests {
            text-align: center;
            padding: 50px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            font-size: 18px;
            color: #777;
        }
        
        @media (max-width: 768px) {
            .requests-container {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 15px;
            }
            
            h2 {
                font-size: 24px;
            }
        }
    </style>
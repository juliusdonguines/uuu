<?php 
session_start(); 
include('db.php'); 

// Check if admin is logged in 
if (!isset($_SESSION['admin_username'])) {
    echo "<script>alert('Unauthorized access! Please log in as admin.'); window.location='login_admin.php';</script>";
    exit();
}

// Fetch all worker reports
$sql = "SELECT wr.id, w.full_name AS worker_name, wr.client_username, wr.reason, wr.created_at
        FROM worker_reports wr
        JOIN workers w ON wr.worker_id = w.id
        ORDER BY wr.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Reports</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }
        
        table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            text-align: left;
            padding: 12px 15px;
            border-bottom: 2px solid #ddd;
        }
        
        table td {
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        
        table tr:hover {
            background-color: #f1f1f1;
        }
        
        .empty-table {
            text-align: center;
            padding: 20px;
            color: #777;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 16px;
            background-color: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .back-link:hover {
            background-color: #1a252f;
        }
        
        .report-count {
            color: #777;
            margin-bottom: 15px;
        }
        
        .date-column {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Worker Reports</h1>
        
        <?php
        $reportCount = $result->num_rows;
        echo "<div class='report-count'>Showing $reportCount report" . ($reportCount != 1 ? "s" : "") . "</div>";
        ?>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Worker Name</th>
                        <th>Reported By</th>
                        <th>Reason</th>
                        <th>Reported On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($report = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['worker_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['client_username']); ?></td>
                            <td><?php echo htmlspecialchars($report['reason']); ?></td>
                            <td class="date-column">
                                <?php 
                                    $date = new DateTime($report['created_at']);
                                    echo $date->format('M d, Y - g:i A');
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-table">No reports found.</div>
        <?php endif; ?>
        
        <a href="admin_dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
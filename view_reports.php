<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['admin_username'])) {
    echo "<script>alert('Please log in as admin.'); window.location='login_admin.php';</script>";
    exit();
}

$sql = "SELECT r.id, w.full_name AS worker_name, r.client_username, r.reason, r.created_at 
        FROM reports r
        JOIN workers w ON r.worker_id = w.id
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Reports</title>
</head>
<body>
    <h1>Reported Workers</h1>

    <?php if ($result->num_rows > 0): ?>
        <table border="1">
            <tr>
                <th>Worker Name</th>
                <th>Reported By</th>
                <th>Reason</th>
                <th>Date</th>
            </tr>
            <?php while ($report = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($report['worker_name']); ?></td>
                    <td><?php echo htmlspecialchars($report['client_username']); ?></td>
                    <td><?php echo htmlspecialchars($report['reason']); ?></td>
                    <td><?php echo htmlspecialchars($report['created_at']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No reports available.</p>
    <?php endif; ?>

    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>

<?php $conn->close(); ?>

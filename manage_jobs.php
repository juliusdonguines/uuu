<?php session_start(); include('db.php'); // Database connection 

// Delete Job Offer
if (isset($_GET['delete'])) {
    $job_id = intval($_GET['id']);
    
    $delete_sql = "DELETE FROM job_offers WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    
    echo "<script>alert('Job offer deleted successfully.'); window.location='manage_jobs.php';</script>";
}

// Fetch job offers with client full name and worker full name
$job_sql = "
    SELECT j.id, c.full_name AS client_name, j.job_title, j.job_description,
            j.schedule_date, j.schedule_time, w.full_name AS worker_name
    FROM job_offers j
    JOIN clients c ON j.client_id = c.id
    LEFT JOIN workers w ON j.accepted_by = w.id 
";
$job_result = $conn->query($job_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Job Offers</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #5a4534;
            --primary-light: #8b6e4f;
            --secondary: #1e88e5;
            --danger: #e53935;
            --success: #43a047;
            --gray-light: #f8f9fa;
            --gray: #e9ecef;
            --dark: #343a40;
            --text: #212529;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-light);
            color: var(--text);
            line-height: 1.6;
        }
        
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--primary);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .logo {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo h1 {
            font-size: 24px;
            letter-spacing: 1px;
        }
        
        .nav-list {
            list-style: none;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-light);
            color: white;
            border-left: 4px solid white;
        }
        
        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-x: auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray);
        }
        
        .page-title {
            font-size: 24px;
            color: var(--primary);
        }
        
        .action-button {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .action-button:hover {
            background-color: #1565c0;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .card-header {
            padding: 15px 20px;
            background-color: white;
            border-bottom: 1px solid var(--gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 18px;
            color: var(--primary);
        }
        
        .card-body {
            padding: 0;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background-color: var(--gray-light);
            color: var(--dark);
            font-weight: 600;
            text-align: left;
            padding: 12px 15px;
            border-bottom: 2px solid var(--gray);
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--gray);
            vertical-align: middle;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tr:hover {
            background-color: var(--gray-light);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #ffecb3;
            color: #ff8f00;
        }
        
        .status-accepted {
            background-color: #c8e6c9;
            color: var(--success);
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            color: white;
            margin-right: 5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: opacity 0.3s;
        }
        
        .action-btn:hover {
            opacity: 0.85;
        }
        
        .edit-btn {
            background-color: var(--secondary);
        }
        
        .delete-btn {
            background-color: var(--danger);
        }
        
        .empty-state {
            padding: 40px;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .truncate {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }
        
        .tooltip {
            position: relative;
            display: inline-block;
        }
        
        .tooltip .tooltip-text {
            visibility: hidden;
            width: 220px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 10px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
        
        @media screen and (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 10px;
            }
            
            .sidebar .nav-link {
                padding: 8px 15px;
            }
            
            .main-content {
                padding: 15px;
            }
            
            .data-table th, 
            .data-table td {
                padding: 8px 10px;
            }
        }
        /* Feedback card styles */
        .feedback-list {
            padding: 0;
        }
        
        .feedback-card {
            border-bottom: 1px solid var(--gray);
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .feedback-card:last-child {
            border-bottom: none;
        }
        
        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h1>JobConnect</h1>
            </div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_jobs.php" class="nav-link active">
                        <i class="fas fa-briefcase"></i> Job Offers
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_users.php" class="nav-link">
                        <i class="fas fa-hard-hat"></i> Manage Users
                    </a>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Manage Job Offers</h1>
                <a href="add_job.php" class="action-button">
                    <i class="fas fa-plus"></i> Add New Job
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">All Job Offers</h2>
                    <div class="header-actions">
                        <input type="text" id="searchInput" placeholder="Search jobs..." 
                               style="padding: 6px 10px; border: 1px solid var(--gray); border-radius: 4px;">
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($job_result->num_rows > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Job Title</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($job = $job_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($job['client_name']); ?></td>
                                        <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                                        <td class="tooltip">
                                            <span class="truncate"><?php echo htmlspecialchars($job['job_description']); ?></span>
                                            <span class="tooltip-text"><?php echo htmlspecialchars($job['job_description']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($job['schedule_date']); ?></td>
                                        <td><?php echo htmlspecialchars($job['schedule_time']); ?></td>
                                        <td>
                                            <?php if (!empty($job['worker_name'])): ?>
                                                <span class="status-badge status-accepted">
                                                    <i class="fas fa-check-circle"></i> 
                                                    Accepted by <?php echo htmlspecialchars($job['worker_name']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge status-pending">
                                                    <i class="fas fa-clock"></i> 
                                                    Pending Acceptance
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="action-btn edit-btn">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="?delete=1&id=<?php echo $job['id']; ?>" class="action-btn delete-btn" 
                                               onclick="return confirm('Are you sure you want to delete this job offer?');">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-briefcase"></i>
                            <h3>No Job Offers Found</h3>
                            <p>There are currently no job offers available in the system.</p>
                            <a href="add_job.php" class="action-button" style="margin-top: 15px;">
                                <i class="fas fa-plus"></i> Create Your First Job Offer
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let input = this.value.toLowerCase();
            let rows = document.querySelectorAll('.data-table tbody tr');
            
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
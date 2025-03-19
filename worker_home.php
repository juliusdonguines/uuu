<?php
session_start();
include('db.php');

if (!isset($_SESSION['worker_username'])) {
    header("Location: login_worker.php");
    exit();
}

$worker_username = htmlspecialchars($_SESSION['worker_username']);

// Fetch Worker ID
$sql = "SELECT id FROM workers WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $worker_username);
$stmt->execute();
$worker_result = $stmt->get_result();
$worker = $worker_result->fetch_assoc();
$worker_id = $worker['id'];

// Mark Job as Completed
if (isset($_POST['done_working'])) {
    $hire_id = intval($_POST['hire_id']);

    // Update hire status to 'Completed'
    $update_hires_sql = "UPDATE hires SET status = 'Completed' WHERE worker_id = ? AND id = ?";
    $update_hires_stmt = $conn->prepare($update_hires_sql);
    $update_hires_stmt->bind_param("ii", $worker_id, $hire_id);
    $update_hires_stmt->execute();

    echo "<script>alert('Job marked as completed successfully!'); window.location='worker_home.php';</script>";
}


// Fetch Scheduled Hires (Improved Query)
$schedule_sql = "SELECT h.id AS hire_id, j.job_title, j.job_description, 
                        h.schedule_date, h.schedule_time, 
                        h.client_username, c.full_name AS client_name, 
                        c.begy, c.residential_address
                 FROM hires h
                 LEFT JOIN job_offers j ON h.job_id = j.id
                 LEFT JOIN clients c ON h.client_id = c.id
                 WHERE h.worker_id = ? AND (h.status = 'Accepted' OR h.status = 'On Duty')";
$schedule_stmt = $conn->prepare($schedule_sql);
$schedule_stmt->bind_param("i", $worker_id);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();

// Fetch Available Job Offers
$job_sql = "SELECT j.id, j.job_title, j.job_description, j.schedule_date, j.schedule_time, 
                    j.client_id, c.full_name AS client_name, c.begy
             FROM job_offers j
             JOIN clients c ON j.client_id = c.id
             WHERE j.status = 'Pending'";
$job_result = $conn->query($job_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>WorkerHub</h2>
                <p>Worker Portal</p>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="worker_home.php" class="nav-link active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="view_clients.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>View Clients</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="worker_profile.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        <span>Your Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="edit_worker_profile.php" class="nav-link">
                        <i class="fas fa-user-edit"></i>
                        <span>Edit Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_hires.php" class="nav-link">
                        <i class="fas fa-briefcase"></i>
                        <span>Manage Hires</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="worker_history.php" class="nav-link">
                        <i class="fas fa-history"></i>
                        <span>History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="chat_client.php" class="nav-link">
                        <i class="fas fa-comment-alt"></i>
                        <span>Messages</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1>Hello, <?php echo $worker_username; ?>!</h1>
                <div class="breadcrumb">Dashboard > Home</div>
            </div>
            
            <!-- Scheduled Hires Section -->
            <div class="card">
                <div class="card-header">
                    <h2>My Scheduled Jobs</h2>
                </div>
                <div class="card-body">
                    <?php if ($schedule_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Client</th>
                                        <th>Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($schedule = $schedule_result->fetch_assoc()): ?>
                                        <?php 
                                            $today = date('Y-m-d');
                                            $show_map = ($today === $schedule['schedule_date']);
                                            $date_formatted = date('M d, Y', strtotime($schedule['schedule_date']));
                                            $time_formatted = date('h:i A', strtotime($schedule['schedule_time']));
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($schedule['job_title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars(substr($schedule['job_description'], 0, 50)) . (strlen($schedule['job_description']) > 50 ? '...' : ''); ?></td>
                                            <td><?php echo $date_formatted; ?></td>
                                            <td><?php echo $time_formatted; ?></td>
                                            <td><?php echo htmlspecialchars($schedule['client_name']); ?></td>
                                            <td><?php echo htmlspecialchars($schedule['begy']); ?></td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <?php if ($show_map): ?>
                                                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($schedule['residential_address']); ?>" 
                                                        target="_blank" class="btn btn-info btn-sm">
                                                            <i class="fas fa-map-marker-alt"></i> Map
                                                        </a>
                                                    <?php endif; ?>
                                                    <form method="POST">
                                                        <input type="hidden" name="hire_id" value="<?php echo $schedule['hire_id']; ?>">
                                                        <button type="submit" name="done_working" class="btn btn-success btn-sm">
                                                            <i class="fas fa-check"></i> Complete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Mobile view job cards -->
                        <div class="job-grid">
                            <?php 
                            // Reset the result set pointer
                            $schedule_result->data_seek(0);
                            while ($schedule = $schedule_result->fetch_assoc()): 
                                $today = date('Y-m-d');
                                $show_map = ($today === $schedule['schedule_date']);
                                $date_formatted = date('M d, Y', strtotime($schedule['schedule_date']));
                                $time_formatted = date('h:i A', strtotime($schedule['schedule_time']));
                            ?>
                                <div class="job-card">
                                    <div class="job-card-header">
                                        <div class="job-card-title"><?php echo htmlspecialchars($schedule['job_title']); ?></div>
                                        <div class="badge badge-success">Scheduled</div>
                                    </div>
                                    <div class="job-card-body">
                                        <div>
                                            <span class="job-card-label">Description</span>
                                            <span class="job-card-value"><?php echo htmlspecialchars(substr($schedule['job_description'], 0, 100)) . (strlen($schedule['job_description']) > 100 ? '...' : ''); ?></span>
                                        </div>
                                        <div>
                                            <span class="job-card-label">Client</span>
                                            <span class="job-card-value"><?php echo htmlspecialchars($schedule['client_name']); ?></span>
                                        </div>
                                        <div>
                                            <span class="job-card-label">Schedule</span>
                                            <span class="job-card-value"><?php echo $date_formatted . ' at ' . $time_formatted; ?></span>
                                        </div>
                                        <div>
                                            <span class="job-card-label">Location</span>
                                            <span class="job-card-value"><?php echo htmlspecialchars($schedule['begy']); ?></span>
                                        </div>
                                    </div>
                                    <div class="job-card-footer">
                                        <?php if ($show_map): ?>
                                            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($schedule['residential_address']); ?>" 
                                            target="_blank" class="btn btn-info btn-sm" style="margin-right: 0.5rem;">
                                                <i class="fas fa-map-marker-alt"></i> Map
                                            </a>
                                        <?php endif; ?>
                                        <form method="POST">
                                            <input type="hidden" name="hire_id" value="<?php echo $schedule['hire_id']; ?>">
                                            <button type="submit" name="done_working" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> Complete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="text-align: center; padding: 2rem 0;">
                            <i class="fas fa-calendar-alt" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                            <h3>No Scheduled Jobs</h3>
                            <p>You don't have any scheduled jobs at the moment. Check available job offers below.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Available Jobs Section -->
            <div class="card">
                <div class="card-header">
                    <h2>Available Job Offers</h2>
                </div>
                <div class="card-body">
                    <?php if ($job_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Description</th>
                                        <th>Schedule</th>
                                        <th>Client</th>
                                        <th>Location</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($job = $job_result->fetch_assoc()): ?>
                                        <?php 
                                            $date_formatted = date('M d, Y', strtotime($job['schedule_date']));
                                            $time_formatted = date('h:i A', strtotime($job['schedule_time']));
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($job['job_title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars(substr($job['job_description'], 0, 50)) . (strlen($job['job_description']) > 50 ? '...' : ''); ?></td>
                                            <td><?php echo $date_formatted . ' at ' . $time_formatted; ?></td>
                                            <td><?php echo htmlspecialchars($job['client_name']); ?></td>
                                            <td><?php echo htmlspecialchars($job['begy']); ?></td>
                                            <td>
                                                <form method="POST" action="accept_job.php">
                                                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-handshake"></i> Accept
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Mobile view job cards -->
                        <div class="job-grid">
                            <?php 
                            // Reset the result set pointer
                            $job_result->data_seek(0);
                            while ($job = $job_result->fetch_assoc()): 
                                $date_formatted = date('M d, Y', strtotime($job['schedule_date']));
                                $time_formatted = date('h:i A', strtotime($job['schedule_time']));
                            ?>
                                <div class="job-card">
                                    <div class="job-card-header">
                                        <div class="job-card-title"><?php echo htmlspecialchars($job['job_title']); ?></div>
                                        <div class="badge badge-warning">Available</div>
                                    </div>
                                    <div class="job-card-body">
                                        <div>
                                            <span class="job-card-label">Description</span>
                                            <span class="job-card-value"><?php echo htmlspecialchars(substr($job['job_description'], 0, 100)) . (strlen($job['job_description']) > 100 ? '...' : ''); ?></span>
                                        </div>
                                        <div>
                                            <span class="job-card-label">Client</span>
                                            <span class="job-card-value"><?php echo htmlspecialchars($job['client_name']); ?></span>
                                        </div>
                                        <div>
                                            <span class="job-card-label">Schedule</span>
                                            <span class="job-card-value"><?php echo $date_formatted . ' at ' . $time_formatted; ?></span>
                                        </div>
                                        <div>
                                            <span class="job-card-label">Location</span>
                                            <span class="job-card-value"><?php echo htmlspecialchars($job['begy']); ?></span>
                                        </div>
                                    </div>
                                    <div class="job-card-footer">
                                        <form method="POST" action="accept_job.php">
                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-handshake"></i> Accept Job
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="text-align: center; padding: 2rem 0;">
                            <i class="fas fa-briefcase" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                            <h3>No Job Offers</h3>
                            <p>There are no available job offers at the moment. Please check back later.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Add active class to current nav link
        const currentLocation = location.href;
        const menuItems = document.querySelectorAll('.nav-link');
        menuItems.forEach(item => {
            if(item.href === currentLocation) {
                item.classList.add('active');
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
<style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #475569;
            --light: #f8fafc;
            --dark: #1e293b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.05);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f1f5f9;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background-color: var(--dark);
            color: var(--light);
            padding: 1.5rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            box-shadow: 2px 0 5px var(--shadow);
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--light);
        }
        
        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 0.5rem;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--light);
        }
        
        .nav-link.active {
            background-color: var(--primary);
            color: var(--light);
            border-radius: 0 4px 4px 0;
        }
        
        .nav-link i {
            margin-right: 0.75rem;
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
        }
        
        /* Main content styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
            transition: all 0.3s ease;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .page-header .breadcrumb {
            font-size: 0.875rem;
            color: var(--secondary);
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Table styles */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        table th {
            font-weight: 600;
            background-color: rgba(241, 245, 249, 0.5);
            color: var(--secondary);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        table tr:hover td {
            background-color: rgba(241, 245, 249, 0.5);
        }
        
        /* Button styles */
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.875rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #15803d;
        }
        
        .btn-info {
            background-color: var(--info);
            color: white;
        }
        
        .btn-info:hover {
            background-color: #2563eb;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }
        
        /* Status badge */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: rgba(34, 197, 94, 0.1);
            color: var(--success);
        }
        
        .badge-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        /* Job card grid for mobile view */
        .job-grid {
            display: none;
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                z-index: 100;
            }
            
            .sidebar-header h2, .sidebar-header p, .nav-link span {
                display: none;
            }
            
            .sidebar-header {
                padding: 1rem 0;
                text-align: center;
            }
            
            .nav-link {
                padding: 0.75rem 0;
                justify-content: center;
            }
            
            .nav-link i {
                margin-right: 0;
                font-size: 1.25rem;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                display: none;
            }
            
            .job-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .job-card {
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px var(--shadow);
                padding: 1rem;
            }
            
            .job-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 0.75rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid var(--border);
            }
            
            .job-card-title {
                font-weight: 600;
                font-size: 1.1rem;
            }
            
            .job-card-body div {
                margin-bottom: 0.5rem;
            }
            
            .job-card-label {
                font-size: 0.8rem;
                color: var(--secondary);
                display: block;
            }
            
            .job-card-value {
                font-weight: 500;
            }
            
            .job-card-footer {
                margin-top: 1rem;
                padding-top: 0.75rem;
                border-top: 1px solid var(--border);
                display: flex;
                justify-content: flex-end;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 1rem;
            }
        }
    </style>
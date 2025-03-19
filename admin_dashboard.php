<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin_username'])) {
    echo "<script>alert('Unauthorized access! Please log in as admin.'); window.location='login_admin.php';</script>";
    exit();
}

// Fetch data for Workers
$worker_data = [];
$worker_labels = [];
$worker_sql = "SELECT DATE_FORMAT(created_at, '%M') AS month, COUNT(*) AS total 
               FROM workers 
               GROUP BY month 
               ORDER BY MIN(created_at)";
$worker_result = $conn->query($worker_sql);

while ($row = $worker_result->fetch_assoc()) {
    $worker_labels[] = $row['month'];
    $worker_data[] = $row['total'];
}

// Fetch data for Clients
$client_data = [];
$client_labels = [];
$client_sql = "SELECT DATE_FORMAT(created_at, '%M') AS month, COUNT(*) AS total 
               FROM hires 
               GROUP BY month 
               ORDER BY MIN(created_at)";
$client_result = $conn->query($client_sql);

while ($row = $client_result->fetch_assoc()) {
    $client_labels[] = $row['month'];
    $client_data[] = $row['total'];
}

// Fetch Job Post Data (Monthly Total)
$job_posts = [];
$job_labels = [];
$job_post_sql = "SELECT DATE_FORMAT(created_at, '%M') AS month, COUNT(*) AS total 
                 FROM job_offers 
                 GROUP BY month 
                 ORDER BY MIN(created_at)";
$job_post_result = $conn->query($job_post_sql);

while ($row = $job_post_result->fetch_assoc()) {
    $job_labels[] = $row['month'];
    $job_posts[] = $row['total'];
}

// Fetch Detailed Job List
$job_details_sql = "SELECT j.job_title, j.status, c.full_name AS client_name, w.full_name AS worker_name 
                    FROM job_offers j
                    LEFT JOIN clients c ON j.client_id = c.id
                    LEFT JOIN workers w ON j.accepted_by = w.id
                    ORDER BY j.created_at DESC";
$job_details_result = $conn->query($job_details_sql);
// Total Job Posts for Current Month
$current_month_sql = "SELECT COUNT(*) AS total FROM job_offers WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$current_month_result = $conn->query($current_month_sql);
$current_month_total = $current_month_result->fetch_assoc()['total'];

// Total Job Posts for Current Year
$current_year_sql = "SELECT COUNT(*) AS total FROM job_offers WHERE YEAR(created_at) = YEAR(CURRENT_DATE())";
$current_year_result = $conn->query($current_year_sql);
$current_year_total = $current_year_result->fetch_assoc()['total'];
// Total Workers
$total_workers_sql = "SELECT COUNT(*) AS total_workers FROM workers";
$total_workers_result = $conn->query($total_workers_sql);
$total_workers = $total_workers_result->fetch_assoc()['total_workers'];

// Total Clients
$total_clients_sql = "SELECT COUNT(*) AS total_clients FROM clients";
$total_clients_result = $conn->query($total_clients_sql);
$total_clients = $total_clients_result->fetch_assoc()['total_clients'];

// Overall Total (Workers + Clients)
$total_users = $total_workers + $total_clients;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #3b4863;
            --secondary: #506690;
            --accent: #85334e;
            --light: #f8f9fa;
            --dark: #344054;
            --success: #1e7c45;
            --info: #0284c7;
            --warning: #f59e0b;
            --danger: #dc2626;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --pending: #f97316;
            --completed: #10b981;
            --cancelled: #ef4444;
            --border-radius: 6px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.14);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Roboto', sans-serif;
        }

        body {
            background-color: #f5f7fb;
            min-height: 100vh;
            display: flex;
            color: var(--dark);
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            position: fixed;
            height: 100vh;
            padding: 0;
            box-shadow: var(--shadow-lg);
            z-index: 100;
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 10px;
        }

        .sidebar-brand {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .sidebar-subtitle {
            font-size: 12px;
            opacity: 0.7;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 12px 20px;
            transition: var(--transition);
            font-weight: 500;
            position: relative;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid var(--accent);
        }

        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.15);
        }

        .sidebar-menu a i {
            margin-right: 12px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .sidebar-divider {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.1);
            margin: 15px 0;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 15px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 10px;
            background-color: rgba(220, 38, 38, 0.9);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: rgba(220, 38, 38, 1);
        }

        .logout-btn i {
            margin-right: 8px;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 25px;
            transition: var(--transition);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray-200);
        }

        .greeting {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
        }

        .date-display {
            font-size: 14px;
            color: var(--gray-500);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
            color: white;
        }

        .stat-detail {
            flex: 1;
        }

        .stat-title {
            font-size: 14px;
            color: var(--gray-600);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 3px;
        }

        .stat-bg-info { background-color: rgba(2, 132, 199, 0.15); }
        .stat-bg-success { background-color: rgba(30, 124, 69, 0.15); }
        .stat-bg-warning { background-color: rgba(245, 158, 11, 0.15); }

        .stat-icon-info { background-color: var(--info); }
        .stat-icon-success { background-color: var(--success); }
        .stat-icon-warning { background-color: var(--warning); }

        /* Charts Section */
        .charts-section {
            margin-bottom: 30px;
        }

        .chart-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .chart-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .chart-card:hover {
            box-shadow: var(--shadow-md);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gray-200);
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
        }

        .chart-period {
            font-size: 13px;
            color: var(--gray-500);
            background-color: var(--gray-100);
            padding: 5px 10px;
            border-radius: 20px;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Job Details Table */
        .job-details {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gray-200);
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            background-color: var(--gray-100);
            color: var(--gray-700);
            font-weight: 600;
            font-size: 14px;
        }

        td {
            font-size: 14px;
            color: var(--gray-700);
        }

        tr:hover {
            background-color: var(--gray-100);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            color: white;
        }

        .pending { background-color: var(--pending); }
        .accepted { background-color: var(--info); }
        .completed { background-color: var(--completed); }
        .cancelled { background-color: var(--cancelled); }
        .processing { background-color: var(--warning); }

        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .summary-card {
            background-color: var(--gray-100);
            border-radius: var(--border-radius);
            padding: 15px;
        }

        .summary-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 10px;
        }

        .summary-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
        }

        /* Responsive */
        @media screen and (max-width: 1200px) {
            .chart-row {
                grid-template-columns: 1fr;
            }
        }

        @media screen and (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-brand, .sidebar-subtitle, .sidebar-menu a span {
                display: none;
            }
            
            .sidebar-menu a {
                justify-content: center;
                padding: 15px 0;
            }
            
            .sidebar-menu a i {
                margin: 0;
                font-size: 20px;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">Admin Panel</div>
            <div class="sidebar-subtitle">Management System</div>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="#" class="active"><i class="fas fa-chart-line"></i> <span>Dashboard</span></a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> <span>Manage Users</span></a></li>
            <li><a href="manage_jobs.php"><i class="fas fa-briefcase"></i> <span>Manage Jobs</span></a></li>
            <li><a href="feedback.php"><i class="fas fa-comment"></i> <span>View Feedback</span></a></li>
            
            <div class="sidebar-divider"></div>
            
            <li><a href="admin_worker_reports.php"><i class="fas fa-hard-hat"></i> <span>Worker Reports</span></a></li>
            <li><a href="admin_client_reports.php"><i class="fas fa-user-tie"></i> <span>Client Reports</span></a></li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-power-off"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1 class="greeting">Welcome, Admin!</h1>
                <p class="date-display"><?php echo date('l, F j, Y'); ?></p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-bg-info">
                <div class="stat-icon stat-icon-info">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-detail">
                    <div class="stat-title">Total Clients</div>
                    <div class="stat-value"><?php echo $total_clients; ?></div>
                </div>
            </div>
            
            <div class="stat-card stat-bg-success">
                <div class="stat-icon stat-icon-success">
                    <i class="fas fa-hard-hat"></i>
                </div>
                <div class="stat-detail">
                    <div class="stat-title">Total Workers</div>
                    <div class="stat-value"><?php echo $total_workers; ?></div>
                </div>
            </div>
            
            <div class="stat-card stat-bg-warning">
                <div class="stat-icon stat-icon-warning">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-detail">
                    <div class="stat-title">Total Users</div>
                    <div class="stat-value"><?php echo $total_users; ?></div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-row">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Workers Registration Trend</div>
                        <div class="chart-period">Monthly</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="workerChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Client Hiring Activity</div>
                        <div class="chart-period">Monthly</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="clientChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">Job Posts Analysis</div>
                    <div class="chart-period">Monthly</div>
                </div>
                <div class="chart-container">
                    <canvas id="jobPostChart"></canvas>
                </div>
                
                <div class="summary-grid">
                    <div class="summary-card">
                        <div class="summary-title">Posts This Month</div>
                        <div class="summary-value"><?php echo $current_month_total; ?></div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-title">Posts This Year</div>
                        <div class="summary-value"><?php echo $current_year_total; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Details Table -->
        <div class="job-details">
            <div class="table-header">
                <div class="table-title">Recent Job Activities</div>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Client</th>
                            <th>Worker</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($job_details_result->num_rows > 0): ?>
                            <?php while ($job = $job_details_result->fetch_assoc()): 
                                $status_class = strtolower($job['status']);
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                                    <td><?php echo htmlspecialchars($job['client_name'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($job['worker_name'] ?: 'Not Assigned'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($job['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No job details available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Worker Chart
        const workerCtx = document.getElementById('workerChart').getContext('2d');
        new Chart(workerCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($worker_labels); ?>,
                datasets: [{
                    label: 'Workers Registered',
                    data: <?php echo json_encode($worker_data); ?>,
                    backgroundColor: 'rgba(30, 124, 69, 0.2)',
                    borderColor: '#1e7c45',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#1e7c45',
                    pointRadius: 4,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Client Chart
        const clientCtx = document.getElementById('clientChart').getContext('2d');
        new Chart(clientCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($client_labels); ?>,
                datasets: [{
                    label: 'Clients Who Hired',
                    data: <?php echo json_encode($client_data); ?>,
                    backgroundColor: 'rgba(2, 132, 199, 0.2)',
                    borderColor: '#0284c7',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0284c7',
                    pointRadius: 4,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Job Posts Chart
        const jobPostCtx = document.getElementById('jobPostChart').getContext('2d');
        new Chart(jobPostCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($job_labels); ?>,
                datasets: [{
                    label: 'Total Job Posts',
                    data: <?php echo json_encode($job_posts); ?>,
                    backgroundColor: '#85334e',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
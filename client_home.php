<?php
session_start();
include('db.php'); // Include your database connection

// Check if the client is logged in
if (!isset($_SESSION['client_username'])) {
    header("Location: login_client.php");
    exit();
}

// Retrieve the client's username
$client_username = htmlspecialchars($_SESSION['client_username']);

// Fetch client ID
$sql = "SELECT id FROM clients WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $client_username);
$stmt->execute();
$client_result = $stmt->get_result();
$client = $client_result->fetch_assoc();
$client_id = $client['id'];

// Fetch hire schedules (now includes accepted job offers)
$schedule_sql = "
    SELECT h.id AS hire_id, w.full_name AS worker_name, w.skills, 
           h.schedule_date, h.schedule_time, h.status, 'hires' AS source
    FROM hires h
    JOIN workers w ON h.worker_id = w.id
    WHERE h.client_id = ?
    UNION
    SELECT jo.id AS hire_id, w.full_name AS worker_name, w.skills, 
           jo.schedule_date, jo.schedule_time, 'On Duty' AS status, 'job_offers' AS source
    FROM job_offers jo
    JOIN workers w ON jo.accepted_by = w.id
    WHERE jo.client_id = ? AND jo.status = 'Accepted'
";


$schedule_stmt = $conn->prepare($schedule_sql);
$schedule_stmt->bind_param("ii", $client_id, $client_id);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();

// Fetch notifications
$notif_sql = "SELECT message, created_at FROM notifications WHERE client_id = ? ORDER BY created_at DESC";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $client_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();

// Mark job as done
if (isset($_POST['done_working'])) {
    $hire_id = intval($_POST['hire_id']);
    $source = isset($_POST['source']) ? $_POST['source'] : '';  

    if (!empty($source) && in_array($source, ['hires', 'job_offers'])) {  
        $update_sql = "UPDATE $source SET status = 'Completed' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $hire_id);

        if ($update_stmt->execute()) {
            // Fetch worker's full name for feedback
            $worker_sql = "
                SELECT w.full_name 
                FROM $source s
                JOIN workers w ON 
                    (s.accepted_by = w.id) 
                WHERE s.id = ?";
                    
            $worker_stmt = $conn->prepare($worker_sql);
            $worker_stmt->bind_param("i", $hire_id);
            $worker_stmt->execute();
            $worker_result = $worker_stmt->get_result();
            $worker = $worker_result->fetch_assoc();
            $worker_name = urlencode($worker['full_name']); 

            echo "
            <script>
                if (confirm('Job marked as completed! Do you want to leave feedback for the worker?')) {
                    window.location.href = 'feedback_form.php?worker_name=$worker_name';
                } else {
                    window.location.href = 'client_home.php';
                }
            </script>";
        } else {
            echo "<script>alert('Failed to mark job as completed. Please try again.'); window.location='client_home.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid source. Unable to mark job as completed.'); window.location='client_home.php';</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-inner">
                <a href="client_home.php" class="brand">
                    
                    
                </a>
                <div class="user-nav">
                    <div class="welcome-message">Welcome, <?php echo $client_username; ?></div>
                    <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <nav>
        <div class="container">
            <div class="nav-links">
                <a href="client_home.php" class="active">Dashboard</a>
                <a href="view_services.php">View Services</a>
                <a href="client_profile.php">My Profile</a>
                <a href="edit_client_profile.php">Edit Profile</a>
                <a href="chat_worker.php">Messages</a>
            </div>
        </div>
    </nav>
    
    <main class="container">
        <div class="dashboard-grid">
            <div class="main-content">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Post a Job Offer</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="post_job.php">
                            <div class="form-group">
                                <label for="job_title">Job Title:</label>
                                <input type="text" name="job_title" id="job_title" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="job_description">Job Description:</label>
                                <textarea name="job_description" id="job_description" rows="3" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <label for="schedule_date">Date:</label>
                                        <input type="date" name="schedule_date" id="schedule_date" required>
                                    </div>
                                    <div>
                                        <label for="schedule_time">Time:</label>
                                        <input type="time" name="schedule_time" id="schedule_time" required>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn">Post Job</button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">My Scheduled Hires</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($schedule_result->num_rows > 0): ?>
                            <div style="overflow-x: auto;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Worker Name</th>
                                            <th>Skills</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($schedule = $schedule_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($schedule['worker_name']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['skills']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['schedule_date']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['schedule_time']); ?></td>
                                                <td class="<?php echo $schedule['status'] === 'Completed' ? 'status-completed' : 'status-pending'; ?>">
                                                    <?php echo htmlspecialchars($schedule['status']); ?>
                                                </td>
                                                <td>
                                                    <?php if ($schedule['status'] === 'On Duty'): ?>
                                                        <form method="POST">
                                                            <input type="hidden" name="hire_id" value="<?php echo $schedule['hire_id']; ?>">
                                                            <input type="hidden" name="source" value="<?php echo htmlspecialchars($schedule['source']); ?>"> 
                                                            <button type="submit" name="done_working" class="btn btn-sm btn-success">Complete</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No scheduled hires yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Post History</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        // Fetch job history data
                        $history_sql = "
                            SELECT jo.job_title, w.full_name AS worker_name, jo.schedule_date, jo.schedule_time, jo.status
                            FROM job_offers jo
                            LEFT JOIN workers w ON jo.accepted_by = w.id
                            WHERE jo.client_id = ?
                            ORDER BY jo.schedule_date DESC";
                            
                        $history_stmt = $conn->prepare($history_sql);
                        $history_stmt->bind_param("i", $client_id);
                        $history_stmt->execute();
                        $history_result = $history_stmt->get_result();
                        ?>
                        
                        <?php if ($history_result->num_rows > 0): ?>
                            <div style="overflow-x: auto;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Job Title</th>
                                            <th>Worker Name</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($history = $history_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($history['job_title']); ?></td>
                                                <td>
                                                    <?php 
                                                    echo $history['worker_name'] 
                                                        ? htmlspecialchars($history['worker_name']) 
                                                        : 'Not Accepted Yet'; 
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($history['schedule_date']); ?></td>
                                                <td><?php echo htmlspecialchars($history['schedule_time']); ?></td>
                                                <td class="<?php 
                                                    echo ($history['status'] === 'Completed') ? 'status-completed' : (
                                                        ($history['status'] === 'Cancelled') ? 'status-cancelled' : 'status-pending'
                                                    ); ?>">
                                                    <?php echo htmlspecialchars($history['status']); ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No job history available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="sidebar">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-links">
                            <a href="view_services.php" class="btn">Services</a>
                            <a href="client_profile.php" class="btn btn-outline">Profile</a>
                            <a href="edit_client_profile.php" class="btn btn-outline">Edit Profile</a>
                            <a href="chat_worker.php" class="btn btn-outline">Messages</a>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Notifications</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($notif_result->num_rows > 0): ?>
                            <div class="notification-list">
                                <?php while ($notif = $notif_result->fetch_assoc()): ?>
                                    <div class="notification-item">
                                        <div class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></div>
                                        <div class="notification-time"><?php echo htmlspecialchars($notif['created_at']); ?></div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No notifications yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> WorkerConnect. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

<?php
$conn->close();
?>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary: #0f172a;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #ea580c;
            --background: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-tertiary: #94a3b8;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-sm: 0.25rem;
            --radius: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: var(--text-primary);
            line-height: 1.5;
            background-color: var(--background);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        header {
            background-color: var(--surface);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--border);
        }
        
        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .brand-icon {
            background-color: var(--primary);
            color: white;
            width: 2rem;
            height: 2rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        
        .user-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .welcome-message {
            font-weight: 600;
            display: none;
        }
        
        @media (min-width: 768px) {
            .welcome-message {
                display: block;
            }
        }
        
        nav {
            background-color: var(--surface);
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius);
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        
        .nav-links a:hover {
            color: var(--primary);
            background-color: rgba(37, 99, 235, 0.05);
        }
        
        .nav-links a.active {
            color: var(--primary);
            background-color: rgba(37, 99, 235, 0.1);
        }
        
        /* Cards */
        .card {
            background-color: var(--surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background-color: rgba(248, 250, 252, 0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--secondary);
            margin: 0;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Grid layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 2fr 1fr;
            }
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        input[type="text"],
        input[type="date"],
        input[type="time"],
        textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background-color: var(--surface);
            color: var(--text-primary);
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.15s ease;
        }
        
        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 5rem;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            font-size: 0.9375rem;
            font-weight: 500;
            line-height: 1.5;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            user-select: none;
            border: 1px solid transparent;
            border-radius: var(--radius);
            background-color: var(--primary);
            color: #fff;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        
        .btn:hover {
            background-color: var(--primary-hover);
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .btn-success {
            background-color: var(--success);
        }
        
        .btn-success:hover {
            background-color: #15803d;
        }
        
        .btn-danger {
            background-color: var(--danger);
        }
        
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        
        .btn-outline {
            background-color: transparent;
            border-color: var(--border);
            color: var(--text-secondary);
        }
        
        .btn-outline:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--text-primary);
        }
        
        .btn-link {
            background: none;
            color: var(--primary);
            border: none;
            padding: 0;
            text-decoration: underline;
            cursor: pointer;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 0.5rem;
        }
        
        th, td {
            padding: 0.875rem 1rem;
            text-align: left;
        }
        
        th {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background-color: rgba(248, 250, 252, 0.8);
            border-bottom: 1px solid var(--border);
        }
        
        td {
            border-bottom: 1px solid var(--border);
            font-size: 0.9375rem;
        }
        
        tbody tr:last-child td {
            border-bottom: none;
        }
        
        tbody tr:hover {
            background-color: rgba(248, 250, 252, 0.8);
        }
        
        /* Status labels */
        .status-pending {
            color: var(--warning);
            font-weight: 600;
        }
        
        .status-completed {
            color: var(--success);
            font-weight: 600;
        }
        
        .status-cancelled {
            color: var(--danger);
            font-weight: 600;
        }
        
        /* Empty states */
        .empty-state {
            text-align: center;
            padding: 2rem 0;
            color: var(--text-tertiary);
        }
        
        /* Notification items */
        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            max-height: 320px;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 0.75rem;
            border-radius: var(--radius);
            background-color: rgba(248, 250, 252, 0.5);
            border: 1px solid var(--border);
        }
        
        .notification-message {
            margin-bottom: 0.25rem;
            font-size: 0.9375rem;
        }
        
        .notification-time {
            font-size: 0.75rem;
            color: var(--text-tertiary);
        }
        
        /* Utility classes */
        .mb-1 {
            margin-bottom: 0.25rem;
        }
        
        .mb-2 {
            margin-bottom: 0.5rem;
        }
        
        .mb-3 {
            margin-bottom: 0.75rem;
        }
        
        .mb-4 {
            margin-bottom: 1rem;
        }
        
        .mb-5 {
            margin-bottom: 1.5rem;
        }
        
        .mt-1 {
            margin-top: 0.25rem;
        }
        
        .mt-2 {
            margin-top: 0.5rem;
        }
        
        .mt-3 {
            margin-top: 0.75rem;
        }
        
        .mt-4 {
            margin-top: 1rem;
        }
        
        .mt-5 {
            margin-top: 1.5rem;
        }
        
        /* Quick links */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.75rem;
        }
        
        footer {
            text-align: center;
            padding: 2rem 0;
            color: var(--text-tertiary);
            font-size: 0.875rem;
            border-top: 1px solid var(--border);
            margin-top: 2rem;
        }

        /* Custom scrollbar */  
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(226, 232, 240, 0.5);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--text-tertiary);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-secondary);
        }
    </style>
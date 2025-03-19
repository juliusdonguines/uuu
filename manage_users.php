<?php
session_start();
include('db.php'); // Database connection

// Delete User
if (isset($_GET['delete'])) {
    $user_type = $_GET['type'];
    $user_id = intval($_GET['id']);

    $table = ($user_type === 'client') ? 'clients' : 'workers';
    $delete_sql = "DELETE FROM $table WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    echo "<script>alert('User deleted successfully.'); window.location='manage_users.php';</script>";
}

// Fetch clients
$client_sql = "SELECT * FROM clients";
$client_result = $conn->query($client_sql);

// Fetch workers
$worker_sql = "SELECT * FROM workers";
$worker_result = $conn->query($worker_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a4028;
            --primary-light: #8b5e3c;
            --primary-dark: #4d2e1d;
            --secondary: #3498db;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-600: #6c757d;
            --gray-900: #212529;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-900);
            line-height: 1.6;
        }
        
        .dashboard-header {
            background-color: white;
            padding: 20px 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-title {
            color: var(--primary);
            font-size: 24px;
            font-weight: 600;
        }
        
        .add-user-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s;
        }
        
        .add-user-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid var(--gray-300);
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 12px 24px;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            color: var(--gray-600);
            transition: all 0.2s;
        }
        
        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .card-header {
            background-color: white;
            padding: 16px 20px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-title i {
            color: var(--primary-light);
        }
        
        .card-body {
            padding: 0;
        }
        
        .search-bar {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .search-input {
            padding: 8px 16px 8px 36px;
            border: 1px solid var(--gray-300);
            border-radius: 5px;
            font-size: 14px;
            width: 250px;
        }
        
        .search-icon {
            position: absolute;
            left: 12px;
            color: var(--gray-600);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        th {
            text-align: left;
            padding: 12px 16px;
            background-color: var(--light);
            color: var(--gray-600);
            font-weight: 600;
            border-bottom: 1px solid var(--gray-300);
        }
        
        td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background-color: var(--gray-100);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .user-username {
            color: var(--gray-600);
            font-size: 12px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }
        
        .badge-primary {
            background-color: rgba(106, 64, 40, 0.1);
            color: var(--primary);
        }
        
        .action-cell {
            text-align: right;
        }
        
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
            border: none;
        }
        
        .btn-edit {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--secondary);
        }
        
        .btn-edit:hover {
            background-color: rgba(52, 152, 219, 0.2);
        }
        
        .btn-delete {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            margin-left: 5px;
        }
        
        .btn-delete:hover {
            background-color: rgba(220, 53, 69, 0.2);
        }
        
        .file-link {
            color: var(--secondary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .file-link:hover {
            text-decoration: underline;
        }
        
        .empty-state {
            padding: 40px;
            text-align: center;
            color: var(--gray-600);
        }
        
        .empty-icon {
            font-size: 48px;
            color: var(--gray-300);
            margin-bottom: 15px;
        }
        
        .fee {
            font-weight: 500;
            color: var(--primary);
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .search-input {
                width: 200px;
            }
        }
        
        @media (max-width: 992px) {
            .table-responsive {
                overflow-x: auto;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .tabs {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <header class="dashboard-header">
        <h1 class="dashboard-title">User Management Dashboard</h1>
        </button>
    </header>
    
    <div class="container">
        <div class="tabs">
            <div class="tab active" data-tab="clients">
                <i class="fas fa-user"></i> Clients
            </div>
            <div class="tab" data-tab="workers">
                <i class="fas fa-hard-hat"></i> Workers
            </div>
        </div>
        
        <!-- Clients Tab -->
        <div class="tab-content active" id="clients-tab">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-users"></i> Client Directory
                    </h2>
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search clients..." id="client-search">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Barangay</th>
                                    <th>Contact</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="client-table-body">
                                <?php if ($client_result->num_rows > 0): ?>
                                    <?php while ($client = $client_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $client['id']; ?></td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <img src="uploads/<?php echo htmlspecialchars($client['picture']); ?>" alt="<?php echo htmlspecialchars($client['full_name']); ?>" class="user-avatar">
                                                    <div>
                                                        <div class="user-name"><?php echo htmlspecialchars($client['full_name']); ?></div>
                                                        <div class="user-username">@<?php echo htmlspecialchars($client['username']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    <?php echo htmlspecialchars($client['begy']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($client['contact_num']); ?></td>
                                            <td class="action-cell">
                                                <a href="edit_user.php?type=client&id=<?php echo $client['id']; ?>" class="btn btn-edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="?delete=1&type=client&id=<?php echo $client['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this client?');">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <div class="empty-icon">
                                                    <i class="fas fa-users-slash"></i>
                                                </div>
                                                <p>No clients found in the system.</p>
                                                <p>Add a client to get started.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Workers Tab -->
        <div class="tab-content" id="workers-tab">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-hard-hat"></i> Worker Directory
                    </h2>
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search workers..." id="worker-search">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Worker</th>
                                    <th>Barangay</th>
                                    <th>Service Fee</th>
                                    <th>Contact</th>
                                    <th>Resume</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="worker-table-body">
                                <?php if ($worker_result->num_rows > 0): ?>
                                    <?php while ($worker = $worker_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $worker['id']; ?></td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <img src="uploads/<?php echo htmlspecialchars($worker['picture']); ?>" alt="<?php echo htmlspecialchars($worker['full_name']); ?>" class="user-avatar">
                                                    <div>
                                                        <div class="user-name"><?php echo htmlspecialchars($worker['full_name']); ?></div>
                                                        <div class="user-username">@<?php echo htmlspecialchars($worker['username']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    <?php echo htmlspecialchars($worker['begy']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fee">₱<?php echo number_format($worker['service_fee'], 2); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($worker['contact_num']); ?></td>
                                            <td>
                                                <?php if (!empty($worker['resume'])): ?>
                                                    <a href="uploads/<?php echo htmlspecialchars($worker['resume']); ?>" target="_blank" class="file-link">
                                                        <i class="fas fa-file-pdf"></i> View
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge badge-success">No Resume</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="action-cell">
                                                <a href="edit_user.php?type=worker&id=<?php echo $worker['id']; ?>" class="btn btn-edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="?delete=1&type=worker&id=<?php echo $worker['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this worker?');">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <div class="empty-icon">
                                                    <i class="fas fa-hard-hat"></i>
                                                </div>
                                                <p>No workers found in the system.</p>
                                                <p>Add a worker to get started.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show corresponding tab content
                const tabName = tab.getAttribute('data-tab');
                document.getElementById(`${tabName}-tab`).classList.add('active');
            });
        });
        
        // Client search functionality
        document.getElementById('client-search').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#client-table-body tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
        
        // Worker search functionality
        document.getElementById('worker-search').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#worker-table-body tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
<?php session_start(); 
include('db.php'); // Database connection 

// Fetch feedback with client and worker full names
$feedback_sql = "
    SELECT r.id, c.full_name AS client_name, w.full_name AS worker_name, 
            r.rating, r.comment, r.created_at
    FROM ratings r
    JOIN clients c ON r.client_id = c.id
    JOIN workers w ON r.worker_id = w.id
    ORDER BY r.created_at DESC
";
$feedback_result = $conn->query($feedback_sql);

// Star rating display logic
function displayStars($rating) {
    if (!is_numeric($rating)) return "☆☆☆☆☆";
    
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - ($fullStars + $halfStar);
    
    return str_repeat("★", $fullStars) . ($halfStar ? "★" : "") . str_repeat("☆", $emptyStars);
}

// Calculate average rating
function calculateAverageRating($result) {
    if ($result->num_rows === 0) return 0;
    
    $total = 0;
    $count = 0;
    
    // Reset the pointer
    $result->data_seek(0);
    
    while ($row = $result->fetch_assoc()) {
        $total += $row['rating'];
        $count++;
    }
    
    // Reset the pointer again for later use
    $result->data_seek(0);
    
    return $count > 0 ? round($total / $count, 1) : 0;
}

// Get rating distribution
function getRatingDistribution($result) {
    $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
    $total = 0;
    
    // Reset the pointer
    $result->data_seek(0);
    
    while ($row = $result->fetch_assoc()) {
        $rating = floor($row['rating']);
        if (isset($distribution[$rating])) {
            $distribution[$rating]++;
            $total++;
        }
    }
    
    // Calculate percentages
    $percentages = [];
    foreach ($distribution as $rating => $count) {
        $percentages[$rating] = $total > 0 ? round(($count / $total) * 100) : 0;
    }
    
    // Reset the pointer again for later use
    $result->data_seek(0);
    
    return [
        'counts' => $distribution,
        'percentages' => $percentages,
        'total' => $total
    ];
}

$averageRating = calculateAverageRating($feedback_result);
$ratingDistribution = getRatingDistribution($feedback_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #5a4534;
            --primary-light: #8b6e4f;
            --secondary: #1e88e5;
            --danger: #e53935;
            --success: #43a047;
            --warning: #ffa000;
            --star: #ffb400;
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
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .stat-icon {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }
        
        .rating-stars {
            color: var(--star);
            font-size: 24px;
            margin: 5px 0;
        }
        
        .rating-distribution {
            width: 100%;
            margin-top: 15px;
        }
        
        .rating-bar {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .rating-label {
            width: 30px;
            text-align: right;
            margin-right: 10px;
            font-weight: 500;
        }
        
        .bar-container {
            flex-grow: 1;
            background-color: var(--gray);
            height: 12px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .bar-fill {
            height: 100%;
            background-color: var(--star);
            border-radius: 10px;
        }
        
        .rating-percentage {
            width: 40px;
            text-align: right;
            margin-left: 10px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 20px;
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
        
        .rating-cell {
            color: var(--star);
            font-size: 18px;
        }
        
        .comment-cell {
            max-width: 300px;
        }
        
        .date-cell {
            white-space: nowrap;
            font-size: 13px;
            color: #6c757d;
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
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .data-table th, 
            .data-table td {
                padding: 8px 10px;
            }
            
            .comment-cell {
                max-width: 150px;
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
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: bold;
        }
        
        .client-label {
            font-size: 12px;
            background-color: var(--primary-light);
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 6px;
        }
        
        .feedback-date {
            font-size: 12px;
            color: #6c757d;
        }
        
        .feedback-rating {
            color: var(--star);
            font-size: 18px;
        }
        
        .feedback-comment {
            margin-top: 10px;
            line-height: 1.6;
        }
        
        .feedback-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
        }
        
        .feedback-action {
            background: none;
            border: none;
            color: var(--secondary);
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            margin-left: 15px;
        }
        
        .feedback-action i {
            margin-right: 5px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            margin: 20px 0;
        }
        
        .pagination li {
            margin: 0 5px;
        }
        
        .pagination a {
            display: block;
            padding: 5px 10px;
            background-color: white;
            border: 1px solid var(--gray);
            border-radius: 4px;
            color: var(--dark);
            text-decoration: none;
        }
        
        .pagination a.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination a:hover:not(.active) {
            background-color: var(--gray);
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
                    <a href="manage_jobs.php" class="nav-link">
                        <i class="fas fa-briefcase"></i> Job Offers
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_users.php" class="nav-link">
                        <i class="fas fa-user-tie"></i> Manage Users
                    </a>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="feedback.php" class="nav-link active">
                        <i class="fas fa-star"></i> Feedback
                    </a>
                </li>
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
                <h1 class="page-title">Feedback Management</h1>
                <div class="header-actions">
                    <input type="text" id="searchInput" placeholder="Search feedback..." 
                           style="padding: 6px 10px; border: 1px solid var(--gray); border-radius: 4px;">
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-container">
                <!-- Average Rating -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($averageRating, 1); ?></div>
                    <div class="rating-stars"><?php echo displayStars($averageRating); ?></div>
                    <div class="stat-label">Average Rating</div>
                </div>
                
                <!-- Total Reviews -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-value"><?php echo $ratingDistribution['total']; ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
                
                <!-- Rating Distribution -->
                <div class="stat-card" style="align-items: flex-start;">
                    <h3 style="margin-bottom: 10px; align-self: center;">Rating Distribution</h3>
                    <div class="rating-distribution">
                        <?php for($i = 5; $i >= 1; $i--): ?>
                            <div class="rating-bar">
                                <div class="rating-label"><?php echo $i; ?> ★</div>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: <?php echo $ratingDistribution['percentages'][$i]; ?>%;"></div>
                                </div>
                                <div class="rating-percentage"><?php echo $ratingDistribution['percentages'][$i]; ?>%</div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <!-- Feedback List with Cards -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">All Feedback</h2>
                    <div>
                        <select id="sortFilter" style="padding: 6px 10px; border: 1px solid var(--gray); border-radius: 4px;">
                            <option value="recent">Most Recent</option>
                            <option value="highest">Highest Rated</option>
                            <option value="lowest">Lowest Rated</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($feedback_result->num_rows > 0): ?>
                        <div class="feedback-list">
                            <?php while ($feedback = $feedback_result->fetch_assoc()): ?>
                                <div class="feedback-card">
                                    <div class="feedback-header">
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo substr($feedback['client_name'], 0, 1); ?>
                                            </div>
                                            <div class="user-details">
                                                <div>
                                                    <span class="user-name"><?php echo htmlspecialchars($feedback['client_name']); ?></span>
                                                    <span class="client-label">Client</span>
                                                </div>
                                                <div>
                                                    <small>For: <strong><?php echo htmlspecialchars($feedback['worker_name']); ?></strong></small>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="feedback-rating"><?php echo displayStars($feedback['rating']); ?></div>
                                            <div class="feedback-date"><?php echo date("F d, Y", strtotime($feedback['created_at'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="feedback-comment">
                                        <?php echo htmlspecialchars($feedback['comment']); ?>
                                    </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <!-- Pagination -->
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-star"></i>
                            <h3>No Feedback Yet</h3>
                            <p>There are currently no ratings or reviews in the system.</p>
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
            let feedbackCards = document.querySelectorAll('.feedback-card');
            
            feedbackCards.forEach(card => {
                let text = card.textContent.toLowerCase();
                card.style.display = text.includes(input) ? '' : 'none';
            });
        });
        
        // Simple sort functionality (would need proper implementation)
        document.getElementById('sortFilter').addEventListener('change', function() {
            let value = this.value;
            alert('Sorting by ' + value + ' (This would be implemented with actual sorting in a complete system)');
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
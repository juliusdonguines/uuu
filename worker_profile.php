<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['worker_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_worker.php';</script>";
    exit();
}

$worker_username = $_SESSION['worker_username'];

// Fetch worker details
$sql = "SELECT * FROM workers WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $worker_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $worker = $result->fetch_assoc();
} else {
    echo "<script>alert('Worker profile not found.'); window.location='login_worker.php';</script>";
    exit();
}

// Calculate average rating
$rating_sql = "SELECT AVG(rating) AS avg_rating FROM ratings WHERE worker_id = ?";
$rating_stmt = $conn->prepare($rating_sql);
$rating_stmt->bind_param("i", $worker['id']);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result();
$rating_data = $rating_result->fetch_assoc();

$average_rating = is_numeric($rating_data['avg_rating']) ? round($rating_data['avg_rating'], 1) : 0;

// Fetch comments with client name
$comments_sql = "
    SELECT r.rating, r.comment, r.feedback, r.created_at, c.full_name 
    FROM ratings r 
    JOIN clients c ON r.client_id = c.id 
    WHERE r.worker_id = ?
    ORDER BY r.created_at DESC
";
$comments_stmt = $conn->prepare($comments_sql);
$comments_stmt->bind_param("i", $worker['id']);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

// Star rating logic
function displayStars($rating) {
    if (!is_numeric($rating)) return "☆☆☆☆☆";

    $fullStars = floor($rating); 
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - ($fullStars + $halfStar);

    return str_repeat("★", $fullStars) . ($halfStar ? "☆" : "") . str_repeat("☆", $emptyStars);
}

// Count total reviews
$total_reviews = $comments_result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($worker['full_name']); ?> - Worker Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Base Styles */
        :root {
            --primary: #2e4057;
            --secondary: #048ba8;
            --accent: #16db93;
            --light: #f6f8fa;
            --dark: #2d3748;
            --gray: #718096;
            --light-gray: #e2e8f0;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            color: #333;
            line-height: 1.6;
        }
        
        /* Layout */
        .page-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .profile-header {
            background-color: var(--primary);
            color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-title h1 {
            font-size: 24px;
            font-weight: 600;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
        }
        
        /* Profile Card */
        .profile-card {
            background-color: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .profile-image-container {
            height: 200px;
            overflow: hidden;
            position: relative;
            background-color: var(--light-gray);
        }
        
        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: var(--secondary);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .profile-info {
            padding: 20px;
        }
        
        .profile-name {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        .profile-username {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .profile-rating {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .stars {
            color: #f7b731;
            letter-spacing: 2px;
            margin-right: 10px;
        }
        
        .review-count {
            color: var(--gray);
            font-size: 14px;
        }
        
        .profile-detail {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        
        .profile-detail i {
            color: var(--secondary);
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .profile-label {
            font-weight: 500;
            margin-right: 8px;
            color: var(--dark);
        }
        
        .profile-value {
            color: var(--gray);
            word-break: break-word;
        }
        
        .profile-divider {
            height: 1px;
            background-color: var(--light-gray);
            margin: 20px 0;
        }
        
        .service-fee {
            background-color: var(--light);
            padding: 15px;
            border-radius: var(--border-radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        
        .fee-label {
            font-weight: 600;
            color: var(--dark);
        }
        
        .fee-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--secondary);
        }
        
        /* Reviews Section */
        .reviews-section {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: var(--secondary);
        }
        
        .review-card {
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .reviewer-name {
            font-weight: 600;
            color: var(--dark);
        }
        
        .review-date {
            color: var(--gray);
            font-size: 12px;
        }
        
        .review-stars {
            color: #f7b731;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        
        .review-comment {
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .review-feedback {
            background-color: var(--light);
            padding: 10px;
            border-radius: var(--border-radius);
            font-style: italic;
        }
        
        .no-reviews {
            text-align: center;
            color: var(--gray);
            padding: 30px;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #223243;
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }
        
        /* Resume Link */
        .resume-link {
            display: inline-flex;
            align-items: center;
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            margin-top: 10px;
        }
        
        .resume-link i {
            margin-right: 5px;
        }
        
        .resume-link:hover {
            text-decoration: underline;
        }
        
        /* Skills Tags */
        .skills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 5px;
        }
        
        .skill-tag {
            background-color: var(--light);
            color: var(--secondary);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        /* Empty State */
        .empty-state {
            color: #777;
            font-style: italic;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="profile-header">
            <div class="header-title">
                <h1>Your Profile</h1>
            </div>
            <div class="header-actions">
                <a href="worker_home.php" class="btn btn-outline">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="logout.php" class="btn btn-primary">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="main-content">
            <!-- Profile Information -->
            <div class="profile-card">
                <div class="profile-image-container">
                    <img src="uploads/<?php echo htmlspecialchars($worker['picture']); ?>" alt="<?php echo htmlspecialchars($worker['full_name']); ?>" class="profile-image">
                    <?php if($average_rating >= 4.5): ?>
                        <div class="profile-badge">Top Rated</div>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h2 class="profile-name"><?php echo htmlspecialchars($worker['full_name']); ?></h2>
                    <div class="profile-username">@<?php echo htmlspecialchars($worker['username']); ?></div>
                    
                    <div class="profile-rating">
                        <div class="stars"><?php echo displayStars($average_rating); ?></div>
                        <div class="review-count"><?php echo $average_rating; ?> (<?php echo $total_reviews; ?> reviews)</div>
                    </div>
                    
                    <div class="profile-divider"></div>
                    
                    <div class="profile-detail">
                        <i class="fas fa-tools"></i>
                        <span class="profile-label">Primary Skills:</span>
                        <span class="profile-value"><?php echo htmlspecialchars($worker['skills'] ?? 'Not specified'); ?></span>
                    </div>
                    
                    <div class="profile-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <span class="profile-label">Barangay:</span>
                        <span class="profile-value"><?php echo htmlspecialchars($worker['begy'] ?? 'Not specified'); ?></span>
                    </div>
                    
                    <div class="profile-detail">
                        <i class="fas fa-home"></i>
                        <span class="profile-label">Address:</span>
                        <span class="profile-value"><?php echo htmlspecialchars($worker['residential_address'] ?? 'Not specified'); ?></span>
                    </div>
                    
                    <div class="profile-detail">
                        <i class="fas fa-phone"></i>
                        <span class="profile-label">Contact:</span>
                        <span class="profile-value"><?php echo htmlspecialchars($worker['contact_num']); ?></span>
                    </div>
                    
                    <?php if (!empty($worker['other_skills'])): ?>
                        <div class="profile-detail">
                            <i class="fas fa-cog"></i>
                            <span class="profile-label">Other Skills:</span>
                            <div>
                                <div class="skills-container">
                                    <?php 
                                        $skills = explode(',', $worker['other_skills']);
                                        foreach ($skills as $skill) {
                                            echo '<span class="skill-tag">' . htmlspecialchars(trim($skill)) . '</span>';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="profile-detail">
                            <i class="fas fa-cog"></i>
                            <span class="profile-label">Other Skills:</span>
                            <span class="empty-state">Go to edit profile to add your other skills</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($worker['resume'])): ?>
                        <a href="uploads/<?php echo htmlspecialchars($worker['resume']); ?>" target="_blank" class="resume-link">
                            <i class="fas fa-file-alt"></i> View Resume
                        </a>
                    <?php endif; ?>
                    
                    <div class="service-fee">
                        <span class="fee-label">Service Fee</span>
                        <span class="fee-value">₱<?php echo number_format($worker['service_fee'], 2); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <div class="reviews-section">
                <h3 class="section-title">
                    <i class="fas fa-comment-alt"></i>
                    Client Reviews (<?php echo $total_reviews; ?>)
                </h3>
                
                <?php if ($comments_result->num_rows > 0): ?>
                    <?php while ($comment = $comments_result->fetch_assoc()): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-name"><?php echo htmlspecialchars($comment['full_name']); ?></div>
                                <div class="review-date"><?php echo date("F d, Y", strtotime($comment['created_at'])); ?></div>
                            </div>
                            <div class="review-stars"><?php echo displayStars($comment['rating']); ?></div>
                            <div class="review-comment"><?php echo htmlspecialchars($comment['comment']); ?></div>
                            <?php if (!empty($comment['feedback'])): ?>
                                <div class="review-feedback">
                                    <strong>Your Response:</strong> <?php echo htmlspecialchars($comment['feedback']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-reviews">
                        <i class="far fa-comment-alt" style="font-size: 48px; color: #e2e8f0; margin-bottom: 15px;"></i>
                        <p>No reviews available yet.</p>
                        <p style="font-size: 14px; margin-top: 10px;">Complete jobs to receive client reviews.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
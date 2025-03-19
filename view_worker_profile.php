<?php
session_start();
include('db.php');

if (!isset($_SESSION['client_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_client.php';</script>";
    exit();
}

if (!isset($_GET['worker_id']) || empty($_GET['worker_id'])) {
    echo "<script>alert('Invalid worker profile.'); window.location='available_services.php';</script>";
    exit();
}

$worker_id = intval($_GET['worker_id']);

// Fetch worker's data
$sql = "SELECT * FROM workers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $worker = $result->fetch_assoc();
} else {
    echo "<script>alert('Worker not found.'); window.location='available_services.php';</script>";
    exit();
}

// Fetch average rating and total reviews
$rating_sql = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM ratings WHERE worker_id = ?";
$rating_stmt = $conn->prepare($rating_sql);
$rating_stmt->bind_param("i", $worker_id);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result();
$rating_data = $rating_result->fetch_assoc();

$average_rating = $rating_data['avg_rating'] ? number_format($rating_data['avg_rating'], 1) : 'No ratings yet';
$total_reviews = $rating_data['total_reviews'];

// Fetch comments with client name
$comments_sql = "SELECT r.rating, r.comment, r.feedback, r.created_at, c.full_name 
                  FROM ratings r 
                  JOIN clients c ON r.client_id = c.id 
                  WHERE r.worker_id = ?";
$comments_stmt = $conn->prepare($comments_sql);
$comments_stmt->bind_param("i", $worker_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

// Handling the hire form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hire_worker'])) {
    if (!isset($_SESSION['client_id'])) {
        echo "<script>alert('Unauthorized access. Please log in as a client.'); window.location='login_client.php';</script>";
        exit();
    }

    $client_id = $_SESSION['client_id'];
    $worker_id = intval($_GET['worker_id']); // Correctly retrieve worker_id from URL
    $worker_full_name = $_POST['worker_full_name'];
    $schedule_date = $_POST['schedule_date'];
    $schedule_time = $_POST['schedule_time'];
    $job_id = intval($_POST['job_id']);

    // Fetch the client's username
    $client_sql = "SELECT username FROM clients WHERE id = ?";
    $client_stmt = $conn->prepare($client_sql);
    $client_stmt->bind_param("i", $client_id);
    $client_stmt->execute();
    $client_result = $client_stmt->get_result();
    $client = $client_result->fetch_assoc();
    $client_username = $client['username'];

    // Insert into `hires` table with `worker_id`
    $insert_sql = "INSERT INTO hires (client_id, client_username, worker_id, full_name, job_id, schedule_date, schedule_time, status, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("isissss", $client_id, $client_username, $worker_id, $worker_full_name, $job_id, $schedule_date, $schedule_time);

    if ($insert_stmt->execute()) {
        echo "<script>alert('Hire request sent successfully!'); window.location='client_home.php';</script>";
    } else {
        echo "<script>alert('Failed to send hire request. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Profile - <?php echo htmlspecialchars($worker['full_name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Worker Profile</h1>
            <a href="view_services.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Services
            </a>
        </div>

        <div class="profile-grid">
            <!-- Left Sidebar -->
            <div class="profile-sidebar">
                <img src="uploads/<?php echo htmlspecialchars($worker['picture']); ?>" alt="<?php echo htmlspecialchars($worker['full_name']); ?>" class="profile-image">
                
                <div class="profile-info">
                    <h2 class="profile-name"><?php echo htmlspecialchars($worker['full_name']); ?></h2>
                    
                    <div class="profile-rating">
                        <div class="stars">
                            <?php
                                if ($average_rating !== 'No ratings yet') {
                                    $full_stars = floor($average_rating);
                                    $half_star = $average_rating - $full_stars > 0.3;
                                    
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $full_stars) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($half_star && $i == $full_stars + 1) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                            $half_star = false;
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    echo ' <span>' . $average_rating . '</span>';
                                } else {
                                    echo '<i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
                                    echo ' <span>No ratings</span>';
                                }
                            ?>
                        </div>
                        <span class="review-count">(<?php echo $total_reviews; ?> reviews)</span>
                    </div>
                    
                    <div class="profile-detail">
                        <span class="detail-label">Service Fee:</span>
                        <span class="detail-value">₱<?php echo number_format($worker['service_fee'], 2); ?></span>
                    </div>
                    
                    <div class="profile-detail">
                        <span class="detail-label">Contact:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($worker['contact_num']); ?></span>
                    </div>
                    
                    <div class="profile-detail">
                        <span class="detail-label">Barangay:</span>
                        <span class="detail-value">
                            <?php echo !empty($worker['begy']) 
                                ? htmlspecialchars($worker['begy']) 
                                : "<span style='color: var(--gray-400);'>Not specified</span>"; ?>
                        </span>
                    </div>
                    
                    <div class="profile-detail">
                        <span class="detail-label">Skills:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($worker['skills']); ?></span>
                    </div>
                    
                    <?php if (!empty($worker['other_skills'])): ?>
                    <div class="profile-detail">
                        <span class="detail-label">Other Skills:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($worker['other_skills']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="profile-actions">
                        <?php if (!empty($worker['resume'])): ?>
                            <a href="uploads/<?php echo htmlspecialchars($worker['resume']); ?>" target="_blank" class="btn btn-outline">
                                <i class="fas fa-file-alt"></i> View Resume
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Main Content -->
            <div class="profile-main">
                <!-- Tabs Container -->
                <div class="section">
                    <div class="tab-container">
                        <div class="tabs">
                            <div class="tab active" data-tab="hire">Hire Worker</div>
                            <div class="tab" data-tab="rate">Rate Worker</div>
                            <div class="tab" data-tab="reviews">Reviews (<?php echo $total_reviews; ?>)</div>
                        </div>
                        
                        <!-- Hire Form Tab -->
                        <div class="tab-content active" id="hire-form" data-tab="hire">
                            <h3 class="section-title">
                                <i class="fas fa-calendar-plus"></i> Schedule a Service
                            </h3>
                            
                            <form action="" method="POST">
                                <input type="hidden" name="worker_id" value="<?php echo $worker['id']; ?>">
                                <input type="hidden" name="worker_full_name" value="<?php echo htmlspecialchars($worker['full_name']); ?>">
                                <input type="hidden" name="job_id" value="1"> <!-- You might want to change this based on your job offerings -->
                                
                                <div class="form-group">
                                    <label for="schedule_date" class="form-label">When do you need this service?</label>
                                    <input type="date" id="schedule_date" name="schedule_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="schedule_time" class="form-label">What time?</label>
                                    <input type="time" id="schedule_time" name="schedule_time" class="form-control" required>
                                </div>
                                
                                <button type="submit" name="hire_worker" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Hire Request
                                </button>
                            </form>
                        </div>
                        
                        <!-- Rate Form Tab -->
                        <div class="tab-content" data-tab="rate">
                            <h3 class="section-title">
                                <i class="fas fa-star"></i> Rate Your Experience
                            </h3>
                            
                            <form action="rate_worker.php" method="POST">
                                <input type="hidden" name="worker_id" value="<?php echo $worker['id']; ?>">
                                
                                <div class="form-group">
                                    <label class="form-label">How would you rate this worker?</label>
                                    <div class="rating-stars" id="rating-stars">
                                        <i class="star far fa-star" data-rating="1"></i>
                                        <i class="star far fa-star" data-rating="2"></i>
                                        <i class="star far fa-star" data-rating="3"></i>
                                        <i class="star far fa-star" data-rating="4"></i>
                                        <i class="star far fa-star" data-rating="5"></i>
                                    </div>
                                    <input type="hidden" name="rating" id="selected-rating" value="0" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="comment" class="form-label">Your review</label>
                                    <textarea id="comment" name="comment" rows="4" class="form-control" placeholder="Share details of your experience with this worker..." required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Submit Rating
                                </button>
                            </form>
                        </div>
                        
                        <!-- Reviews Tab -->
                        <div class="tab-content" data-tab="reviews">
                            <h3 class="section-title">
                                <i class="fas fa-comments"></i> Client Reviews
                            </h3>
                            
                            <?php if ($comments_result->num_rows > 0): ?>
                                <div class="comments-list">
                                    <?php while ($comment = $comments_result->fetch_assoc()): ?>
                                        <div class="comment-card">
                                            <div class="comment-header">
                                                <span class="comment-author"><?php echo htmlspecialchars($comment['full_name']); ?></span>
                                                <div class="comment-rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= $comment['rating']): ?>
                                                            <i class="fas fa-star" style="color: var(--warning);"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star" style="color: var(--gray-300);"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="comment-body">
                                                <?php echo htmlspecialchars($comment['comment']); ?>
                                            </div>
                                            
                                            <?php if (!empty($comment['feedback'])): ?>
                                                <div class="comment-feedback">
                                                    <strong>Worker's Response:</strong> <?php echo htmlspecialchars($comment['feedback']); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="comment-date">
                                                Posted on <?php echo date("F d, Y", strtotime($comment['created_at'])); ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="far fa-comment-dots"></i>
                                    <h3>No Reviews Yet</h3>
                                    <p>Be the first to review this worker!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabId = tab.getAttribute('data-tab');
                    
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to current tab
                    tab.classList.add('active');
                    
                    // Show the corresponding tab content
                    const activeContent = document.querySelector(`.tab-content[data-tab="${tabId}"]`);
                    if (activeContent) {
                        activeContent.classList.add('active');
                    }
                });
            });
            
            // Star rating functionality
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('selected-rating');
            
            stars.forEach(star => {
                star.addEventListener('mouseover', () => {
                    const rating = parseInt(star.getAttribute('data-rating'));
                    highlightStars(rating);
                });
                
                star.addEventListener('click', () => {
                    const rating = parseInt(star.getAttribute('data-rating'));
                    ratingInput.value = rating;
                    
                    // Update star icons permanently
                    stars.forEach(s => {
                        const starRating = parseInt(s.getAttribute('data-rating'));
                        if (starRating <= rating) {
                            s.classList.remove('far');
                            s.classList.add('fas');
                        } else {
                            s.classList.remove('fas');
                            s.classList.add('far');
                        }
                    });
                });
            });
            
            const ratingContainer = document.getElementById('rating-stars');
            if (ratingContainer) {
                ratingContainer.addEventListener('mouseout', () => {
                    const currentRating = parseInt(ratingInput.value);
                    highlightStars(currentRating);
                });
            }
            
            function highlightStars(rating) {
                stars.forEach(s => {
                    const starRating = parseInt(s.getAttribute('data-rating'));
                    if (starRating <= rating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });
            }
            
            // Check if there's a hash in the URL to auto-select a tab
            const hash = window.location.hash.substring(1);
            if (hash) {
                const tabToActivate = document.querySelector(`.tab[data-tab="${hash}"]`);
                if (tabToActivate) {
                    tabToActivate.click();
                }
            }
        });
    </script>
</body>
</html>
<style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #64748b;
            --light: #f8fafc;
            --dark: #0f172a;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --border-radius: 0.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.75rem;
            color: var(--dark);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary-dark);
        }

        .back-link i {
            margin-right: 0.5rem;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }

        .profile-sidebar {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .profile-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-bottom: 1px solid var(--gray-200);
        }

        .profile-info {
            padding: 1.5rem;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .profile-rating {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }

        .stars {
            color: var(--warning);
            margin-right: 0.5rem;
        }

        .review-count {
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        .profile-detail {
            display: flex;
            margin-bottom: 1rem;
        }

        .detail-label {
            width: 120px;
            font-weight: 500;
            color: var(--gray-500);
        }

        .detail-value {
            flex: 1;
            color: var(--dark);
        }

        .profile-actions {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #475569;
        }

        .profile-main {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .section {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--dark);
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 0.75rem;
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.375rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.375rem;
            font-size: 1rem;
            transition: border-color 0.2s;
            background-color: white;
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%2364748b" class="bi bi-chevron-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .comment-card {
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius);
            padding: 1.25rem;
            background-color: var(--gray-100);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .comment-author {
            font-weight: 600;
            color: var(--dark);
        }

        .comment-rating {
            display: flex;
            align-items: center;
        }

        .comment-body {
            margin-bottom: 0.75rem;
            color: var(--dark);
        }

        .comment-feedback {
            padding: 0.75rem;
            background-color: white;
            border-radius: 0.375rem;
            margin-bottom: 0.75rem;
            border-left: 3px solid var(--primary);
        }

        .comment-date {
            color: var(--gray-500);
            font-size: 0.875rem;
            text-align: right;
        }

        .rating-stars {
            display: flex;
            gap: 0.25rem;
        }

        .star {
            color: var(--gray-300);
            cursor: pointer;
            font-size: 1.5rem;
            transition: color 0.2s;
        }

        .star.active {
            color: var(--warning);
        }

        .empty-state {
            text-align: center;
            padding: 2rem 0;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            margin-left: 0.5rem;
        }

        .badge-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .tab-container {
            margin-bottom: 1.5rem;
        }

        .tabs {
            display: flex;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 1.5rem;
        }

        .tab {
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            color: var(--gray-500);
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 2px solid transparent;
        }

        .tab.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }

            .profile-image {
                height: 200px;
            }
        }
    </style>

<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['client_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_client.php';</script>";
    exit();
}

// Search functionality
$search = "";
if (isset($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
    $sql = "SELECT * FROM workers WHERE full_name LIKE ? OR skills LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_param = "%$search%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM workers";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommended Services</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Recommended Services</h1>
        </div>

        <div class="search-container">
            <div class="search-bar">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Search by worker name or skills..." value="<?php echo $search; ?>" class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                </form>
            </div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <h2 class="section-title">
                <?php echo !empty($search) ? "Search Results for \"<span class=\"keyword\">{$search}</span>\"" : "Top Recommended Service Providers"; ?>
            </h2>
            
            <div class="services-grid">
                <?php while ($worker = $result->fetch_assoc()): ?>
                    <div class="service-card">
                        <div class="service-header">
                            <img src="uploads/<?php echo htmlspecialchars($worker['picture']); ?>" alt="<?php echo htmlspecialchars($worker['full_name']); ?>" class="service-avatar">
                            <div class="service-title">
                                <h3 class="service-name"><?php echo htmlspecialchars($worker['full_name']); ?></h3>
                                <span class="service-badge"><?php echo htmlspecialchars($worker['skills']); ?></span>
                            </div>
                        </div>
                        
                        <div class="service-body">
                            <div class="service-detail">
                                <span class="detail-icon"><i class="fas fa-briefcase"></i></span>
                                <div class="detail-content">
                                    <div class="detail-label">Primary Skills</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($worker['skills']); ?></div>
                                </div>
                            </div>
                            
                            <div class="service-detail">
                                <span class="detail-icon"><i class="fas fa-tools"></i></span>
                                <div class="detail-content">
                                    <div class="detail-label">Other Skills</div>
                                    <div class="detail-value">
                                        <?php echo !empty($worker['other_skills']) 
                                            ? htmlspecialchars($worker['other_skills']) 
                                            : "<span class='text-muted'>No other skills listed</span>"; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-detail">
                                <span class="detail-icon"><i class="fas fa-map-marker-alt"></i></span>
                                <div class="detail-content">
                                    <div class="detail-label">Location</div>
                                    <div class="detail-value">
                                        <?php echo !empty($worker['begy']) 
                                            ? htmlspecialchars($worker['begy']) 
                                            : "<span class='text-muted'>Location not specified</span>"; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-detail">
                                <span class="detail-icon"><i class="fas fa-phone"></i></span>
                                <div class="detail-content">
                                    <div class="detail-label">Contact Number</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($worker['contact_num']); ?></div>
                                </div>
                            </div>
                            
                            <div class="service-detail">
                                <span class="detail-icon"><i class="fas fa-tag"></i></span>
                                <div class="detail-content">
                                    <div class="detail-label">Service Fee</div>
                                    <div class="detail-value price-highlight">₱<?php echo number_format($worker['service_fee'], 2); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="service-footer">
                            <a href="view_resume.php?id=<?php echo $worker['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-file-alt btn-icon"></i> View Resume
                            </a>
                            <a href="view_worker_profile.php?worker_id=<?php echo $worker['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-user btn-icon"></i> View Profile
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="no-results-title">No results found</h3>
                <p class="no-results-text">
                    We couldn't find any service providers matching 
                    "<strong><?php echo $search; ?></strong>".
                    Try different keywords or browse all available services.
                </p>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-primary">
                    <i class="fas fa-redo btn-icon"></i> Show All Services
                </a>
            </div>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="client_home.php" class="back-button">
                <i class="fas fa-arrow-left back-button-icon"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
<style>
        :root {
            --primary: #3563E9;
            --primary-light: #EEF2FF;
            --primary-dark: #2549BE;
            --secondary: #FF6B35;
            --dark: #1E293B;
            --light: #F8FAFC;
            --gray-100: #F1F5F9;
            --gray-200: #E2E8F0;
            --gray-300: #CBD5E1;
            --gray-400: #94A3B8;
            --gray-500: #64748B;
            --gray-600: #475569;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--gray-100);
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        .page-title:after {
            content: '';
            display: block;
            width: 60%;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
            margin: 8px auto 0;
        }

        .search-container {
            background: var(--light);
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .search-bar {
            width: 100%;
        }

        .search-bar form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: white;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(53, 99, 233, 0.2);
        }

        .search-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0 25px;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .service-card {
            background-color: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--gray-200);
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .service-header {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid var(--gray-200);
            background-color: var(--primary-light);
        }

        .service-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: var(--shadow-sm);
            margin-right: 15px;
        }

        .service-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .service-badge {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .service-body {
            padding: 20px;
            flex: 1;
        }

        .service-detail {
            display: flex;
            margin-bottom: 12px;
            align-items: flex-start;
        }

        .detail-icon {
            color: var(--primary);
            margin-right: 12px;
            font-size: 16px;
            padding-top: 2px;
            width: 20px;
            text-align: center;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-weight: 600;
            color: var(--gray-600);
            margin-bottom: 2px;
            font-size: 14px;
        }

        .detail-value {
            color: var(--dark);
        }

        .price-highlight {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 18px;
        }

        .text-muted {
            color: var(--gray-500);
            font-style: italic;
            font-size: 14px;
        }

        .service-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: 10px;
            background-color: var(--gray-100);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: var(--radius);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
            flex: 1;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: white;
            color: var(--primary-dark);
            border: 1px solid var(--primary);
            flex: 1;
        }

        .btn-secondary:hover {
            background-color: var(--primary-light);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--gray-600);
            border: 1px solid var(--gray-300);
        }

        .btn-outline:hover {
            background-color: var(--gray-200);
        }

        .btn-icon {
            margin-right: 8px;
        }

        .no-results {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .no-results-icon {
            font-size: 60px;
            color: var(--gray-300);
            margin-bottom: 20px;
        }

        .no-results-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .no-results-text {
            color: var(--gray-500);
            font-size: 16px;
            max-width: 400px;
            margin: 0 auto 20px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            background-color: var(--primary);
            color: white;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 20px auto;
            box-shadow: var(--shadow);
        }

        .back-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .back-button-icon {
            margin-right: 8px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--gray-600);
            display: flex;
            align-items: center;
        }

        .section-title:after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gray-300);
            margin-left: 10px;
        }

        .keyword {
            font-weight: 700;
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .service-header {
                flex-direction: column;
                text-align: center;
            }
            
            .service-avatar {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .search-bar form {
                flex-direction: column;
            }
            
            .search-btn {
                padding: 12px;
            }
        }
    </style>
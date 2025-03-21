<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['client_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_client.php';</script>";
    exit();
}

// Fetch distinct skills for dropdown
$skills_sql = "SELECT DISTINCT skills FROM workers";
$skills_result = $conn->query($skills_sql);

// Filter workers by selected skill (if set)
$selected_skill = isset($_GET['skill']) ? mysqli_real_escape_string($conn, $_GET['skill']) : '';

if (!empty($selected_skill)) {
    $sql = "SELECT * FROM workers WHERE skills LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchSkill = "%$selected_skill%";
    $stmt->bind_param("s", $searchSkill);
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
    <title>Available Services | Find Your Service Provider</title>
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4a6de5;
            --primary-light: #eef0ff;
            --secondary-color: #2c3e50;
            --accent-color: #3ECF8E;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --grey-light: #f8f9fa;
            --grey-dark: #6c757d;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            --radius: 8px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
            padding-bottom: 40px;
        }
        
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 30px 0;
            background: linear-gradient(135deg, var(--primary-color), #7a8dff);
            color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .filter-container {
            background-color: white;
            padding: 20px;
            border-radius: var(--radius);
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }
        
        .filter-form {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-grow: 1;
        }
        
        .filter-label {
            font-weight: 600;
            color: var(--secondary-color);
            min-width: 100px;
        }
        
        .filter-select {
            padding: 12px 15px;
            border: 1px solid #e1e5e9;
            border-radius: var(--radius);
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: var(--secondary-color);
            background-color: white;
            width: 250px;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .filter-select:focus, .filter-select:hover {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 109, 229, 0.1);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #3857c2;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--grey-dark);
            border: 1px solid #e1e5e9;
        }
        
        .btn-outline:hover {
            border-color: var(--grey-dark);
            color: var(--secondary-color);
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .service-card {
            background-color: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #eaeaea;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.08);
        }
        
        .service-header {
            position: relative;
            display: flex;
            align-items: center;
            padding: 20px;
            background-color: var(--primary-light);
            border-bottom: 1px solid #eaeaea;
        }
        
        .service-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .service-title {
            margin-left: 15px;
        }
        
        .service-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .service-rating {
            display: flex;
            align-items: center;
            gap: 3px;
            color: #f1c40f;
            margin-bottom: 5px;
        }
        
        .service-body {
            padding: 20px;
        }
        
        .service-detail {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .detail-icon {
            color: var(--primary-color);
            min-width: 25px;
            font-size: 0.9rem;
            padding-top: 2px;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--secondary-color);
            min-width: 100px;
        }
        
        .detail-value {
            color: #555;
            flex-grow: 1;
        }
        
        .price-tag {
            font-weight: 600;
            color: var(--accent-color);
        }
        
        .text-muted {
            color: var(--grey-dark);
            font-style: italic;
            font-size: 0.9rem;
        }
        
        .service-footer {
            display: flex;
            justify-content: space-between;
            padding: 15px 20px;
            background-color: #fafafa;
            border-top: 1px solid #eaeaea;
        }
        
        .no-results {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        
        .no-results-icon {
            font-size: 3rem;
            color: var(--grey-dark);
            margin-bottom: 20px;
        }
        
        .no-results-title {
            font-size: 1.5rem;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .no-results-message {
            color: var(--grey-dark);
            margin-bottom: 20px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                margin-bottom: 15px;
            }
            
            .filter-select {
                width: 100%;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <header>
            <h1 class="page-title">Available Service Providers</h1>
            <p class="page-subtitle">Find skilled professionals for your needs</p>
        </header>

        <div class="filter-container">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="skill" class="filter-label">Filter by Skill:</label>
                    <select name="skill" id="skill" class="filter-select">
                        <option value="">All Skills</option>
                        <?php while ($skill = $skills_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($skill['skills']); ?>"
                            <?php echo ($selected_skill == $skill['skills']) ? 'selected' : ''; ?>
                            ><?php echo htmlspecialchars($skill['skills']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="services-grid">
                <?php while ($worker = $result->fetch_assoc()): 
                    $worker_id = $worker['id'];

                    // Fetch average rating from the ratings table
                    $rating_query = "SELECT COALESCE(AVG(rating), 0) AS avg_rating FROM ratings WHERE worker_id = ?";
                    $stmt = $conn->prepare($rating_query);
                    $stmt->bind_param("i", $worker_id);
                    $stmt->execute();
                    $rating_result = $stmt->get_result();
                    $rating_data = $rating_result->fetch_assoc();
                    $average_rating = round($rating_data['avg_rating'], 1); // Round to 1 decimal place
                ?>
                    <div class="service-card">
                        <div class="service-header">
                            <img src="uploads/<?php echo htmlspecialchars($worker['picture']); ?>" 
                                alt="<?php echo htmlspecialchars($worker['full_name']); ?>" 
                                class="service-image">
                            <div class="service-title">
                                <h3 class="service-name"><?php echo htmlspecialchars($worker['full_name']); ?></h3>
                                <div class="service-rating">
                                    <?php 
                                    $full_stars = floor($average_rating);
                                    $half_star = ($average_rating - $full_stars >= 0.5);
                                    
                                    // Print full stars
                                    for ($i = 0; $i < $full_stars; $i++) {
                                        echo '<i class="fas fa-star"></i>';
                                    }
                                    
                                    // Print half star if applicable
                                    if ($half_star) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    }

                                    // Print empty stars to make 5 total
                                    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                                    for ($i = 0; $i < $empty_stars; $i++) {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                                <p>(<?php echo $average_rating; ?> / 5)</p>
                            </div>
                        </div>
                        
                        <div class="service-body">
                            <div class="service-detail">
                                <span class="detail-icon"><i class="fas fa-tools"></i></span>
                                <span class="detail-label">Skills:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($worker['skills']); ?></span>
                            </div>
                            
                            <div class="service-detail">
                                <span class="detail-icon"><i class="fas fa-plus-circle"></i></span>
                                <span class="detail-label">Other Skills:</span>
                                <span class="detail-value">
                                    <?php echo !empty($worker['other_skills']) 
                                        ? htmlspecialchars($worker['other_skills']) 
                                        : '<span class="text-muted">None listed</span>'; ?>
                                </span>
                            </div>
                            
                            <div class="service-detail">
                                <span class="detail-icon"><i class="fas fa-map-marker-alt"></i></span>
                                <span class="detail-label">Location:</span>
                                <span class="detail-value">
                                    <?php echo !empty($worker['begy']) 
                                        ? htmlspecialchars($worker['begy']) 
                                        : '<span class="text-muted">Not specified</span>'; ?>
                                </span>
                            </div>
                            
                            <div class="service-detail">
                                <span class="detail-icon"><i class="fas fa-phone-alt"></i></span>
                                <span class="detail-label">Contact:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($worker['contact_num']); ?></span>
                            </div>
                            
                            <div class="service-detail">
                                <span class="detail-icon"><i class="fas fa-tag"></i></span>
                                <span class="detail-label">Service Fee:</span>
                                <span class="detail-value price-tag">₱<?php echo number_format($worker['service_fee'], 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="service-footer">
                            <a href="view_worker_profile.php?worker_id=<?php echo $worker['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-user"></i> View Profile
                            </a>
                            <a href="report_worker.php?worker_id=<?php echo $worker['id']; ?>" class="btn btn-outline">
                                <i class="fas fa-flag"></i> Report
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
                <h3 class="no-results-title">No Service Providers Found</h3>
                <p class="no-results-message">There are currently no service providers available for the selected skill.</p>
                <a href="available_services.php" class="btn btn-primary">
                    <i class="fas fa-redo"></i> Show All Services
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-submit form when selection changes
        document.getElementById('skill').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
                </button>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="services-grid">
                <?php while ($worker = $result->fetch_assoc()): ?>
                    <div class="service-card">
                        <div class="service-header">
                            <img src="uploads/<?php echo htmlspecialchars($worker['picture']); ?>" alt="<?php echo htmlspecialchars($worker['full_name']); ?>" class="service-image">
                            <div class="service-title">
                                <h3 class="service-name"><?php echo htmlspecialchars($worker['full_name']); ?></h3>
                                <div class="service-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
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
<style>
        :root {
            --primary: #3a7bd5;
            --primary-dark: #2b5da0;
            --secondary: #00d2ff;
            --dark: #2d3748;
            --light: #f8fafc;
            --danger: #e53e3e;
            --danger-dark: #c53030;
            --gray-100: #f7fafc;
            --gray-200: #edf2f7;
            --gray-300: #e2e8f0;
            --gray-600: #718096;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --radius: 8px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-100);
            color: var(--dark);
            line-height: 1.6;
        }
        
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .page-subtitle {
            color: var(--gray-600);
            font-size: 16px;
            margin-bottom: 25px;
        }
        
        .filter-container {
            background-color: white;
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 25px;
        }
        
        .filter-form {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 200px;
        }
        
        .filter-label {
            font-weight: 600;
            font-size: 15px;
            white-space: nowrap;
        }
        
        .filter-select {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 15px;
            background-color: white;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%232d3748' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 12px) center;
            padding-right: 35px;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .filter-button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .filter-button:hover {
            background-color: var(--primary-dark);
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .service-card {
            display: flex;
            flex-direction: column;
            background-color: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .service-header {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .service-image {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--gray-200);
        }
        
        .service-title {
            flex: 1;
        }
        
        .service-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .service-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #f59e0b;
        }
        
        .service-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex: 1;
        }
        
        .service-detail {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detail-icon {
            color: var(--primary);
            width: 20px;
            display: flex;
            justify-content: center;
        }
        
        .detail-label {
            font-weight: 600;
            min-width: 90px;
        }
        
        .detail-value {
            flex: 1;
        }
        
        .text-muted {
            color: var(--gray-600);
            font-style: italic;
        }
        
        .price-tag {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 18px;
        }
        
        .service-footer {
            padding: 15px 20px;
            display: flex;
            gap: 10px;
            background-color: var(--gray-100);
            border-top: 1px solid var(--gray-200);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-outline {
            background-color: white;
            color: var(--dark);
            border: 1px solid var(--gray-300);
        }
        
        .btn-outline:hover {
            background-color: var(--gray-200);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: var(--danger-dark);
        }
        
        .no-results {
            text-align: center;
            padding: 40px 20px;
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        
        .no-results-icon {
            font-size: 48px;
            color: var(--gray-300);
            margin-bottom: 20px;
        }
        
        .no-results-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .no-results-message {
            color: var(--gray-600);
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .service-footer {
                flex-direction: column;
            }
        }
    </style>
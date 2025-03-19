<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

// Check if user ID and type are provided
if (!isset($_GET['id']) || !isset($_GET['type'])) {
    echo "<script>alert('Invalid request.'); window.location='manage_users.php';</script>";
    exit();
}

$user_id = intval($_GET['id']);
$user_type = $_GET['type']; // 'client' or 'worker'

// Fetch user details based on type
if ($user_type === 'client') {
    $sql = "SELECT * FROM clients WHERE id = ?";
} elseif ($user_type === 'worker') {
    $sql = "SELECT * FROM workers WHERE id = ?";
} else {
    echo "<script>alert('Invalid user type.'); window.location='manage_users.php';</script>";
    exit();
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('User not found.'); window.location='manage_users.php';</script>";
    exit();
}

$user = $result->fetch_assoc();

// Update logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = htmlspecialchars($_POST['username']);
    $full_name = htmlspecialchars($_POST['full_name']);
    $begy = htmlspecialchars($_POST['begy']);
    $contact_num = htmlspecialchars($_POST['contact_num']);
    $residential_address = htmlspecialchars($_POST['residential_address']);

    // Handle profile picture upload
    $picture = $user['picture'];  // Default to existing picture
    if (!empty($_FILES['picture']['name'])) {
        $target_dir = "uploads/";
        $picture = $target_dir . basename($_FILES['picture']['name']);
        move_uploaded_file($_FILES['picture']['tmp_name'], $picture);
    }

    // Client or Worker-specific updates
    if ($user_type === 'client') {
        $update_sql = "UPDATE clients 
                       SET username = ?, full_name = ?, begy = ?, 
                           contact_num = ?, residential_address = ?, picture = ? 
                       WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssssi", $username, $full_name, $begy, $contact_num, 
                          $residential_address, $picture, $user_id);
    } elseif ($user_type === 'worker') {
        $service_fee = htmlspecialchars($_POST['service_fee']);
        $skills = htmlspecialchars($_POST['skills']);
        $other_skills = htmlspecialchars($_POST['other_skills']); // Added field

        // Handle resume upload
        $resume = $user['resume'];  // Default to existing resume
        if (!empty($_FILES['resume']['name'])) {
            $target_dir = "uploads/";
            $resume = $target_dir . basename($_FILES['resume']['name']);
            move_uploaded_file($_FILES['resume']['tmp_name'], $resume);
        }

        $update_sql = "UPDATE workers 
                       SET username = ?, full_name = ?, begy = ?, 
                           contact_num = ?, residential_address = ?, picture = ?, 
                           service_fee = ?, resume = ?, skills = ?, 
                           other_skills = ?
                       WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssssssssi", $username, $full_name, $begy, $contact_num, 
                          $residential_address, $picture, $service_fee, $resume, 
                          $skills, $other_skills, $user_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('User updated successfully!'); window.location='manage_users.php';</script>";
    } else {
        echo "<script>alert('Failed to update user. Please try again.'); window.location='manage_users.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo ucfirst($user_type); ?> Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Dashboard</h2>
            </div>
            <div class="sidebar-menu">
                <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="manage_users.php" class="active"><i class="fas fa-users"></i> Manage Users</a>
                <a href="feedback.php"><i class="fas fa-chart-bar"></i> View Feedback</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="header">
                <div class="toggle-btn">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <img src="admin_avatar.jpg" alt="Admin">
                </div>
            </div>
            
            <div class="content-area">
                <div class="content-header">
                    <h1>Edit <?php echo ucfirst($user_type); ?> Profile</h1>
                    <div class="breadcrumbs">
                        <a href="dashboard.php">Dashboard</a> / 
                        <a href="manage_users.php">Manage Users</a> / 
                        <span>Edit <?php echo ucfirst($user_type); ?></span>
                    </div>
                </div>
                
                <div class="card">
                    <form method="POST" enctype="multipart/form-data" class="edit-form">
                        <div class="form-grid">
                            <div class="user-profile-section">
                                <div class="profile-pic-container">
                                    <img src="<?php echo htmlspecialchars($user['picture']); ?>" id="preview-image" alt="User Profile">
                                    <div class="upload-overlay">
                                        <label for="picture" class="upload-btn">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                        <input type="file" name="picture" id="picture" accept="image/*" onchange="previewImage(this);">
                                    </div>
                                </div>
                                <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                                <p><?php echo ucfirst($user_type); ?> #<?php echo $user_id; ?></p>
                            </div>
                            
                            <div class="form-section">
                                <h3>Basic Information</h3>
                                <div class="form-group">
                                    <label for="username"><i class="fas fa-user"></i> Username</label>
                                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="full_name"><i class="fas fa-id-card"></i> Full Name</label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_num"><i class="fas fa-phone"></i> Contact Number</label>
                                    <input type="text" id="contact_num" name="contact_num" value="<?php echo htmlspecialchars($user['contact_num']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>Location Details</h3>
                                <div class="form-group">
                                    <label for="begy"><i class="fas fa-map-marker-alt"></i> Barangay</label>
                                    <input type="text" id="begy" name="begy" value="<?php echo htmlspecialchars($user['begy']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="residential_address"><i class="fas fa-home"></i> Residential Address</label>
                                    <input type="text" id="residential_address" name="residential_address" value="<?php echo htmlspecialchars($user['residential_address']); ?>" required>
                                </div>
                            </div>
                            
                            <?php if ($user_type === 'worker'): ?>
                            <div class="form-section worker-specific">
                                <h3>Professional Details</h3>
                                <div class="form-group">
                                    <label for="service_fee"><i class="fas fa-money-bill"></i> Service Fee</label>
                                    <div class="input-with-icon">
                                        <span class="input-prefix">₱</span>
                                        <input type="text" id="service_fee" name="service_fee" value="<?php echo htmlspecialchars($user['service_fee']); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="skills"><i class="fas fa-tools"></i> Skills</label>
                                    <textarea id="skills" name="skills" rows="3"><?php echo htmlspecialchars($user['skills']); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="other_skills"><i class="fas fa-plus-circle"></i> Other Skills</label>
                                    <textarea id="other_skills" name="other_skills" rows="3"><?php echo htmlspecialchars($user['other_skills'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group resume-upload">
                                    <label><i class="fas fa-file-alt"></i> Resume</label>
                                    <div class="file-upload-container">
                                        <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx">
                                        <label for="resume" class="file-upload-btn">
                                            <i class="fas fa-upload"></i> Upload New Resume
                                        </label>
                                        <?php if(!empty($user['resume'])): ?>
                                        <a href="<?php echo htmlspecialchars($user['resume']); ?>" target="_blank" class="view-file-btn">
                                            <i class="fas fa-eye"></i> View Current Resume
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-actions">
                            <a href="manage_users.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar
        document.querySelector('.toggle-btn').addEventListener('click', function() {
            document.querySelector('.page-wrapper').classList.toggle('sidebar-collapsed');
        });
        
        // Preview uploaded image
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>

<style>
    /* Reset and Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
        background-color: #f8f9fc;
        color: #333;
        line-height: 1.6;
    }
    
    /* Page Layout */
    .page-wrapper {
        display: flex;
        min-height: 100vh;
        transition: all 0.3s ease;
    }
    
    .sidebar {
        width: 280px;
        background: linear-gradient(180deg, #8b5e3c 0%, #714d30 100%);
        color: #fff;
        transition: all 0.3s ease;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        z-index: 10;
    }
    
    .sidebar-header {
        padding: 25px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar-header h2 {
        font-size: 22px;
        font-weight: 600;
    }
    
    .sidebar-menu {
        padding: 20px 0;
    }
    
    .sidebar-menu a {
        display: block;
        padding: 15px 20px;
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        font-size: 16px;
        transition: all 0.2s;
        border-left: 3px solid transparent;
    }
    
    .sidebar-menu a:hover, .sidebar-menu a.active {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border-left-color: #fff;
    }
    
    .sidebar-menu a i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    .main-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow-x: hidden;
    }
    
    .header {
        height: 70px;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .toggle-btn {
        font-size: 20px;
        color: #555;
        cursor: pointer;
    }
    
    .user-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .user-info span {
        font-size: 15px;
        color: #555;
    }
    
    .user-info img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #eee;
    }
    
    .content-area {
        flex: 1;
        padding: 25px;
        overflow-y: auto;
    }
    
    .content-header {
        margin-bottom: 25px;
    }
    
    .content-header h1 {
        font-size: 28px;
        color: #333;
        margin-bottom: 10px;
        font-weight: 600;
    }
    
    .breadcrumbs {
        color: #777;
        font-size: 14px;
    }
    
    .breadcrumbs a {
        color: #8b5e3c;
        text-decoration: none;
    }
    
    .breadcrumbs a:hover {
        text-decoration: underline;
    }
    
    /* Card Styles */
    .card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 30px;
        margin-bottom: 25px;
    }
    
    /* Form Styles */
    .form-grid {
        display: grid;
        grid-template-columns: 240px 1fr 1fr;
        gap: 30px;
    }
    
    .user-profile-section {
        text-align: center;
        padding: 20px 10px;
    }
    
    .profile-pic-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto 20px;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .profile-pic-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .upload-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.5);
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .profile-pic-container:hover .upload-overlay {
        opacity: 1;
    }
    
    .upload-btn {
        color: #fff;
        cursor: pointer;
        font-size: 18px;
    }
    
    .user-profile-section input[type="file"] {
        display: none;
    }
    
    .user-profile-section h3 {
        font-size: 18px;
        margin-bottom: 5px;
        color: #333;
    }
    
    .user-profile-section p {
        color: #777;
        font-size: 14px;
    }
    
    .form-section {
        padding: 15px 0;
    }
    
    .form-section h3 {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
        color: #8b5e3c;
        font-size: 18px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: 500;
        font-size: 14px;
    }
    
    .form-group label i {
        width: 20px;
        color: #8b5e3c;
        margin-right: 5px;
    }
    
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 15px;
        transition: all 0.3s;
    }
    
    .form-group input:focus, .form-group textarea:focus {
        border-color: #8b5e3c;
        box-shadow: 0 0 0 3px rgba(139, 94, 60, 0.1);
        outline: none;
    }
    
    .input-with-icon {
        position: relative;
    }
    
    .input-prefix {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #777;
    }
    
    .input-with-icon input {
        padding-left: 30px;
    }
    
    .file-upload-container {
        display: flex;
        gap: 10px;
    }
    
    .file-upload-container input[type="file"] {
        display: none;
    }
    
    .file-upload-btn, .view-file-btn {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        transition: all 0.3s;
        text-decoration: none;
    }
    
    .file-upload-btn {
        background-color: #f8f9fc;
        color: #555;
    }
    
    .file-upload-btn:hover {
        background-color: #eee;
    }
    
    .view-file-btn {
        background-color: #e6f0f9;
        color: #3573b9;
    }
    
    .view-file-btn:hover {
        background-color: #d0e3f7;
    }
    
    .file-upload-btn i, .view-file-btn i {
        margin-right: 5px;
    }
    
    /* Form Action Buttons */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }
    
    .btn {
        padding: 12px 25px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        border: none;
        text-decoration: none;
    }
    
    .btn i {
        margin-right: 8px;
    }
    
    .btn-primary {
        background-color: #8b5e3c;
        color: #fff;
    }
    
    .btn-primary:hover {
        background-color: #714d30;
    }
    
    .btn-secondary {
        background-color: #f2f2f2;
        color: #555;
    }
    
    .btn-secondary:hover {
        background-color: #e5e5e5;
    }
    
    /* Worker-specific Styles */
    .worker-specific {
        grid-column: span 2;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 1024px) {
        .form-grid {
            grid-template-columns: 200px 1fr;
        }
        
        .worker-specific {
            grid-column: span 1;
        }
    }
    
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .sidebar {
            position: fixed;
            left: -280px;
            height: 100%;
        }
        
        .page-wrapper.sidebar-collapsed .sidebar {
            left: 0;
        }
        
        .page-wrapper.sidebar-collapsed .main-content::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 5;
        }
    }
    
    /* Sidebar Collapsed State */
    .page-wrapper.sidebar-collapsed .sidebar {
        width: 80px;
    }
    
    .page-wrapper.sidebar-collapsed .sidebar-header h2,
    .page-wrapper.sidebar-collapsed .sidebar-menu a span {
        display: none;
    }
    
    .page-wrapper.sidebar-collapsed .sidebar-menu a {
        text-align: center;
        padding: 15px;
    }
    
    .page-wrapper.sidebar-collapsed .sidebar-menu a i {
        margin-right: 0;
        font-size: 18px;
    }
    
    @media (min-width: 769px) {
        .page-wrapper.sidebar-collapsed .main-content {
            margin-left: 80px;
        }
    }
</style>
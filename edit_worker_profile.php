<?php
session_start();
include('db.php');

if (!isset($_SESSION['worker_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_worker.php';</script>";
    exit();
}

$worker_username = $_SESSION['worker_username'];

// Fetch worker data
$sql = "SELECT * FROM workers WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $worker_username);
$stmt->execute();
$result = $stmt->get_result();
$worker = $result->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $begy = mysqli_real_escape_string($conn, $_POST['begy']);
    $residential_address = mysqli_real_escape_string($conn, $_POST['residential_address']);
    $skills = mysqli_real_escape_string($conn, $_POST['skills']);
    $other_skills = mysqli_real_escape_string($conn, $_POST['other_skills']);  // New Field
    $service_fee = floatval($_POST['service_fee']);
    $contact_num = mysqli_real_escape_string($conn, $_POST['contact_num']);
    $password = $_POST['password'];

    // Handle profile picture upload
    $picture = $worker['picture'];
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        $picture = time() . '_' . basename($_FILES['profile_picture']['name']);
        $target_file = $target_dir . $picture;

        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['profile_picture']['type'], $allowed_types)) {
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
        } else {
            echo "<script>alert('Invalid file type. Only JPG, JPEG, and PNG are allowed.');</script>";
        }
    }

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $update_sql = "UPDATE workers 
                       SET full_name = ?, begy = ?, residential_address = ?, skills = ?, 
                           other_skills = ?, service_fee = ?, contact_num = ?, password = ?, picture = ? 
                       WHERE username = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssssisss", $full_name, $begy, $residential_address, $skills, 
                          $other_skills, $service_fee, $contact_num, $hashed_password, $picture, $worker_username);
    } else {
        $update_sql = "UPDATE workers 
                       SET full_name = ?, begy = ?, residential_address = ?, skills = ?, 
                           other_skills = ?, service_fee = ?, contact_num = ?, picture = ? 
                       WHERE username = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssssiss", $full_name, $begy, $residential_address, $skills, 
                          $other_skills, $service_fee, $contact_num, $picture, $worker_username);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location='worker_home.php';</script>";
    } else {
        echo "<script>alert('Error updating profile. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Worker Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Edit Profile</h1>
            <a href="worker_home.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-picture-container">
                    <img class="profile-picture" 
                         src="uploads/<?php echo htmlspecialchars($worker['picture'] ?? 'default.jpg'); ?>" 
                         alt="Profile Picture">
                    <label for="profile_picture" class="camera-overlay">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>
                <div class="profile-username"><?php echo htmlspecialchars($worker_username); ?></div>
            </div>
            
            <form class="profile-form" method="POST" enctype="multipart/form-data">
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="file-input">
                
                <h3 class="section-title">Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Name</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($worker['full_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="contact_num">Contact Number</label>
                        <div class="input-with-icon">
                            <i class="fas fa-phone"></i>
                            <input type="text" class="form-control" id="contact_num" name="contact_num" 
                                   value="<?php echo htmlspecialchars($worker['contact_num']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <h3 class="section-title">Location</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="begy">Barangay</label>
                        <div class="input-with-icon">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" class="form-control" id="begy" name="begy" 
                                   value="<?php echo htmlspecialchars($worker['begy']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="residential_address">Complete Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-home"></i>
                            <input type="text" class="form-control" id="residential_address" name="residential_address" 
                                   value="<?php echo htmlspecialchars($worker['residential_address'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                
                <h3 class="section-title">Skills & Services</h3>
                <div class="form-group">
                    <label class="form-label" for="skills">Primary Skills</label>
                    <div class="input-with-icon">
                        <i class="fas fa-tools"></i>
                        <input type="text" class="form-control" id="skills" name="skills" 
                               value="<?php echo htmlspecialchars($worker['skills']); ?>" required>
                    </div>
                    <small style="color: var(--gray); margin-top: 5px; display: block;">
                        List your main skills separated by commas (e.g., Plumbing, Electrical, Carpentry)
                    </small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="other_skills">Additional Skills (Optional)</label>
                    <div class="input-with-icon">
                        <i class="fas fa-plus-circle"></i>
                        <input type="text" class="form-control" id="other_skills" name="other_skills" 
                               value="<?php echo htmlspecialchars($worker['other_skills'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="service_fee">Service Fee</label>
                    <div class="input-group">
                        <div class="input-group-prepend">₱</div>
                        <input type="number" class="form-control" id="service_fee" name="service_fee" 
                               value="<?php echo htmlspecialchars($worker['service_fee']); ?>" required>
                    </div>
                </div>
                
                <h3 class="section-title">Account Security</h3>
                <div class="form-group">
                    <label class="form-label" for="password">New Password (Leave blank to keep current password)</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="worker_home.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Handle profile picture preview
        document.querySelector('.camera-overlay').addEventListener('click', function() {
            document.getElementById('profile_picture').click();
        });
        
        document.getElementById('profile_picture').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-picture').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
<style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2c3e50;
            --light-color: #ecf0f1;
            --success-color: #27ae60;
            --warning-color: #e67e22;
            --danger-color: #e74c3c;
            --gray-light: #f5f7fa;
            --gray: #95a5a6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--gray-light);
            color: var(--secondary-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            color: var(--secondary-color);
            font-size: 24px;
            font-weight: 600;
        }
        
        .back-button {
            background-color: transparent;
            color: var(--primary-color);
            border: none;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        
        .back-button:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .back-button i {
            margin-right: 5px;
        }
        
        .profile-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .profile-header {
            background-color: var(--primary-color);
            color: white;
            padding: 30px;
            position: relative;
            text-align: center;
        }
        
        .profile-picture-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .camera-overlay {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: var(--primary-color);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: background-color 0.2s;
        }
        
        .camera-overlay:hover {
            background-color: var(--primary-dark);
        }
        
        .profile-username {
            margin-top: 15px;
            font-size: 22px;
            font-weight: 600;
        }
        
        .profile-form {
            padding: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--secondary-color);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.2s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .file-input {
            display: none;
        }
        
        .section-title {
            font-size: 18px;
            color: var(--secondary-color);
            font-weight: 600;
            margin: 25px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }
        
        .btn:active {
            transform: scale(0.98);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-cancel {
            background-color: var(--light-color);
            color: var(--secondary-color);
        }
        
        .btn-cancel:hover {
            background-color: #dfe6e9;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon .form-control {
            padding-left: 40px;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .input-group {
            display: flex;
            align-items: center;
        }

        .input-group-prepend {
            background-color: #f8f9fa;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 5px 0 0 5px;
            color: var(--gray);
        }

        .input-group .form-control {
            border-radius: 0 5px 5px 0;
        }
    </style>
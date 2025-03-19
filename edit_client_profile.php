<?php
session_start();
include('db.php');

if (!isset($_SESSION['client_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_client.php';</script>";
    exit();
}

$client_username = $_SESSION['client_username'];

// Fetch client data
$sql = "SELECT * FROM clients WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $client_username);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $begy = mysqli_real_escape_string($conn, $_POST['begy']);
    $residential_address = mysqli_real_escape_string($conn, $_POST['residential_address']);
    $contact_num = mysqli_real_escape_string($conn, $_POST['contact_num']);
    $password = $_POST['password'];

    // Handle profile picture upload
    $picture = $client['picture']; // Default to current picture
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        $picture = time() . '_' . basename($_FILES['profile_picture']['name']);
        $target_file = $target_dir . $picture;

        // Check file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['profile_picture']['type'], $allowed_types)) {
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
        } else {
            echo "<script>alert('Invalid file type. Only JPG, JPEG, and PNG are allowed.');</script>";
        }
    }

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $update_sql = "UPDATE clients SET full_name = ?, begy = ?, residential_address = ?, contact_num = ?, password = ?, picture = ? WHERE username = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssssss", $full_name, $begy, $residential_address, $contact_num, $hashed_password, $picture, $client_username);
    } else {
        $update_sql = "UPDATE clients SET full_name = ?, begy = ?, residential_address = ?, contact_num = ?, picture = ? WHERE username = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssss", $full_name, $begy, $residential_address, $contact_num, $picture, $client_username);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location='client_home.php';</script>";
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
    <title>Edit Profile - Client</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <h1>Edit Profile</h1>
            <a href="client_home.php" style="text-decoration: none; color: var(--primary-color);"><i class="fas fa-home"></i> Back to Home</a>
        </header>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-section">
                <div class="profile-picture-container">
                    <img class="profile-picture" 
                         src="uploads/<?php echo htmlspecialchars($client['picture']); ?>" 
                         alt="Profile Picture" id="profile_pic_preview">
                    <label for="profile_picture_upload" class="camera-icon">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" name="profile_picture" id="profile_picture_upload" accept="image/*" onchange="previewImage(this);">
                </div>
                <h3><?php echo htmlspecialchars($client['username']); ?></h3>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="full_name"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" class="form-control" name="full_name" id="full_name" value="<?php echo htmlspecialchars($client['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="begy"><i class="fas fa-id-card"></i> Begy</label>
                    <input type="text" class="form-control" name="begy" id="begy" value="<?php echo htmlspecialchars($client['begy']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="residential_address"><i class="fas fa-home"></i> Residential Address</label>
                    <input type="text" class="form-control" name="residential_address" id="residential_address" value="<?php echo htmlspecialchars($client['residential_address'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_num"><i class="fas fa-phone"></i> Contact Number</label>
                    <input type="text" class="form-control" name="contact_num" id="contact_num" value="<?php echo htmlspecialchars($client['contact_num']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> New Password (optional)</label>
                    <div class="password-toggle">
                        <input type="password" class="form-control" name="password" id="password">
                        <i class="far fa-eye-slash" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm"><i class="fas fa-lock"></i> Confirm New Password</label>
                    <div class="password-toggle">
                        <input type="password" class="form-control" id="password_confirm">
                        <i class="far fa-eye-slash" id="toggleConfirmPassword"></i>
                    </div>
                </div>
            </div>
            
            <div class="btn-section">
                <a href="client_home.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn" id="update_btn">Update Profile</button>
            </div>
        </form>
    </div>
    
    <script>
        // Preview profile picture before upload
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('profile_pic_preview').src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password_confirm');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
        
        // Password confirmation check
        document.getElementById('update_btn').addEventListener('click', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirm').value;
            
            if (password && password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>
<style>
        :root {
            --primary-color: #3a6ea5;
            --secondary-color: #004e98;
            --accent-color: #ff6b6b;
            --light-gray: #f5f7fa;
            --dark-gray: #555;
            --white: #ffffff;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 30px;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: var(--box-shadow);
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        h1 {
            color: var(--secondary-color);
            font-size: 24px;
            font-weight: 600;
        }
        
        .profile-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: var(--light-gray);
            border-radius: 10px;
        }
        
        .profile-picture-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
            box-shadow: var(--box-shadow);
        }
        
        .camera-icon {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }
        
        .camera-icon:hover {
            background-color: var(--secondary-color);
        }
        
        #profile_picture_upload {
            display: none;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(58, 110, 165, 0.2);
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--dark-gray);
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
        }
        
        .btn-section {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }
        
        .btn-cancel {
            background-color: #e0e0e0;
            color: #333;
        }
        
        .btn-cancel:hover {
            background-color: #d0d0d0;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 20px;
            }
            
            .btn-section {
                flex-direction: column;
            }
        }
    </style>
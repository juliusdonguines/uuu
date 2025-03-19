<?php
include('db.php'); // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and collect form data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Securely hash password
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $begy = mysqli_real_escape_string($conn, $_POST['begy']);
    $residential_address = mysqli_real_escape_string($conn, $_POST['residential_address']);
    $contact_num = mysqli_real_escape_string($conn, $_POST['contact_num']);

    // Handle Picture Upload
    $target_dir = "uploads/";
    $picture_name = basename($_FILES["picture"]["name"]);
    $target_file = $target_dir . $picture_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the uploaded file is a valid image
    $check = getimagesize($_FILES["picture"]["tmp_name"]);
    if ($check === false) {
        die("File is not an image.");
    }

    // Check file size (limit to 2MB)
    if ($_FILES["picture"]["size"] > 2097152) {
        die("Sorry, your file is too large (Max: 2MB).");
    }

    // Allow only JPG, JPEG, PNG
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
        die("Sorry, only JPG, JPEG, and PNG files are allowed.");
    }

    // Move the uploaded file to the target directory
    if (!move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
        die("Sorry, there was an error uploading your file.");
    }

    // Insert data into the database
    $sql = "INSERT INTO clients (username, password, full_name, begy, residential_address, contact_num, picture)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $username, $password, $full_name, $begy, $residential_address, $contact_num, $picture_name);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! Please log in now.'); window.location='login_client.php';</script>";
        exit(); // Important to prevent further code execution
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Create Your Account</h1>
                <p>Join our community and unlock exclusive benefits</p>
            </div>
            <div class="card-body">
                <form action="register_client.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <input type="text" id="username" name="username" class="form-control" required>
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control" required>
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                    
                    <div class="form-divider">
                        <span>Personal Information</span>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <div class="input-group">
                            <input type="text" id="full_name" name="full_name" class="form-control" required>
                            <i class="fas fa-id-card input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="begy">Barangay</label>
                        <div class="input-group">
                            <input type="text" id="begy" name="begy" class="form-control" required>
                            <i class="fas fa-map-marker-alt input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="residential_address">Residential Address</label>
                        <div class="input-group">
                            <input type="text" id="residential_address" name="residential_address" class="form-control" required>
                            <i class="fas fa-home input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact_num">Contact Number</label>
                        <div class="input-group">
                            <input type="text" id="contact_num" name="contact_num" class="form-control" required>
                            <i class="fas fa-phone input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="picture">Upload Your Photo</label>
                        <input type="file" id="picture" name="picture" accept="image/*" required>
                        <label for="picture" class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span class="upload-text">Click to upload your 2x2 photo</span>
                            <p class="help-text">JPG, JPEG, or PNG (Max: 2MB)</p>
                        </label>
                        <div id="preview-container">
                            <img id="image-preview" src="#" alt="Preview">
                        </div>
                    </div>

                    <button type="submit" class="btn">Create Account</button>
                </form>
                <div class="login-link">
                    Already have an account?<a href="login_client.php">Sign in</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview uploaded image
        document.getElementById('picture').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            const previewContainer = document.getElementById('preview-container');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            }
        });
        

    </script>
</body>
</html>

<style>
        /* Custom font import */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary-color: #2563eb;
            --primary-light: #60a5fa;
            --primary-dark: #1e40af;
            --accent-color: #8b5cf6;
            --light-color: #f9fafb;
            --dark-color: #1f2937;
            --success-color: #10b981;
            --error-color: #ef4444;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --text-color: #374151;
            --border-radius: 10px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f5f5;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23cecece' fill-opacity='0.2'%3E%3Cpath opacity='.5' d='M96 95h4v1h-4v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9zm-1 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9z'/%3E%3Cpath d='M6 5V0H5v5H0v1h5v94h1V6h94V5H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            color: var(--text-color);
        }

        .container {
            width: 100%;
            max-width: 580px;
            position: relative;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
        }

        .card-header {
            padding: 40px 30px 20px;
            position: relative;
            text-align: center;
        }

        .card-header:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        .card-header h1 {
            font-size: 28px;
            margin: 0;
            font-weight: 700;
            color: var(--dark-color);
            letter-spacing: -0.5px;
        }

        .card-header p {
            color: var(--gray-500);
            margin-top: 8px;
            font-size: 15px;
        }

        .card-body {
            padding: 10px 30px 40px;
        }

        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--dark-color);
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            transition: var(--transition);
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 45px;
            font-size: 15px;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            transition: var(--transition);
            color: var(--dark-color);
            font-family: 'Inter', sans-serif;
            background-color: var(--light-color);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background-color: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .form-control:focus + .input-icon {
            color: var(--primary-color);
        }

        .file-input-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 25px 15px;
            border: 2px dashed var(--gray-300);
            border-radius: var(--border-radius);
            background-color: var(--gray-100);
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .file-input-label:hover {
            border-color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.05);
        }

        .file-input-label i {
            font-size: 32px;
            margin-bottom: 12px;
            color: var(--primary-color);
        }

        .upload-text {
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .help-text {
            font-size: 13px;
            color: var(--gray-500);
            margin-top: 6px;
            line-height: 1.4;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 16px;
            background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.12);
        }
        
        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .btn:hover:before {
            left: 100%;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            font-size: 15px;
            color: var(--gray-500);
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: var(--transition);
        }

        .login-link a:hover {
            color: var(--primary-dark);
        }

        /* Hide the actual file input but keep it accessible */
        #picture {
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            z-index: -1;
        }

        #preview-container {
            margin-top: 15px;
            text-align: center;
            display: none;
        }

        #image-preview {
            width: 120px;
            height: 120px;
            border-radius: 60px; /* Circle shape */
            border: 3px solid var(--primary-light);
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .form-divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: var(--gray-400);
        }

        .form-divider:before,
        .form-divider:after {
            content: "";
            flex: 1;
            border-top: 1px solid var(--gray-200);
        }

        .form-divider span {
            padding: 0 10px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Password strength indicator */
        .password-strength {
            height: 5px;
            margin-top: 8px;
            border-radius: 3px;
            background-color: var(--gray-200);
            overflow: hidden;
            position: relative;
        }

        .password-strength-meter {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }

        .password-strength.weak .password-strength-meter {
            width: 33%;
            background-color: var(--error-color);
        }

        .password-strength.medium .password-strength-meter {
            width: 66%;
            background-color: #f59e0b;
        }

        .password-strength.strong .password-strength-meter {
            width: 100%;
            background-color: var(--success-color);
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .card-body {
                padding: 10px 20px 30px;
            }
            
            .card-header {
                padding: 30px 20px 15px;
            }
            
            .card-header h1 {
                font-size: 24px;
            }
        }
        
        /* Animated background for card */
        .bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.03;
            pointer-events: none;
        }
    </style>
<?php session_start(); include('db.php'); // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); // Secure password input
    
    $sql = "SELECT * FROM clients WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row['password'])) {
            $_SESSION['client_username'] = $row['username'];
            $_SESSION['client_id'] = $row['id'];  // ✅ Added client_id for rating system
            header("Location: client_home.php"); // Redirect to Client Profile
            exit();
        } else {
            echo "<script>alert('Invalid password. Please try again.'); window.location='login_client.php';</script>";
        }
    } else {
        echo "<script>alert('User not found. Please try again.'); window.location='login_client.php';</script>";
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
    <title>Client Login - PESO Los Baños</title>
</head>
<body>
    <div class="login-container">
        <div class="brand-header">
            
            <h1 class="brand-name">PESO Los Baños</h1>
            <p class="brand-tagline">Your Gateway to Employment Services</p>
        </div>
        
        <div class="login-card">
            <div class="login-header">
                <h2 class="login-title">Client Login</h2>
                <p class="login-subtitle">Access your account to find skilled workers</p>
            </div>
            
            <div class="login-body">
                <form action="login_client.php" method="POST">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <div class="form-hint">Your password is case-sensitive</div>
                    </div>
                    
                    <button type="submit" class="submit-btn">Sign In</button>
                </form>
                
                <div class="register-prompt">
                    Don't have an account? <a href="register_client.php" class="register-link">Register Now</a>
                </div>
                
                <a href="index.php" class="back-link">
                    <span class="back-icon">←</span> Back to Home
                </a>
            </div>
            
            <div class="login-footer">
                <div class="login-options">
                    
                    <a href="login_worker.php" class="login-link">Login as Worker</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<style>
        :root {
            --primary-color: #8b5e3c;
            --primary-dark: #714d30;
            --primary-light: #d7c3b0;
            --accent-color: #5da5da;
            --text-color: #333;
            --light-bg: #f8f8f8;
            --success-color: #4CAF50;
            --error-color: #f44336;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }
        
        .brand-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .brand-logo {
            width: 80px;
            height: 80px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 32px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .brand-name {
            color: var(--primary-color);
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .brand-tagline {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .login-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .login-header {
            background-color: var(--primary-color);
            color: white;
            padding: 25px 30px;
            position: relative;
        }
        
        .login-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        
        .login-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
            font-weight: 400;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            color: #555;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 15px;
        }
        
        .form-control {
            width: 100%;
            height: 48px;
            padding: 0 15px;
            font-size: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(139, 94, 60, 0.2);
            outline: none;
        }
        
        .form-hint {
            font-size: 13px;
            color: #777;
            margin-top: 5px;
        }
        
        .submit-btn {
            width: 100%;
            height: 48px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .submit-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .login-footer {
            padding: 20px 30px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--light-bg);
        }
        
        .login-options {
            display: flex;
            gap: 20px;
        }
        
        .login-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .login-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .register-prompt {
            text-align: center;
            margin-top: 25px;
            color: #555;
            font-size: 14px;
        }
        
        .register-link {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            margin-left: 5px;
        }
        
        .register-link:hover {
            text-decoration: underline;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #333;
            text-decoration: none;
            font-size: 14px;
            margin-top: 15px;
        }
        
        .back-link:hover {
            color: var(--primary-color);
        }
        
        .back-icon {
            margin-right: 6px;
        }
    </style>
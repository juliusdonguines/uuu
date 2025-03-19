<?php session_start(); include('db.php'); // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM workers WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row['password'])) {
            $_SESSION['worker_username'] = $row['username'];
            header("Location: worker_home.php"); // Redirect to Worker Profile
            exit();
        } else {
            echo "<script>alert('Invalid password. Please try again.'); window.location='login_worker.php';</script>";
        }
    } else {
        echo "<script>alert('User not found. Please try again.'); window.location='login_worker.php';</script>";
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
    <title>Worker Login - PESO Los Baños</title>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <a href="login.php" class="back-to-home">
                <span class="back-arrow">←</span> Home
            </a>
            <div class="">
                <span></span>
            </div>
            <h2>Worker Login</h2>
        </div>
        
        <div class="login-body">
            <!-- Display Error Alerts -->
            <?php if (isset($error_message)): ?>
                <div class="alert"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form action="login_worker.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" name="username" id="username" placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="login-btn">Sign In</button>
                
                <div class="links">
                    
                    <a href="login_client.php">Login as Client</a>
                </div>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="register_worker.php">Register now</a>
            </div>
        </div>
    </div>
</body>
</html>
<style>
        :root {
            --primary-color: #8b5e3c;
            --primary-dark: #714d30;
            --accent-color: #4a90e2;
            --text-color: #333;
            --light-bg: #f7f7f7;
            --error-color: #e74c3c;
            --success-color: #2ecc71;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: var(--text-color);
        }
        
        .login-card {
            width: 400px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        
        .login-header h2 {
            font-size: 26px;
            margin: 0;
            font-weight: 600;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .logo span {
            font-size: 26px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(139, 94, 60, 0.2);
            outline: none;
        }
        
        .login-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .alert {
            background-color: #fdecea;
            color: var(--error-color);
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .links {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            font-size: 14px;
        }
        
        .links a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .links a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .register-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .register-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .back-to-home {
            position: absolute;
            top: 15px;
            left: 15px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .back-to-home:hover {
            text-decoration: underline;
        }
        
        .back-arrow {
            margin-right: 5px;
        }
    </style>
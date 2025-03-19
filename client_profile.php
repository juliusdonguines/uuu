<?php
session_start();
include('db.php');

// Ensure the client is logged in
if (!isset($_SESSION['client_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_client.php';</script>";
    exit();
}

$client_username = $_SESSION['client_username'];

// Fetch client details
$sql = "SELECT * FROM clients WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $client_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $client = $result->fetch_assoc();
} else {
    echo "<script>alert('Client profile not found.'); window.location='login_client.php';</script>";
    exit();
}

// Handle Google Maps URL submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $exact_map_location = htmlspecialchars($_POST['exact_map_location']);

    // Auto-fix common Google Maps URL mistakes
    if (strpos($exact_map_location, "goo.gl/maps") !== false) {
        echo "<script>alert('Invalid link! Please use the Google Maps EMBED link.'); window.location='client_profile.php';</script>";
        exit();
    }

    if (!str_contains($exact_map_location, "https://www.google.com/maps/embed?pb=")) {
        echo "<script>alert('Invalid Map link format. Please provide a valid Google Maps embed link.');</script>";
    } else {
        $update_sql = "UPDATE clients SET exact_map_location = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $exact_map_location, $client_username);

        if ($update_stmt->execute()) {
            echo "<script>alert('Map location updated successfully!'); window.location='client_profile.php';</script>";
        } else {
            echo "<script>alert('Failed to update location. Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h2>Client Profile</h2>
            <p>Manage your personal information</p>
        </div>
        
        <div class="avatar-container">
            <img class="avatar" src="uploads/<?php echo htmlspecialchars($client['picture']); ?>" alt="Client Picture">
        </div>
        
        <div class="profile-body">
            <div class="profile-section">
                <h3 class="section-title"><i class="fas fa-user"></i> Personal Information</h3>
                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($client['username']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($client['full_name']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="profile-section">
                <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Location Details</h3>
                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-label">Barangay</div>
                        <div class="info-value"><?php echo htmlspecialchars($client['begy']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($client['residential_address']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Contact</div>
                        <div class="info-value"><?php echo htmlspecialchars($client['contact_num']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="profile-section">
                <h3 class="section-title"><i class="fas fa-location-dot"></i> My Location on Map</h3>
                
                <?php if (!empty($client['exact_map_location'])): ?>
                    <div class="map-container">
                        <iframe 
                            src="<?php echo htmlspecialchars($client['exact_map_location']); ?>" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                <?php else: ?>
                    <div class="no-map">
                        <i class="fas fa-map"></i>
                        <p>You haven't added your location map yet</p>
                    </div>
                <?php endif; ?>
                
                <div class="map-form">
                    <div class="map-instructions">
                        <strong>How to get Google Maps Embed Link:</strong>
                        <ol>
                            <li>Go to Google Maps and search your location</li>
                            <li>Click "Share" and select "Embed a map"</li>
                            <li>Copy the HTML code (only the URL part after src=")</li>
                        </ol>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label for="exact_map_location">Google Maps Embed Link:</label>
                            <input type="text" name="exact_map_location" id="exact_map_location" 
                                class="form-control"
                                placeholder="Paste your Google Maps embed link here" required>
                            <p class="help-text">Link should start with https://www.google.com/maps/embed?pb=</p>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-map-pin"></i> Update My Location
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="button-group">
                <a href="client_home.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
    <style>
        /* CSS CODE STARTS HERE */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #4f46e5;
            --primary-light: #818cf8;
            --primary-dark: #4338ca;
            --secondary-color: #e11d48;
            --background-color: #f7f7f9;
            --surface-color: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #4b5563;
            --text-tertiary: #9ca3af;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --border-radius-sm: 8px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: 
                radial-gradient(circle at 20% 35%, rgba(79, 70, 229, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 75% 65%, rgba(236, 72, 153, 0.05) 0%, transparent 50%);
        }

        .profile-container {
            width: 100%;
            max-width: 480px;
            background-color: var(--surface-color);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            position: relative;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }

        .profile-header h2 {
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .profile-header p {
            opacity: 0.8;
            font-size: 14px;
        }

        .avatar-container {
            width: 110px;
            height: 110px;
            margin: -55px auto 15px;
            position: relative;
            z-index: 1;
        }

        .avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 4px solid var(--surface-color);
            object-fit: cover;
            box-shadow: var(--shadow-md);
            background-color: var(--surface-color);
        }

        .profile-body {
            padding: 10px 25px 25px;
        }

        .profile-section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-tertiary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 8px;
            color: var(--primary-color);
        }

        .profile-info {
            background-color: #f9fafb;
            border-radius: var(--border-radius-sm);
            padding: 15px;
        }

        .info-item {
            display: flex;
            margin-bottom: 12px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            flex: 0 0 40%;
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .info-value {
            flex: 0 0 60%;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 14px;
        }

        .map-container {
            margin-top: 15px;
            width: 100%;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        iframe {
            width: 100%;
            height: 200px;
            border: none;
            display: block;
        }

        .map-form {
            margin-top: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            font-size: 14px;
            font-family: inherit;
            transition: var(--transition);
            background-color: #f9fafb;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-tertiary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            border: none;
            outline: none;
            text-align: center;
            margin-bottom: 12px;
            width: 100%;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background-color: #f9fafb;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #be123c;
            transform: translateY(-2px);
        }

        .btn i {
            margin-right: 8px;
        }

        .button-group {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .help-text {
            font-size: 12px;
            color: var(--text-tertiary);
            margin-top: 5px;
        }

        .map-instructions {
            margin-top: 10px;
            margin-bottom: 15px;
            font-size: 13px;
            color: var(--text-secondary);
            background-color: #f0f9ff;
            border-left: 3px solid var(--primary-color);
            padding: 10px 12px;
            border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;
        }

        .map-instructions ol {
            margin: 10px 0;
            padding-left: 25px;
        }

        .map-instructions li {
            margin-bottom: 5px;
        }

        /* No map message */
        .no-map {
            padding: 30px 15px;
            background-color: #f9fafb;
            border-radius: var(--border-radius-sm);
            text-align: center;
            color: var(--text-tertiary);
        }

        .no-map i {
            font-size: 32px;
            margin-bottom: 10px;
            color: var(--text-tertiary);
        }

        .no-map p {
            font-size: 14px;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .profile-header {
                padding: 25px 15px;
            }
            
            .profile-body {
                padding: 10px 15px 20px;
            }
            
            .info-label, .info-value {
                font-size: 13px;
            }
            
            .avatar-container {
                width: 90px;
                height: 90px;
                margin-top: -45px;
            }
        }
        /* CSS CODE ENDS HERE */
    </style>
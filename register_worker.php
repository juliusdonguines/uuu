<?php
session_start();
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $add_skill = mysqli_real_escape_string($conn, $_POST['add_skill']);
    $service_fee = floatval($_POST['service_fee']);
    $begy = mysqli_real_escape_string($conn, $_POST['begy']);
    $contact_num = mysqli_real_escape_string($conn, $_POST['contact_num']);
    $residential_address = mysqli_real_escape_string($conn, $_POST['residential_address']);

    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $picture_name = basename($_FILES["picture"]["name"]);
    $target_file = $target_dir . $picture_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES["picture"]["tmp_name"]);
    if ($check === false) die("Sorry, file is not an image.");

    if ($_FILES["picture"]["size"] > 2097152) die("Sorry, your picture file is too large (Max: 2MB).");

    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) die("Sorry, only JPG, JPEG, and PNG files are allowed.");

    if (!move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) die("Error uploading your picture.");

    $resume_name = null;
    if (!empty($_FILES["resume"]["name"])) {
        $resume_name = basename($_FILES["resume"]["name"]);
        $resume_file = $target_dir . $resume_name;
        $resumeFileType = strtolower(pathinfo($resume_file, PATHINFO_EXTENSION));

        if (!in_array($resumeFileType, ['pdf', 'doc', 'docx'])) die("Only PDF, DOC, and DOCX files are allowed for resumes.");

        if ($_FILES["resume"]["size"] > 5242880) die("Sorry, your resume file is too large (Max: 5MB).");

        if (!move_uploaded_file($_FILES["resume"]["tmp_name"], $resume_file)) die("Error uploading your resume.");
    }

    $sql = "INSERT INTO workers (username, password, full_name, skills, service_fee, begy, contact_num, residential_address, picture, resume)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisssss", $username, $password, $full_name, $add_skill, $service_fee, $begy, $contact_num, $residential_address, $picture_name, $resume_name);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location='login_worker.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

// Fetch distinct skills from the database for the dropdown
$skills_sql = "SELECT DISTINCT skills FROM workers WHERE skills IS NOT NULL AND skills != ''";
$skills_result = $conn->query($skills_sql);

$existing_skills = [];
while ($row = $skills_result->fetch_assoc()) {
    $existing_skills[] = htmlspecialchars($row['skills']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skilled Worker Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <h1>Skilled Worker Registration</h1>
            <p>Join our community of skilled professionals and connect with clients looking for your services.</p>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h2>Create Your Worker Profile</h2>
                <p>Fill in the form below to showcase your skills and start receiving job requests.</p>
            </div>

            <form action="register_worker.php" method="POST" enctype="multipart/form-data" id="worker-form">
                <div class="form-body">
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-user"></i> Account Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="username">Username</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                                <div class="form-help">Use full name</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                                <div class="form-help">Use a strong password </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-id-card"></i> Personal Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact_num">Contact Number</label>
                                <input type="text" id="contact_num" name="contact_num" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="begy">Barangay</label>
                                <input type="text" id="begy" name="begy" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="residential_address">Residential Address</label>
                                <input type="text" id="residential_address" name="residential_address" class="form-control" placeholder="e.g., Blk 10 Lot 48 Street Name" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-tools"></i> Skills & Services</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="existing_skills">Select or Add Skills</label>
                                <div class="custom-select-wrapper">
                                    <select id="existing_skills" class="form-control form-select" onchange="setSkill()">
                                        <option value="">-- Select Skill --</option>
                                        <?php foreach ($existing_skills as $skill): ?>
                                            <option value="<?php echo $skill; ?>"><?php echo $skill; ?></option>
                                        <?php endforeach; ?>
                                        <option value="custom">Add a new skill</option>
                                    </select>
                                </div>
                                <input type="text" id="add_skill" name="add_skill" class="form-control" style="margin-top: 0.75rem;" placeholder="Type your skill here" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="service_fee">Service Fee</label>
                                <div class="currency-wrapper">
                                    <input type="number" id="service_fee" name="service_fee" class="form-control currency-input" step="0.01" min="0" required>
                                    <span class="input-addon"></span>
                                </div>
                                <div class="form-help">Set your hourly rate for the service you provide</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-file-upload"></i> Profile Documents</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="picture">2x2 Picture</label>
                                <div class="file-input-container">
                                    <input type="file" id="picture" name="picture" class="file-input" accept="image/*" required>
                                    <label for="picture" class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Upload your 2x2 photo (JPG, PNG)</span>
                                    </label>
                                </div>
                                <div id="picture-preview" class="file-preview">
                                    <img id="picture-image" src="#" alt="Picture Preview">
                                    <span id="picture-name" class="file-name"></span>
                                </div>
                                <div class="form-help">Max size: 2MB. Required for your profile</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="resume">Resume (Optional)</label>
                                <div class="file-input-container">
                                    <input type="file" id="resume" name="resume" class="file-input" accept=".pdf,.doc,.docx">
                                    <label for="resume" class="file-input-label">
                                        <i class="fas fa-file-alt"></i>
                                        <span>Upload your resume (PDF, DOC)</span>
                                    </label>
                                </div>
                                <div id="resume-preview" class="file-preview">
                                    <i class="fas fa-file-pdf fa-3x" style="color: var(--primary);"></i>
                                    <span id="resume-name" class="file-name"></span>
                                </div>
                                <div class="form-help">Max size: 5MB. Highlight your experience</div>
                            </div>
                        </div>
                    </div>

                    <div class="progress-bar">
                        <div class="progress-bar-inner" id="form-progress"></div>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                    <div class="login-link">
                        Already registered? <a href="login_worker.php">Log in here</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Handle skill selection/custom input
    function setSkill() {
        const selectElement = document.getElementById("existing_skills");
        const inputElement = document.getElementById("add_skill");
        
        if (selectElement.value === "custom") {
            inputElement.value = "";
            inputElement.focus();
        } else {
            inputElement.value = selectElement.value;
        }
    }

    // Preview uploaded files
    document.getElementById('picture').addEventListener('change', function(e) {
        const preview = document.getElementById('picture-image');
        const previewContainer = document.getElementById('picture-preview');
        const fileNameElement = document.getElementById('picture-name');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                fileNameElement.textContent = file.name;
                previewContainer.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('resume').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('resume-preview');
        const fileNameElement = document.getElementById('resume-name');
        const file = e.target.files[0];
        
        if (file) {
            fileNameElement.textContent = file.name;
            previewContainer.style.display = 'block';
        }
    });

    // Form progress tracking
    const form = document.getElementById('worker-form');
    const progressBar = document.getElementById('form-progress');
    const requiredFields = form.querySelectorAll('[required]');
    const totalRequired = requiredFields.length;

    function updateProgress() {
        let filledCount = 0;
        
        requiredFields.forEach(field => {
            if (field.value.trim() !== '') {
                filledCount++;
            }
        });
        
        const progressPercent = (filledCount / totalRequired) * 100;
        progressBar.style.width = progressPercent + '%';
    }

    // Listen for input on all fields
    form.querySelectorAll('input, select').forEach(field => {
        field.addEventListener('input', updateProgress);
    });

    // Initial progress update
    updateProgress();
    </script>
</body>
</html>

<style>
        /* Custom properties */
        :root {
            --primary: #e8505b;
            --primary-dark: #d9303c;
            --secondary: #14b8a6;
            --accent: #ffc639;
            --dark: #272343;
            --light: #ffffff;
            --gray-100: #f7f7fc;
            --gray-200: #e9e9ef;
            --gray-300: #d1d1db;
            --gray-400: #9e9ea7;
            --gray-500: #6b6b76;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 5px 15px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --font-sans: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --radius-sm: 0.25rem;
            --radius: 0.5rem;
            --radius-lg: 1rem;
        }

        /* Global styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--gray-100);
            color: var(--dark);
            line-height: 1.6;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23bbb' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            padding: 40px 20px;
            min-height: 100vh;
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 1rem;
        }

        .page-header h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }

        .page-header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            width: 80px;
            height: 4px;
            background: var(--accent);
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .page-header p {
            color: var(--gray-500);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 1.5rem auto 0;
        }

        .form-container {
            background: var(--light);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .form-header {
            background: var(--primary);
            color: white;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .form-header::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .form-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .form-header p {
            opacity: 0.9;
            font-size: 1rem;
            max-width: 500px;
            position: relative;
            z-index: 1;
        }

        .form-body {
            padding: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section-title {
            font-size: 1.2rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-section-title i {
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--gray-500);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            color: var(--dark);
            background-color: var(--gray-100);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(232, 80, 91, 0.1);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Cpath fill='%236b6b76' d='M4.646 6.646a.5.5 0 0 1 .708 0L8 9.293l2.646-2.647a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        .form-help {
            font-size: 0.85rem;
            color: var(--gray-400);
            margin-top: 0.5rem;
        }

        .file-input-container {
            position: relative;
        }

        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-color: var(--gray-100);
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius);
            text-align: center;
            color: var(--gray-500);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .file-input-label:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .file-input-label i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }

        .file-preview {
            margin-top: 1rem;
            text-align: center;
            display: none;
        }

        .file-preview img {
            max-width: 150px;
            max-height: 150px;
            border-radius: var(--radius);
            border: 2px solid var(--gray-200);
            padding: 0.25rem;
            background: white;
        }

        .file-name {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--gray-500);
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .form-footer {
            padding: 1.5rem 2rem;
            background-color: var(--gray-100);
            border-top: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .login-link {
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .currency-wrapper {
            position: relative;
        }

        .currency-wrapper::before {
            content: '₱';
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
        }

        .currency-input {
            padding-left: 2rem;
        }

        .input-addon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        /* Section transition effect */
        .form-section {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.5s forwards;
        }

        .form-section:nth-child(1) { animation-delay: 0.1s; }
        .form-section:nth-child(2) { animation-delay: 0.3s; }
        .form-section:nth-child(3) { animation-delay: 0.5s; }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-body, .form-header {
                padding: 1.5rem;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
        }

        /* Custom dropdown styling */
        .custom-select-wrapper {
            position: relative;
        }

        .custom-select {
            position: relative;
            display: block;
            width: 100%;
        }

        /* Progress indicator */
        .progress-bar {
            height: 6px;
            background-color: var(--gray-200);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 1rem;
        }

        .progress-bar-inner {
            height: 100%;
            background-color: var(--secondary);
            transition: width 0.3s ease;
            width: 0;
        }
    </style>
<?php session_start(); include('db.php'); 
if (!isset($_SESSION['client_username'])) {
    header("Location: login_client.php");
    exit();
}

// Retrieve client's username and ID
$client_username = htmlspecialchars($_SESSION['client_username']);

$client_sql = "SELECT id FROM clients WHERE username = ?";
$client_stmt = $conn->prepare($client_sql);
$client_stmt->bind_param("s", $client_username);
$client_stmt->execute();
$client_result = $client_stmt->get_result();

if ($client_result->num_rows === 0) {
    echo "<script>alert('Client not found. Please log in again.'); window.location='login_client.php';</script>";
    exit();
}

$client = $client_result->fetch_assoc();
$client_id = $client['id'];

// Check if worker's full name is provided
if (!isset($_GET['worker_name'])) {
    echo "<script>alert('Worker not found.'); window.location='client_home.php';</script>";
    exit();
}

$worker_name = htmlspecialchars($_GET['worker_name']);

// Fetch worker ID based on full name
$worker_sql = "SELECT id FROM workers WHERE full_name = ?";
$worker_stmt = $conn->prepare($worker_sql);
$worker_stmt->bind_param("s", $worker_name);
$worker_stmt->execute();
$worker_result = $worker_stmt->get_result();

if ($worker_result->num_rows === 0) {
    echo "<script>alert('Worker not found.'); window.location='client_home.php';</script>";
    exit();
}

$worker = $worker_result->fetch_assoc();
$worker_id = $worker['id'];

// Submit feedback
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = intval($_POST['rating']);
    $comment = htmlspecialchars($_POST['comment']);
    
    $insert_sql = "INSERT INTO ratings (worker_id, client_id, rating, comment, created_at)
                    VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iiis", $worker_id, $client_id, $rating, $comment);
    
    if ($insert_stmt->execute()) {
        echo "<script>alert('Feedback submitted successfully!'); window.location='client_home.php';</script>";
    } else {
        echo "<script>alert('Failed to submit feedback. Please try again.'); window.location='feedback_form.php?worker_name=$worker_name';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6fdc;
            --primary-dark: #3a5cba;
            --secondary-color: #f5f7ff;
            --text-color: #333;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 30px;
            position: relative;
        }
        
        .header h2 {
            font-size: 24px;
            font-weight: 600;
        }
        
        .header .worker-name {
            font-size: 16px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .content {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .rating-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 5px;
        }
        
        .star-rating input {
            display: none;
        }
        
        .star-rating label {
            cursor: pointer;
            font-size: 30px;
            color: #ddd;
            transition: color 0.2s;
        }
        
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #ffc107;
        }
        
        .rating-text {
            font-size: 16px;
            font-weight: 500;
            color: #666;
            margin-top: 5px;
        }
        
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            resize: vertical;
            min-height: 120px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(74, 111, 220, 0.2);
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: #e9ecef;
            color: #495057;
        }
        
        .btn-secondary:hover {
            background-color: #dee2e6;
        }
        
        .required {
            color: #dc3545;
            margin-left: 3px;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
            }
            
            .header {
                padding: 15px 20px;
            }
            
            .content {
                padding: 20px;
            }
            
            .star-rating label {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Leave Feedback</h2>
            <div class="worker-name">for <?php echo htmlspecialchars($worker_name); ?></div>
        </div>
        
        <div class="content">
            <form method="POST">
                <div class="form-group">
                    <label for="rating">Rating<span class="required">*</span></label>
                    <div class="rating-container">
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" required />
                            <label for="star5" title="Excellent"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" id="star4" name="rating" value="4" />
                            <label for="star4" title="Very Good"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" id="star3" name="rating" value="3" />
                            <label for="star3" title="Good"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" id="star2" name="rating" value="2" />
                            <label for="star2" title="Fair"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" id="star1" name="rating" value="1" />
                            <label for="star1" title="Poor"><i class="fas fa-star"></i></label>
                        </div>
                        <div class="rating-text">Select your rating</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="comment">Comment<span class="required">*</span></label>
                    <textarea name="comment" id="comment" rows="4" placeholder="Share your experience working with this person..." required></textarea>
                </div>
                
                <div class="actions">
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                    <a href="client_home.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Update the rating text based on selection
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star-rating input');
            const ratingText = document.querySelector('.rating-text');
            const ratingLabels = {
                1: 'Poor',
                2: 'Fair',
                3: 'Good',
                4: 'Very Good',
                5: 'Excellent'
            };
            
            stars.forEach(star => {
                star.addEventListener('change', function() {
                    const value = this.value;
                    ratingText.textContent = ratingLabels[value];
                });
            });
        });
    </script>
</body>
</html>

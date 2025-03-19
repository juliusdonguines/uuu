<?php
session_start();
include('db.php'); // Database connection file

// Ensure the client is logged in
if (!isset($_SESSION['client_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_client.php';</script>";
    exit();
}

// Get worker ID from URL
if (!isset($_GET['worker_id'])) {
    echo "<script>alert('Invalid worker ID.'); window.location='client_home.php';</script>";
    exit();
}

$worker_id = intval($_GET['worker_id']);

// Fetch worker details
$sql = "SELECT * FROM workers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $worker = $result->fetch_assoc();
} else {
    echo "<script>alert('Worker not found.'); window.location='client_home.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rate Worker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .rating-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 10px;
        }
        .btn {
            background-color: #444;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .btn:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
    <div class="rating-container">
        <h2>Rate <?php echo htmlspecialchars($worker['full_name']); ?></h2>
        
        <form action="rate_worker.php" method="POST">
            <input type="hidden" name="worker_id" value="<?php echo $worker['id']; ?>">
            
            <label for="rating">Rate this worker (1-5):</label>
            <select name="rating" required>
                <option value="1">★☆☆☆☆</option>
                <option value="2">★★☆☆☆</option>
                <option value="3">★★★☆☆</option>
                <option value="4">★★★★☆</option>
                <option value="5">★★★★★</option>
            </select>

            <label for="comment">Comment (optional):</label>
            <textarea name="comment" rows="3" placeholder="Write your feedback..."></textarea>

            <button type="submit" class="btn">Submit Rating</button>
        </form>

        <a href="client_home.php" class="btn" style="margin-top: 10px;">Back to Home</a>
    </div>
</body>
</html>

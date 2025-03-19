<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['worker_username'])) {
    echo "<script>alert('Please log in first.'); window.location='login_worker.php';</script>";
    exit();
}

// Fetch all registered clients from the database
$sql = "SELECT * FROM clients";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Clients</title>
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
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }
        .client-card {
            display: flex;
            align-items: center;
            background-color: #fafafa;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .client-card img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
        }
        .client-info {
            flex: 1;
        }
        .client-info p {
            margin: 5px 0;
        }
        .chat-btn, .report-btn {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
        }
        .chat-btn:hover {
            background-color: #45a049;
        }
        .report-btn {
            background-color: red;
        }
        .report-btn:hover {
            background-color: darkred;
        }
        .back-btn {
            display: inline-block;
            background-color: #444;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            width: 100%;
            margin-top: 15px;
        }
        .back-btn:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Available Clients</h2>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($client = $result->fetch_assoc()): ?>
                <div class="client-card">
                    <img src="uploads/<?php echo htmlspecialchars($client['picture']); ?>" alt="Client Picture">
                    <div class="client-info">
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($client['username']); ?></p>
                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($client['full_name']); ?></p>
                        <p><strong>Begy:</strong> <?php echo htmlspecialchars($client['begy']); ?></p>
                        <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($client['contact_num']); ?></p>
                    </div>
                    
                    <!-- Report Button -->
                    <a href="report_client.php?client_id=<?php echo $client['id']; ?>" class="report-btn">Report</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No available clients found.</p>
        <?php endif; ?>

        <a href="worker_profile.php" class="back-btn">Back to Profile</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>

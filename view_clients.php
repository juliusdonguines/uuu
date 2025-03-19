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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --danger-color: #e74c3c;
            --danger-hover: #c0392b;
            --success-color: #2ecc71;
            --success-hover: #27ae60;
            --text-color: #333;
            --light-text: #777;
            --border-color: #e6e6e6;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .wrapper {
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 800px;
            padding: 30px;
            margin-bottom: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .header h1 {
            font-size: 24px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .search-bar {
            display: flex;
            margin-bottom: 20px;
        }

        .search-bar input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px 0 0 6px;
            font-size: 14px;
            outline: none;
            transition: border 0.3s;
        }

        .search-bar input:focus {
            border-color: var(--primary-color);
        }

        .search-bar button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 0 6px 6px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-bar button:hover {
            background-color: var(--secondary-color);
        }

        .client-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .client-card {
            background-color: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid var(--border-color);
        }

        .client-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .client-header {
            display: flex;
            align-items: center;
            padding: 15px;
            background-color: rgba(52, 152, 219, 0.05);
            border-bottom: 1px solid var(--border-color);
        }

        .client-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .client-name {
            margin-left: 15px;
        }

        .client-name h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .client-username {
            color: var(--light-text);
            font-size: 14px;
        }

        .client-details {
            padding: 15px;
        }

        .client-detail {
            display: flex;
            margin-bottom: 10px;
            align-items: center;
        }

        .client-detail i {
            min-width: 24px;
            color: var(--primary-color);
            margin-right: 10px;
        }

        .client-detail span {
            font-size: 14px;
        }

        .client-actions {
            display: flex;
            padding: 0 15px 15px;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            width: 100%;
        }

        .btn i {
            margin-right: 6px;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: var(--success-hover);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: var(--danger-hover);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 5px;
        }

        .pagination a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: white;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid var(--border-color);
        }

        .pagination a:hover, 
        .pagination a.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .no-results {
            text-align: center;
            padding: 40px 0;
            color: var(--light-text);
        }

        .no-results i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }

        @media (max-width: 768px) {
            .client-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 20px;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-users"></i> Available Clients</h1>
                <a href="worker_profile.php" class="btn btn-primary">
                    <i class="fas fa-user"></i> My Profile
                </a>
            </div>
            


            <?php if ($result->num_rows > 0): ?>
                <div class="client-grid">
                    <?php while ($client = $result->fetch_assoc()): ?>
                        <div class="client-card">
                            <div class="client-header">
                                <img src="uploads/<?php echo htmlspecialchars($client['picture']); ?>" alt="<?php echo htmlspecialchars($client['full_name']); ?>" class="client-img">
                                <div class="client-name">
                                    <h3><?php echo htmlspecialchars($client['full_name']); ?></h3>
                                    <div class="client-username">@<?php echo htmlspecialchars($client['username']); ?></div>
                                </div>
                            </div>
                            <div class="client-details">
                                <div class="client-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Brgy. <?php echo htmlspecialchars($client['begy']); ?></span>
                                </div>
                                <div class="client-detail">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo htmlspecialchars($client['contact_num']); ?></span>
                                </div>
                            </div>
                            <div class="client-actions">
                            <i class="fas fa-comment"></i> Chat
                            <a href="chat_box.php?receiver=<?php echo urlencode($client['username']); ?>" class="btn btn-success">
                             <?php echo htmlspecialchars($client['full_name']); ?>
                                </a>

                                    
                                </a>
                                <a href="report_client.php?client_id=<?php echo $client['id']; ?>" class="btn btn-danger">
                                    <i class="fas fa-flag"></i> Report
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <a href="worker_home.php" class="btn btn-primary">Home</a>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-user-slash"></i>
                    <h3>No clients found</h3>
                    <p>There are currently no registered clients in the system.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    

    <script>
        // Search functionality
        document.getElementById('searchBtn').addEventListener('click', performSearch);
        document.getElementById('searchInput').addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                performSearch();
            }
        });

        function performSearch() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const clientCards = document.querySelectorAll('.client-card');
            
            clientCards.forEach(card => {
                const clientName = card.querySelector('.client-name h3').textContent.toLowerCase();
                const clientLocation = card.querySelector('.client-detail span').textContent.toLowerCase();
                
                if (clientName.includes(input) || clientLocation.includes(input)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
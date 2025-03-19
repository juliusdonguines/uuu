<?php
session_start();
include('db.php');

if (!isset($_GET['worker_id']) || empty($_GET['worker_id'])) {
    echo "<script>alert('Invalid worker profile.'); window.location='available_services.php';</script>";
    exit();
}

$worker_id = intval($_GET['worker_id']);

// Fetch comments with client name
$sql = "SELECT r.rating, r.comment, r.feedback, r.created_at, c.full_name 
        FROM ratings r 
        JOIN clients c ON r.client_id = c.id 
        WHERE r.worker_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Worker Comments</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; width: 100%; max-width: 600px; }
        .comment-card { background-color: #fafafa; border: 1px solid #ddd; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .comment-name { font-weight: bold; color: #333; }
        .comment-text, .feedback-text { margin: 5px 0; }
        .created-at { font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Worker Comments</h2>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($comment = $result->fetch_assoc()): ?>
                <div class="comment-card">
                    <p class="comment-name"><?php echo htmlspecialchars($comment['full_name']); ?></p>
                    <p class="rating">Rating: ★ <?php echo $comment['rating']; ?></p>
                    <p class="comment-text"><strong>Comment:</strong> <?php echo htmlspecialchars($comment['comment']); ?></p>
                    <?php if (!empty($comment['feedback'])): ?>
                        <p class="feedback-text"><strong>Feedback:</strong> <?php echo htmlspecialchars($comment['feedback']); ?></p>
                    <?php endif; ?>
                    <p class="created-at">Posted on: <?php echo date("F d, Y", strtotime($comment['created_at'])); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No comments available for this worker.</p>
        <?php endif; ?>
        
        <a href="view_workerprofile.php?worker_id=<?php echo $worker_id; ?>" class="btn">Back to Profile</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>

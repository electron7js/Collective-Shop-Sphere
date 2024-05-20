<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Fetch all reviews
$query = "
    SELECT r.reviewid, r.reviewdate, r.rating, r.body, p.name AS product_name, u.username AS user_name
    FROM Review r
    JOIN Product p ON r.productid = p.productid
    JOIN Users u ON r.userid = u.userid
    ORDER BY r.reviewdate DESC
";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);

$reviews = [];
while ($review = oci_fetch_assoc($stmt)) {
    $reviews[] = $review;
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reviews - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .review-list {
            list-style: none;
            padding: 0;
        }
        .review-list li {
            display: flex;
            flex-direction: column;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .review-list li:last-child {
            border-bottom: none;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .review-header div {
            margin: 5px 0;
        }
        .review-body {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Review Reviews</h2>
    <ul class="review-list">
        <?php foreach ($reviews as $review): ?>
            <li>
                <div class="review-header">
                    <div>Review ID: <?= $review['REVIEWID'] ?></div>
                    <div>Product: <?= htmlspecialchars($review['PRODUCT_NAME']) ?></div>
                    <div>User: <?= htmlspecialchars($review['USER_NAME']) ?></div>
                    <div>Rating: <?= number_format($review['RATING'], 1) ?>/5</div>
                    <div>Date: <?= date('l, F j, Y', strtotime($review['REVIEWDATE'])) ?></div>
                </div>
                <div class="review-body">
                    <p><?= nl2br(htmlspecialchars($review['BODY'])) ?></p>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php include 'footer.php'; ?>

</body>
</html>

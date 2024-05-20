<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $collectionDate = $_POST['collection_date'];

    $slots = [
        ['start' => '09:00:00', 'end' => '12:00:00'],
        ['start' => '13:00:00', 'end' => '15:00:00'],
        ['start' => '16:00:00', 'end' => '19:00:00']
    ];

    foreach ($slots as $slot) {
        $startDateTime = $collectionDate . ' ' . $slot['start'];
        $endDateTime = $collectionDate . ' ' . $slot['end'];

        // Generate a new collection_slot_id
        $collectionSlotIdQuery = "SELECT NVL(MAX(collection_slot_id), 0) + 1 AS new_id FROM Collection_Slot";
        $stmt = oci_parse($conn, $collectionSlotIdQuery);
        oci_execute($stmt);
        $row = oci_fetch_assoc($stmt);
        $newCollectionSlotId = $row['NEW_ID'];

        $query = "INSERT INTO Collection_Slot (collection_slot_id, collection_date, collection_start, collection_end)
                  VALUES (:collection_slot_id, TO_DATE(:collection_date, 'YYYY-MM-DD'), TO_TIMESTAMP(:start_date_time, 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP(:end_date_time, 'YYYY-MM-DD HH24:MI:SS'))";

        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':collection_slot_id', $newCollectionSlotId);
        oci_bind_by_name($stmt, ':collection_date', $collectionDate);
        oci_bind_by_name($stmt, ':start_date_time', $startDateTime);
        oci_bind_by_name($stmt, ':end_date_time', $endDateTime);
        oci_execute($stmt);
    }

    oci_close($conn);

    echo "Collection slots created successfully for " . date('l, F j, Y', strtotime($collectionDate));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Collection Slot</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            margin-top: 6rem;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-group {
            display: flex;
            justify-content: space-between;
        }
        .btn-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-group .create-btn {
            background-color: #28a745;
            color: white;
        }
        .btn-group .cancel-btn {
            background-color: #ccc;
            color: black;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Create Collection Slot</h2>
    <form method="post" action="">
        <div class="form-group">
            <label for="collection_date">Select Collection Date</label>
            <input type="date" id="collection_date" name="collection_date" required>
        </div>
        <div class="btn-group">
            <button type="submit" class="create-btn">Create Slots</button>
            <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
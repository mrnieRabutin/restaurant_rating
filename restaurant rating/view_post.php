<?php
session_start();

include 'connect.php';

function getUserDetails($userId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT username, pic FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Establish PDO connection
try {
    $conn = new PDO("mysql:host=sql6.freesqldatabase.com;dbname=sql6691333", "sql6691333", "JcDgZUMWNU");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die ("Connection failed: " . $e->getMessage());
}

// Check if user is logged in
if (!isset ($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Get user details
$userDetails = getUserDetails($_SESSION["id"]);

// Fetch booking details
if (isset ($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];

    $select_booking = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
    $select_booking->execute([$booking_id]);
    $booking = $select_booking->fetch(PDO::FETCH_ASSOC);

    // Fetch reviews for the booking
    $select_reviews = $conn->prepare("SELECT * FROM reviews WHERE booking_id = ?");
    $select_reviews->execute([$booking_id]);
    $reviews = $select_reviews->fetchAll(PDO::FETCH_ASSOC);

    // Process reviews deletion
    if (isset ($_POST['delete_review'])) {
        $review_id = $_POST['delete_review'];
        $delete_review = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $delete_review->execute([$review_id]);
        header("Location: {$_SERVER['PHP_SELF']}?booking_id=$booking_id");
        exit();
    }
} else {
    header("Location:add_review.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $booking['venue']; ?> Booking
    </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #152238;
            color: #fff;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 10;
        }

        .profile-container {
            display: flex;
            align-items: center;
        }

        .profile-icon,
        .logout-icon {
            color: #fff;
            text-decoration: none;
            font-size: 20px;
            margin-right: 10px;
        }

        .logout-icon:hover,
        .profile-icon:hover {
            cursor: pointer;
        }

        .container {
            max-width: calc(100% - 440px);
            padding: 100px;
            margin-left: 30%;
            margin-right: 50px;
            margin-top: 20px;
            margin-bottom: 30px;
            margin-left: 40vh;
            margin-right: 15px;

        }

        .all-bookings {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-items: flex-start;
            padding: 0px;
            margin-left: 220px;
        }

        .box-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-items: center;
        }

        .box {
            width: calc(33.33% - 20px);
            margin-right: 20px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            box-sizing: border-box;
        }

        .heading {
            text-align: center;
            margin-bottom: 20px;
        }

        .box img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
            display: block;
            margin: 0 auto;
        }

        .title {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .total-reviews {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .inline-btn {
            display: inline-block;
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .inline-btn:hover {
            background-color: #0056b3;
        }

        .sidebar-wrapper {
            float: left;
            width: 200px;
            height: 100vh;
            background-color: #152238;
            color: #fff;
            padding: 20px;
            position: fixed;
            top: 76px;
            left: 0;
        }


        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
        }

        .sidebar-button {
            display: block;
            width: 100px;
            padding: 20px;
            margin-top: 20px;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
            text-align: center;
            font-size: 16px;
        }

        .sidebar-button:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }

        .content-wrapper {
            margin-left: 240px;
            padding: 20px;
        }

        /* CSS styles */
        .rating {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fa {
            font-size: 24px;
            color: #FFD700;
        }

        .checked {
            color: #FFD700;
        }

        table {
            border-collapse: collapse;
            width: 90%;
            margin: auto;
        }

        th,
        td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 12px;
        }

        th {
            background-color: #f2f2f2;
            font-size: 16px;
        }

        td {
            font-size: 14px;
        }

        .booking-details {
            padding: 40px 0;
        }

        .container {
            max-width: 800px;
            margin-left:40vh;
        }

        .venue-details {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .venue-details h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .venue-info p {
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
        }

        .venue-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }



        /* Your CSS styles here */
    </style>
</head>

<body>

    <!-- Header section -->
    <div class="header">
        <h1>Booking Details</h1>
        <div>
            <a href="profile.php" class="profile-icon"><i class="fas fa-user-circle"></i></a>
            <a href="logout.php" class="logout-icon"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar-wrapper">
        <h2>Dashboard</h2>
        <ul>
            <a href="all_posts.php" class="sidebar-button">Home</a>
            <a href="actlogs.php" class="sidebar-button">Activity Logs</a>
    </div>

    <!-- Booking details section -->
    <section class="booking-details">
        <div class="container">
            <?php if (isset ($booking['venue_id'])): ?>
                <?php
                $select_venue = $conn->prepare("SELECT * FROM venues WHERE id = ?");
                $select_venue->execute([$booking['venue_id']]);
                $venue = $select_venue->fetch(PDO::FETCH_ASSOC);
                ?>
                <?php if ($venue): ?>
                    <div class="venue-details">
                        <h3>Venue Details</h3>
                        <div class="venue-info">
                            <p><strong>Name:</strong>
                                <?= $venue['name']; ?>
                            </p>
                            <?php if (!empty ($venue['image'])): ?>
                                <div class="venue-image">
                                    <img src="images/Italian_R.jpg" alt="Venue Image" width="600px">
                                </div>
                            <?php endif; ?>

                            <p><strong>Description:</strong>
                                <?= $venue['description']; ?>
                            </p>
                            <p><strong>Location:</strong>
                                <?= $venue['location']; ?>
                            </p>
                            <p><strong>Contact:</strong>
                                <?= $venue['contact']; ?>
                            </p>
                            <p><strong>Capacity:</strong>
                                <?= $venue['capacity']; ?>
                            </p>
                            <p><strong>Facilities:</strong>
                                <?= $venue['facilities']; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>


    <!-- Reviews section -->
    <section class="booking-reviews">
        <div class="container">
            <div class="add-review">
                <a href="add_review.php?booking_id=<?= $booking_id ?>" class="inline-btn">Add Review</a>
                <h2>User's Reviews</h2>
                <?php if (!empty ($reviews)): ?>
                    <ul>
                        <?php foreach ($reviews as $review): ?>
                            <li>
                                <!-- Display logged-in user's email -->
                                <p>
                                    <?= $_SESSION['email']; ?>
                                </p>
                                <?php if (!empty ($userDetails) && !empty ($userDetails['pic'])): ?>
                                    <img src="<?= $userDetails['pic']; ?>" alt="Profile Picture" width="50">
                                <?php endif; ?>
                                <form method="post">
                                    <input type="hidden" name="delete_review" value="<?= $review['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                                <p>
                                    <?= $review['comment']; ?>
                                </p>
                                <p>Rating:
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="fas fa-star checked"></i>
                                        <?php else: ?>
                                            <i class="fas fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </p>
                                <p>Date:
                                    <?= $review['date']; ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No reviews added yet!</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

</body>

</html>
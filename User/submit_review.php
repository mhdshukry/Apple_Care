<?php
session_start();
include '../config.php'; // Include database connection

// Check if the user is logged in and has a valid user_id
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to submit a review.";
    header("Location: ../product_details.php?id=" . intval($_POST['product_id']));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $user_id = intval($_SESSION['user_id']); // Get the user_id from session
    $comment = htmlspecialchars($_POST['comment']);
    $rating = intval($_POST['rating']);

    // Ensure rating is between 1 and 5
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Invalid rating. Please select a rating between 1 and 5 stars.";
        header("Location: ../product_details.php?id=" . $product_id);
        exit();
    }

    // Fetch the customer_id using user_id
    $customer_sql = "SELECT customer_id FROM customers WHERE user_id = ?";
    $customer_stmt = $conn->prepare($customer_sql);
    $customer_stmt->bind_param("i", $user_id);
    $customer_stmt->execute();
    $customer_result = $customer_stmt->get_result();

    // Check if customer exists
    if ($customer_result->num_rows > 0) {
        $customer = $customer_result->fetch_assoc();
        $customer_id = $customer['customer_id'];

        // Insert the review into the reviews table
        $review_sql = "INSERT INTO reviews (product_id, customer_id, comment, rating, review_date) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($review_sql);
        $stmt->bind_param("iisi", $product_id, $customer_id, $comment, $rating);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Review submitted successfully.";
        } else {
            $_SESSION['error'] = "Failed to submit review.";
        }

        $stmt->close();
    } else {
        // No customer found for the user
        $_SESSION['error'] = "Customer information not found. Please try again.";
    }

    $customer_stmt->close();
    header("Location: ../product_details.php?id=" . $product_id);
    exit();
}
?>

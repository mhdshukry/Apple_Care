<?php
include '../config.php';

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Delete related records from reviews
        $deleteReviews = "DELETE FROM reviews WHERE product_id = ?";
        $stmt = $conn->prepare($deleteReviews);
        if ($stmt === false) {
            throw new Exception("Error preparing statement for reviews: " . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement for reviews: " . $stmt->error);
        }

        // Delete related records from order_details
        $deleteOrderDetails = "DELETE FROM order_details WHERE product_id = ?";
        $stmt = $conn->prepare($deleteOrderDetails);
        if ($stmt === false) {
            throw new Exception("Error preparing statement for order_details: " . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement for order_details: " . $stmt->error);
        }

        // Delete related records from product_categories
        $deleteProductCategories = "DELETE FROM product_categories WHERE product_id = ?";
        $stmt = $conn->prepare($deleteProductCategories);
        if ($stmt === false) {
            throw new Exception("Error preparing statement for product_categories: " . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement for product_categories: " . $stmt->error);
        }

        // Delete the product
        $deleteProduct = "DELETE FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($deleteProduct);
        if ($stmt === false) {
            throw new Exception("Error preparing statement for products: " . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement for products: " . $stmt->error);
        }

        // Commit the transaction
        if (!$conn->commit()) {
            throw new Exception("Error committing transaction: " . $conn->error);
        }

        header("Location: admin.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction if there is an error
        $conn->rollback();
        echo "Error deleting record: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
